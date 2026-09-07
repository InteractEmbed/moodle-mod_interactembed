<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace mod_interactembed;

/**
 * Tests privacy-sensitive player template rendering.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversNothing
 */
final class content_template_test extends \advanced_testcase {
    /**
     * Tests that click-to-load markup does not initially request the iframe URL.
     */
    public function test_click_to_load_uses_data_source(): void {
        $html = $this->render_player(true);

        $this->assertStringContainsString('data-src="https://content.example.org/player"', $html);
        $this->assertStringNotContainsString(' src="https://content.example.org/player"', $html);
        $this->assertStringContainsString('data-action="load-content"', $html);
        $this->assertStringContainsString('aria-controls="interactembed-test-frame"', $html);
        $this->assertStringContainsString('title="Interactive test"', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
        $this->assertStringContainsString('aria-pressed="false"', $html);
        $this->assertStringContainsString('data-role="fullscreen-label"', $html);
        $this->assertStringContainsString('data-fullscreen-label="Interactive test"', $html);
        $this->assertStringContainsString('data-action="fullscreen"', $html);
        $this->assertStringNotContainsString('/i/fullscreen', $html);
        $this->assertMatchesRegularExpression('/id="interactembed-test-frame"[^>]*hidden/', $html);
    }

    /**
     * Tests that the ordinary inline player keeps immediate loading.
     */
    public function test_inline_player_uses_iframe_source(): void {
        $html = $this->render_player(false);

        $this->assertStringContainsString('src="https://content.example.org/player"', $html);
        $this->assertStringNotContainsString('data-src=', $html);
        $this->assertStringNotContainsString('data-action="load-content"', $html);
    }

    /**
     * Tests the optional accessible description and cover markup.
     */
    public function test_description_and_cover_are_rendered_safely(): void {
        $html = $this->render_player(true, [
            'description' => 'Choose an answer before continuing.',
            'hasdescription' => true,
            'coverurl' => 'https://example.org/cover.jpg',
            'hascover' => true,
            'coveralt' => 'Learners collaborating around a screen',
        ]);

        $this->assertStringContainsString('mod-interactembed-description', $html);
        $this->assertStringContainsString('Choose an answer before continuing.', $html);
        $this->assertStringContainsString('mod-interactembed-cover', $html);
        $this->assertStringContainsString('alt="Learners collaborating around a screen"', $html);
    }

    /**
     * Renders a representative player.
     *
     * @param bool $waitforinteraction whether the iframe waits for a click
     * @param array $overrides template data overrides
     * @return string rendered HTML
     */
    private function render_player(bool $waitforinteraction, array $overrides = []): string {
        global $PAGE;

        $this->resetAfterTest();
        $PAGE->set_context(\context_system::instance());
        $renderer = $PAGE->get_renderer('core');

        $data = [
            'domid' => 'interactembed-test',
            'frameid' => 'interactembed-test-frame',
            'iframeurl' => 'https://content.example.org/player',
            'externalurl' => 'https://content.example.org/player',
            'title' => 'Interactive test',
            'provider' => 'generic',
            'expectedorigin' => 'https://content.example.org',
            'sandbox' => 'allow-scripts',
            'allow' => 'fullscreen',
            'lazy' => true,
            'showfullscreen' => true,
            'waitforinteraction' => $waitforinteraction,
            'iscard' => $waitforinteraction,
            'iscustomheight' => false,
            'customheight' => 600,
            'ratio' => '16 / 9',
            'showembedtools' => false,
            'embedcode' => '',
            'embedinputid' => 'interactembed-test-code',
            'description' => '',
            'hasdescription' => false,
            'coverurl' => '',
            'hascover' => false,
            'coveralt' => '',
        ];
        return $renderer->render_from_template(
            'mod_interactembed/content',
            array_replace($data, $overrides)
        );
    }
}
