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

/**
 * Activity configuration form for InteractEmbed.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Defines the InteractEmbed activity form.
 */
class mod_interactembed_mod_form extends moodleform_mod {
    /**
     * Defines form fields.
     */
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        $mform->addElement('header', 'content', get_string('content', 'mod_interactembed'));
        $sourcetypes = [];
        if (\mod_interactembed\local\site_policy::allows_source('package')) {
            $sourcetypes['package'] = get_string('sourcetypepackage', 'mod_interactembed');
        }
        if (\mod_interactembed\local\site_policy::allows_source('url')) {
            $sourcetypes['url'] = get_string('sourcetypeurl', 'mod_interactembed');
        }
        $sourcetypes['guide'] = get_string('sourcetypeguide', 'mod_interactembed');
        $mform->addElement('select', 'sourcetype', get_string('sourcetype', 'mod_interactembed'), $sourcetypes);
        $mform->setDefault('sourcetype', array_key_first($sourcetypes));
        $mform->addHelpButton('sourcetype', 'sourcetype', 'mod_interactembed');
        $mform->addElement('textarea', 'sourceurl', get_string('sourceurl', 'mod_interactembed'), [
            'rows' => 4,
            'class' => 'w-100',
        ]);
        $mform->setType('sourceurl', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('sourceurl', 'sourceurl', 'mod_interactembed');
        $mform->hideIf('sourceurl', 'sourcetype', 'neq', 'url');
        $mform->addElement(
            'filemanager',
            'packagefiles',
            get_string('packagefiles', 'mod_interactembed'),
            null,
            [
                'accepted_types' => '*',
                'maxbytes' => 0,
                'maxfiles' => -1,
                'subdirs' => true,
            ]
        );
        $mform->addHelpButton('packagefiles', 'packagefiles', 'mod_interactembed');
        $mform->hideIf('packagefiles', 'sourcetype', 'neq', 'package');
        $coursecontext = context_course::instance($this->_course->id);
        $creatoroptions = \mod_interactembed\local\creator_connector::options($coursecontext);
        if ($creatoroptions) {
            $mform->addElement('select', 'packageorigin', get_string('packageorigin', 'mod_interactembed'), [
                'upload' => get_string('packageoriginupload', 'mod_interactembed'),
                'library' => get_string('packageoriginlibrary', 'mod_interactembed'),
            ]);
            $mform->setDefault('packageorigin', 'upload');
            $mform->hideIf('packageorigin', 'sourcetype', 'neq', 'package');
            $mform->addElement(
                'select',
                'creatorpublicationid',
                get_string('creatorpublication', 'mod_interactembed'),
                $creatoroptions
            );
            $mform->setType('creatorpublicationid', PARAM_INT);
            $mform->hideIf('creatorpublicationid', 'sourcetype', 'neq', 'package');
            $mform->hideIf('creatorpublicationid', 'packageorigin', 'neq', 'library');
            $mform->hideIf('packagefiles', 'packageorigin', 'eq', 'library');
        }
        $mform->addElement('text', 'entrypoint', get_string('entrypoint', 'mod_interactembed'));
        $mform->setType('entrypoint', PARAM_PATH);
        $mform->setDefault('entrypoint', 'index.html');
        $mform->addHelpButton('entrypoint', 'entrypoint', 'mod_interactembed');
        $mform->hideIf('entrypoint', 'sourcetype', 'neq', 'package');
        if ($creatoroptions) {
            $mform->hideIf('entrypoint', 'packageorigin', 'eq', 'library');
        }
        if ($creatoroptions) {
            $mform->hideIf('entrypoint', 'packageorigin', 'eq', 'library');
        }

        $mform->addElement('header', 'presentation', get_string('presentation', 'mod_interactembed'));
        $mform->addElement('filemanager', 'coverfiles', get_string('coverimage', 'mod_interactembed'), null, [
            'accepted_types' => ['image'],
            'maxbytes' => 5 * 1024 * 1024,
            'maxfiles' => 1,
            'subdirs' => false,
        ]);
        $mform->addHelpButton('coverfiles', 'coverimage', 'mod_interactembed');
        $mform->addElement(
            'static',
            'coverimageguidance',
            '',
            get_string('coverimageguidance', 'mod_interactembed')
        );
        $mform->addElement('text', 'coveralt', get_string('coveralt', 'mod_interactembed'));
        $mform->setType('coveralt', PARAM_TEXT);
        $mform->addHelpButton('coveralt', 'coveralt', 'mod_interactembed');

        $mform->addElement('select', 'aspectratio', get_string('aspectratio', 'mod_interactembed'), [
            '16x9' => get_string('ratio16x9', 'mod_interactembed'),
            '4x3' => get_string('ratio4x3', 'mod_interactembed'),
            '1x1' => get_string('ratio1x1', 'mod_interactembed'),
            '3x4' => get_string('ratio3x4', 'mod_interactembed'),
            '9x16' => get_string('ratio9x16', 'mod_interactembed'),
            'custom' => get_string('ratiocustom', 'mod_interactembed'),
        ]);
        $defaultaspectratio = \mod_interactembed\local\site_policy::string_setting('defaultaspectratio', '16x9');
        $mform->setDefault('aspectratio', in_array(
            $defaultaspectratio,
            ['16x9', '4x3', '1x1', '3x4', '9x16', 'custom'],
            true
        ) ? $defaultaspectratio : '16x9');
        $mform->addElement('text', 'customheight', get_string('customheight', 'mod_interactembed'));
        $mform->setType('customheight', PARAM_INT);
        $mform->setDefault('customheight', max(200, min(4000, (int)
            \mod_interactembed\local\site_policy::string_setting('defaultcustomheight', '600'))));
        $mform->hideIf('customheight', 'aspectratio', 'neq', 'custom');
        $mform->addElement('select', 'displaymode', get_string('displaymode', 'mod_interactembed'), [
            'inline' => get_string('displaymodeinline', 'mod_interactembed'),
            'card' => get_string('displaymodecard', 'mod_interactembed'),
        ]);
        $mform->setDefault('displaymode', \mod_interactembed\local\configuration_guard::display_mode(
            \mod_interactembed\local\site_policy::string_setting('defaultdisplaymode', 'inline')
        ));
        $mform->addElement('advcheckbox', 'loadonrequest', get_string('loadonrequest', 'mod_interactembed'));
        $mform->addHelpButton('loadonrequest', 'loadonrequest', 'mod_interactembed');
        $mform->setDefault(
            'loadonrequest',
            \mod_interactembed\local\site_policy::bool_setting('defaultloadonrequest', false)
        );
        $mform->hideIf('loadonrequest', 'displaymode', 'eq', 'card');
        $mform->addElement('advcheckbox', 'showfullscreen', get_string('showfullscreen', 'mod_interactembed'));
        $mform->setDefault(
            'showfullscreen',
            \mod_interactembed\local\site_policy::bool_setting('defaultshowfullscreen', true)
        );
        $mform->addElement('advcheckbox', 'lazyload', get_string('lazyload', 'mod_interactembed'));
        $mform->setDefault(
            'lazyload',
            \mod_interactembed\local\site_policy::bool_setting('defaultlazyload', true)
        );
        $sandboxprofiles = [
            'strict' => get_string('sandboxstrict', 'mod_interactembed'),
            'standard' => get_string('sandboxstandard', 'mod_interactembed'),
        ];
        if (has_capability('mod/interactembed:trustpackage', $this->context)) {
            $sandboxprofiles['trusted'] = get_string('sandboxtrusted', 'mod_interactembed');
        }
        $mform->addElement(
            'select',
            'sandboxprofile',
            get_string('sandboxprofile', 'mod_interactembed'),
            $sandboxprofiles
        );
        $defaultsandbox = \mod_interactembed\local\site_policy::string_setting('defaultsandboxprofile', 'standard');
        $mform->setDefault('sandboxprofile', in_array($defaultsandbox, ['strict', 'standard'], true)
            ? $defaultsandbox : 'standard');
        $mform->addHelpButton('sandboxprofile', 'sandboxprofile', 'mod_interactembed');
        if (\mod_interactembed\local\site_policy::allows_embed_code()) {
            $mform->addElement('advcheckbox', 'showembedcode', get_string('showembedcode', 'mod_interactembed'));
            $mform->setDefault(
                'showembedcode',
                \mod_interactembed\local\site_policy::bool_setting('defaultshowembedcode', false)
            );
            $mform->addHelpButton('showembedcode', 'showembedcode', 'mod_interactembed');
        }

        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Adds the explicit content-completion rule.
     *
     * @return array Form element names.
     */
    public function add_completion_rules(): array {
        $element = 'completioncontent' . $this->get_suffix();
        $this->_form->addElement(
            'checkbox',
            $element,
            '',
            get_string('completioncontent', 'mod_interactembed')
        );
        return [$element];
    }

    /**
     * Reports whether the custom completion rule is enabled.
     *
     * @param array $data Submitted form data.
     * @return bool Whether the rule is enabled.
     */
    public function completion_rule_enabled($data): bool {
        return !empty($data['completioncontent' . $this->get_suffix()]);
    }

    /**
     * Ensures an unticked completion checkbox is persisted as zero.
     *
     * @param stdClass $data Submitted form data.
     */
    public function data_postprocessing($data): void {
        parent::data_postprocessing($data);
        if (!empty($data->completionunlocked)) {
            $element = 'completioncontent' . $this->get_suffix();
            $data->{$element} = empty($data->{$element}) ? 0 : 1;
        }
    }

    /**
     * Prepares the active package revision for editing.
     *
     * @param array $defaultvalues Default form values.
     */
    public function data_preprocessing(&$defaultvalues): void {
        parent::data_preprocessing($defaultvalues);
        $draftitemid = file_get_submitted_draft_itemid('packagefiles');
        $itemid = (int) ($defaultvalues['packageitemid'] ?? 0);
        file_prepare_draft_area(
            $draftitemid,
            $this->context->id,
            'mod_interactembed',
            'content',
            $itemid,
            ['subdirs' => true, 'maxfiles' => -1]
        );
        $defaultvalues['packagefiles'] = $draftitemid;

        $coverdraftitemid = file_get_submitted_draft_itemid('coverfiles');
        file_prepare_draft_area(
            $coverdraftitemid,
            $this->context->id,
            'mod_interactembed',
            'cover',
            0,
            ['subdirs' => false, 'maxfiles' => 1, 'accepted_types' => ['image']]
        );
        $defaultvalues['coverfiles'] = $coverdraftitemid;
    }

    /**
     * Validates the selected package before saving.
     *
     * @param array $data Submitted values.
     * @param array $files Submitted files.
     * @return array
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        $coverdraftitemid = (int) ($data['coverfiles'] ?? 0);
        if ($coverdraftitemid > 0) {
            $coverinfo = file_get_draft_area_info($coverdraftitemid);
            if (!empty($coverinfo['filecount']) && trim((string) ($data['coveralt'] ?? '')) === '') {
                $errors['coveralt'] = get_string('coveraltrequired', 'mod_interactembed');
            }
        }
        $height = (int) ($data['customheight'] ?? 600);
        if (($data['aspectratio'] ?? '') === 'custom' && ($height < 200 || $height > 4000)) {
            $errors['customheight'] = get_string('customheighterror', 'mod_interactembed');
        }
        $sourcetype = $data['sourcetype'] ?? 'package';
        if ($sourcetype === 'url') {
            try {
                \mod_interactembed\local\embed\embed_builder::build((string) ($data['sourceurl'] ?? ''));
            } catch (\InvalidArgumentException $exception) {
                $errors['sourceurl'] = get_string('sourceurlinvalid', 'mod_interactembed');
            }
            return $errors;
        }
        if ($sourcetype !== 'package') {
            return $errors;
        }
        if (($data['packageorigin'] ?? 'upload') === 'library') {
            try {
                \mod_interactembed\local\creator_connector::snapshot(
                    context_course::instance($this->_course->id),
                    (int) ($data['creatorpublicationid'] ?? 0)
                );
            } catch (\Throwable $exception) {
                debugging($exception->getMessage(), DEBUG_DEVELOPER);
                $errors['creatorpublicationid'] = get_string('creatorpublicationinvalid', 'mod_interactembed');
            }
            return $errors;
        }
        try {
            \mod_interactembed\local\package\package_importer::validate_draft(
                (int) $data['packagefiles'],
                (string) $data['entrypoint']
            );
        } catch (\moodle_exception $exception) {
            $errors['packagefiles'] = $exception->getMessage();
        }
        return $errors;
    }
}
