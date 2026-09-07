<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_interactembed\local;

use context_module;
use html_writer;
use mod_interactembed\local\embed\embed_builder;
use mod_interactembed\local\package\entrypoint_locator;
use mod_interactembed\local\package\package_access_key;
use mod_interactembed\output\content;
use moodle_url;
use stdClass;

/**
 * Creates validated template data for the activity player.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class content_factory {
    /** Display ratio to CSS aspect-ratio mapping. */
    private const RATIOS = [
        '16x9' => '16 / 9',
        '4x3' => '4 / 3',
        '1x1' => '1 / 1',
        '3x4' => '3 / 4',
        '9x16' => '9 / 16',
    ];

    /**
     * Builds player data for one activity instance.
     *
     * @param stdClass $instance Activity record.
     * @param context_module $context Activity context.
     * @return content
     */
    public static function from_instance(stdClass $instance, context_module $context, bool $showembedtools = false): content {
        $sourcetype = interactembed_normalise_sourcetype((string) ($instance->sourcetype ?? 'package'));
        if (!site_policy::allows_source($sourcetype)) {
            throw new \moodle_exception('sourcenotallowed', 'mod_interactembed');
        }

        $siteorigin = self::site_origin();
        $source = self::source_data($sourcetype, $context, $instance, $siteorigin);
        $display = self::display_data($sourcetype, $instance, $source['origin'], $siteorigin);
        $cover = self::cover_data($context, $instance);
        $domid = 'mod-interactembed-' . (int) $instance->id;

        return new content([
            'domid' => $domid,
            'frameid' => $domid . '-frame',
            'iframeurl' => $source['iframeurl'],
            'externalurl' => $source['iframeurl'],
            'title' => format_string($instance->name, true, ['context' => $context]),
            'description' => '',
            'hasdescription' => false,
            'provider' => $source['provider'],
            'expectedorigin' => $source['origin'],
            'sandbox' => $display['sandbox'],
            'allow' => 'fullscreen; autoplay; clipboard-write; encrypted-media; picture-in-picture',
            'lazy' => !empty($instance->lazyload),
            'showfullscreen' => !empty($instance->showfullscreen),
            'waitforinteraction' => $display['waitforinteraction'],
            'iscard' => $display['iscard'],
            'iscustomheight' => $display['custom'],
            'customheight' => $display['height'],
            'ratio' => $display['ratio'],
            'coverurl' => $cover['url'],
            'hascover' => $cover['url'] !== '',
            'coveralt' => $cover['alt'],
            'showembedtools' => $showembedtools,
            'embedcode' => $showembedtools ? self::embed_code(
                new moodle_url('/mod/interactembed/embed.php', ['id' => $context->instanceid]),
                format_string($instance->name, true, ['context' => $context]),
                $display['height'],
                $context->instanceid
            ) : '',
            'embedinputid' => $domid . '-embedcode',
        ]);
    }

    /**
     * Returns the canonical Moodle origin.
     *
     * @return string Canonical Moodle origin.
     */
    private static function site_origin(): string {
        $parts = parse_url((new moodle_url('/'))->out(false));
        return strtolower($parts['scheme']) . '://' . strtolower($parts['host']) .
            (isset($parts['port']) ? ':' . (int) $parts['port'] : '');
    }

    /**
     * Resolves the selected content source.
     *
     * @param string $sourcetype Source type.
     * @param context_module $context Activity context.
     * @param stdClass $instance Activity record.
     * @param string $siteorigin Moodle origin.
     * @return array{iframeurl:string,origin:string,provider:string}
     */
    private static function source_data(
        string $sourcetype,
        context_module $context,
        stdClass $instance,
        string $siteorigin
    ): array {
        if ($sourcetype === 'guide') {
            $language = substr(current_language(), 0, 2);
            if (!in_array($language, ['en', 'es', 'fr'], true)) {
                $language = 'en';
            }
            return [
                'iframeurl' => (new moodle_url('/mod/interactembed/guide/index.html', [
                    'lang' => $language,
                ]))->out(false),
                'origin' => $siteorigin,
                'provider' => 'interactembedguide',
            ];
        }
        if ($sourcetype === 'url') {
            $result = embed_builder::build((string) $instance->sourceurl);
            return [
                'iframeurl' => $result->get_url(),
                'origin' => (string) $result->get_origin(),
                'provider' => $result->get_provider(),
            ];
        }

        $entry = entrypoint_locator::locate(
            $context,
            (int) $instance->packageitemid,
            (string) $instance->entrypoint
        );
        $key = package_access_key::create(
            $context->id,
            (int) $instance->revision,
            (int) $instance->packageitemid
        );
        $parts = array_values(array_filter(explode('/', trim($entry->get_filepath(), '/')), 'strlen'));
        $parts[] = $entry->get_filename();
        $url = (new moodle_url('/mod/interactembed/package.php'))->out(false) . '/' . $context->id . '/' .
            rawurlencode($key) . '/' . implode('/', array_map('rawurlencode', $parts)) .
            '?rev=' . (int) $instance->revision;
        return ['iframeurl' => $url, 'origin' => $siteorigin, 'provider' => 'package'];
    }

    /**
     * Normalises responsive presentation and sandbox values.
     *
     * @param string $sourcetype Source type.
     * @param stdClass $instance Activity record.
     * @param string $origin Content origin.
     * @param string $siteorigin Moodle origin.
     * @return array{ratio:string,custom:bool,height:int,iscard:bool,waitforinteraction:bool,sandbox:string}
     */
    private static function display_data(
        string $sourcetype,
        stdClass $instance,
        string $origin,
        string $siteorigin
    ): array {
        $ratio = array_key_exists($instance->aspectratio ?? '', self::RATIOS)
            ? $instance->aspectratio
            : '16x9';
        $custom = ($instance->aspectratio ?? '') === 'custom';
        $height = max(200, min(4000, (int) ($instance->customheight ?? 600)));
        $iscard = configuration_guard::display_mode((string) ($instance->displaymode ?? 'inline')) === 'card';
        $profile = (string) ($instance->sandboxprofile ?? 'standard');
        if ($profile !== sandbox_policy::normalise($profile) || ($sourcetype !== 'package' && $profile === 'trusted')) {
            $profile = 'standard';
        }
        $sandbox = sandbox_policy::permissions($profile);
        if ($sourcetype !== 'package' && $profile === 'standard' && $origin !== $siteorigin) {
            $sandbox .= ' allow-same-origin';
        }
        return [
            'ratio' => self::RATIOS[$ratio],
            'custom' => $custom,
            'height' => $height,
            'iscard' => $iscard,
            'waitforinteraction' => $iscard || !empty($instance->loadonrequest) ||
                ($sourcetype === 'url' && site_policy::forces_remote_consent()),
            'sandbox' => $sandbox,
        ];
    }

    /**
     * Returns the optional accessible cover image.
     *
     * @param context_module $context Activity context.
     * @param stdClass $instance Activity record.
     * @return array{url:string,alt:string}
     */
    private static function cover_data(context_module $context, stdClass $instance): array {
        $files = get_file_storage()->get_area_files(
            $context->id,
            'mod_interactembed',
            'cover',
            0,
            'id ASC',
            false
        );
        if (!$files) {
            return ['url' => '', 'alt' => ''];
        }
        $file = reset($files);
        $url = moodle_url::make_pluginfile_url(
            $context->id,
            'mod_interactembed',
            'cover',
            0,
            $file->get_filepath(),
            $file->get_filename()
        )->out(false);
        $alt = trim((string) ($instance->coveralt ?? ''));
        return [
            'url' => $url,
            'alt' => $alt !== '' ? $alt : format_string($instance->name, true, ['context' => $context]),
        ];
    }

    /**
     * Builds a same-site, access-controlled iframe snippet.
     *
     * @param moodle_url $url Embedded activity URL.
     * @param string $title Accessible frame title.
     * @param int $height Frame height in pixels.
     * @param int $cmid Course module identifier.
     * @return string Iframe markup.
     */
    private static function embed_code(moodle_url $url, string $title, int $height, int $cmid): string {
        $code = html_writer::tag('iframe', '', [
            'src' => $url->out(false),
            'title' => $title,
            'width' => '100%',
            'height' => (string) $height,
            'loading' => 'lazy',
            'allowfullscreen' => 'allowfullscreen',
            'data-interactembed-id' => (string) $cmid,
            'style' => 'border:0;display:block;width:100%;',
        ]);
        return $code . html_writer::script('', new moodle_url('/mod/interactembed/embed-resizer.js', [
            'v' => 2026090101,
        ]));
    }
}
