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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Administrative settings for InteractEmbed.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'mod_interactembed/defaultsheading',
        get_string('defaultsheading', 'mod_interactembed'),
        get_string('defaultsheading_desc', 'mod_interactembed')
    ));
    $settings->add(new admin_setting_configselect(
        'mod_interactembed/defaultaspectratio',
        get_string('defaultaspectratio', 'mod_interactembed'),
        get_string('defaultaspectratio_desc', 'mod_interactembed'),
        '16x9',
        [
            '16x9' => get_string('ratio16x9', 'mod_interactembed'),
            '4x3' => get_string('ratio4x3', 'mod_interactembed'),
            '1x1' => get_string('ratio1x1', 'mod_interactembed'),
            '3x4' => get_string('ratio3x4', 'mod_interactembed'),
            '9x16' => get_string('ratio9x16', 'mod_interactembed'),
            'custom' => get_string('ratiocustom', 'mod_interactembed'),
        ]
    ));
    $settings->add(new admin_setting_configtext(
        'mod_interactembed/defaultcustomheight',
        get_string('defaultcustomheight', 'mod_interactembed'),
        get_string('defaultcustomheight_desc', 'mod_interactembed'),
        600,
        PARAM_INT
    ));
    $settings->add(new admin_setting_configselect(
        'mod_interactembed/defaultdisplaymode',
        get_string('defaultdisplaymode', 'mod_interactembed'),
        get_string('defaultdisplaymode_desc', 'mod_interactembed'),
        'inline',
        [
            'inline' => get_string('displaymodeinline', 'mod_interactembed'),
            'card' => get_string('displaymodecard', 'mod_interactembed'),
        ]
    ));
    $settings->add(new admin_setting_configcheckbox(
        'mod_interactembed/defaultloadonrequest',
        get_string('defaultloadonrequest', 'mod_interactembed'),
        get_string('defaultloadonrequest_desc', 'mod_interactembed'),
        0
    ));
    $settings->add(new admin_setting_configcheckbox(
        'mod_interactembed/defaultshowfullscreen',
        get_string('defaultshowfullscreen', 'mod_interactembed'),
        get_string('defaultshowfullscreen_desc', 'mod_interactembed'),
        1
    ));
    $settings->add(new admin_setting_configcheckbox(
        'mod_interactembed/defaultshowembedcode',
        get_string('defaultshowembedcode', 'mod_interactembed'),
        get_string('defaultshowembedcode_desc', 'mod_interactembed'),
        0
    ));
    $settings->add(new admin_setting_configcheckbox(
        'mod_interactembed/defaultlazyload',
        get_string('defaultlazyload', 'mod_interactembed'),
        get_string('defaultlazyload_desc', 'mod_interactembed'),
        1
    ));
    $settings->add(new admin_setting_configselect(
        'mod_interactembed/defaultsandboxprofile',
        get_string('defaultsandboxprofile', 'mod_interactembed'),
        get_string('defaultsandboxprofile_desc', 'mod_interactembed'),
        'standard',
        [
            'strict' => get_string('sandboxstrict', 'mod_interactembed'),
            'standard' => get_string('sandboxstandard', 'mod_interactembed'),
        ]
    ));

    $settings->add(new admin_setting_heading(
        'mod_interactembed/policiesheading',
        get_string('policiesheading', 'mod_interactembed'),
        get_string('policiesheading_desc', 'mod_interactembed')
    ));
    $settings->add(new admin_setting_configselect(
        'mod_interactembed/sourcepolicy',
        get_string('sourcepolicy', 'mod_interactembed'),
        get_string('sourcepolicy_desc', 'mod_interactembed'),
        'both',
        [
            'both' => get_string('sourcepolicyboth', 'mod_interactembed'),
            'urlonly' => get_string('sourcepolicyurlonly', 'mod_interactembed'),
            'packageonly' => get_string('sourcepolicypackageonly', 'mod_interactembed'),
        ]
    ));
    $settings->add(new admin_setting_configcheckbox(
        'mod_interactembed/allowembedcode',
        get_string('allowembedcode', 'mod_interactembed'),
        get_string('allowembedcode_desc', 'mod_interactembed'),
        1
    ));
    $settings->add(new admin_setting_configcheckbox(
        'mod_interactembed/forceremoteconsent',
        get_string('forceremoteconsent', 'mod_interactembed'),
        get_string('forceremoteconsent_desc', 'mod_interactembed'),
        0
    ));
    $settings->add(new admin_setting_configcheckbox(
        'mod_interactembed/allowgenericurls',
        get_string('allowgenericurls', 'mod_interactembed'),
        get_string('allowgenericurls_desc', 'mod_interactembed'),
        0
    ));
    $settings->add(new admin_setting_configtextarea(
        'mod_interactembed/alloweddomains',
        get_string('alloweddomains', 'mod_interactembed'),
        get_string('alloweddomains_desc', 'mod_interactembed'),
        '',
        PARAM_RAW_TRIMMED
    ));

    $settings->add(new admin_setting_heading(
        'mod_interactembed/limitsheading',
        get_string('limitsheading', 'mod_interactembed'),
        get_string('limitsheading_desc', 'mod_interactembed')
    ));
    $settings->add(new admin_setting_configtext(
        'mod_interactembed/maxarchivefiles',
        get_string('maxarchivefiles', 'mod_interactembed'),
        get_string('maxarchivefiles_desc', 'mod_interactembed'),
        5000,
        PARAM_INT
    ));
    $settings->add(new admin_setting_configtext(
        'mod_interactembed/maxarchivebytes',
        get_string('maxarchivebytes', 'mod_interactembed'),
        get_string('maxarchivebytes_desc', 'mod_interactembed'),
        209715200,
        PARAM_INT
    ));
}
