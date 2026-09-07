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
 * English strings for the InteractEmbed activity.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['interactembed:addinstance'] = 'Add a new InteractEmbed activity';
$string['interactembed:view'] = 'View an InteractEmbed activity';
$string['interactembed:submitprogress'] = 'Submit own InteractEmbed progress';
$string['interactembed:viewreports'] = 'View InteractEmbed learner reports';
$string['interactembed:managegrades'] = 'Manage InteractEmbed grades';
$string['interactembed:trustpackage'] = 'Use the trusted HTML5 package profile';
$string['invalidprogressnonce'] = 'This progress message is expired or does not belong to this activity display.';
$string['completioncontent'] = 'Learner must complete the interactive content';
$string['completiondetail:content'] = 'Complete the interactive content';
$string['eventprogressupdated'] = 'Interactive progress updated';
$string['reporttitle'] = 'Learner progress';
$string['resetprogress'] = 'Delete all learner progress and results';
$string['lastinteraction'] = 'Last interaction';
$string['progressstatus:notstarted'] = 'Not started';
$string['progressstatus:inprogress'] = 'In progress';
$string['progressstatus:completed'] = 'Completed';
$string['privacy:metadata:progress'] = 'Learner progress explicitly reported by interactive content.';
$string['privacy:metadata:externalcontent'] = 'When configured external content is displayed, the learner’s browser connects directly to that provider. InteractEmbed does not add Moodle profile or grade data to the request.';
$string['privacy:metadata:externalcontent:ipaddress'] = 'The external provider receives the IP address used for the browser connection.';
$string['privacy:metadata:externalcontent:useragent'] = 'The external provider receives the browser user-agent information.';
$string['privacy:metadata:progress:userid'] = 'The learner whose progress is recorded.';
$string['privacy:metadata:progress:status'] = 'Whether the content is in progress or completed.';
$string['privacy:metadata:progress:score'] = 'The raw score reported by the content.';
$string['privacy:metadata:progress:scoremax'] = 'The maximum raw score reported by the content.';
$string['privacy:metadata:progress:firstinteraction'] = 'Time of the first recorded interaction.';
$string['privacy:metadata:progress:lastinteraction'] = 'Time of the latest recorded interaction.';
$string['privacy:metadata:progress:timecompleted'] = 'Time at which completion was first reported.';
$string['content'] = 'Interactive content';
$string['entrypoint'] = 'Entry point';
$string['entrypoint_help'] = 'Relative path of the HTML file that starts the package, for example index.html or course/start.html.';
$string['modulename'] = 'InteractEmbed';
$string['modulename_help'] = 'InteractEmbed will provide standards-based interactive HTML5 content and external embeds as a Moodle activity.';
$string['modulename_summary'] = 'Add interactive HTML5 content or an external interactive resource to a course.';
$string['modulename_tip'] = 'Use InteractEmbed when learner interaction, completion tracking, or grading is required.';
$string['modulenameplural'] = 'InteractEmbed activities';
$string['noinstances'] = 'There are no InteractEmbed activities in this course.';
$string['packageentryinvalid'] = 'The package entry point must be a safe relative .html or .htm path.';
$string['packageentrymissing'] = 'The configured HTML entry point was not found and no unique index.html is available.';
$string['packagefiles'] = 'HTML5 package files';
$string['packagefiles_help'] = 'Select one ZIP archive, or select unpacked project files and folders. Auxiliary files such as README.txt are accepted.';
$string['packagefilesrequired'] = 'Select a ZIP archive or unpacked HTML5 project files.';
$string['packageinvalidarchive'] = 'The selected ZIP archive is invalid.';
$string['packageonlyonearchive'] = 'Select either one ZIP archive by itself or unpacked files without a ZIP.';
$string['packageunsafearchive'] = 'The ZIP archive contains an unsafe path, unsupported nested archive, or exceeds the configured limits.';
$string['packageunsafefile'] = 'The package contains an unsafe file path: {$a}';
$string['pluginadministration'] = 'InteractEmbed administration';
$string['pluginname'] = 'InteractEmbed';
$string['privacy:metadata'] = 'InteractEmbed package content does not store personal data.';
$string['sourcetype'] = 'Content source';
$string['sourcetype_help'] = 'Use your own HTML5 package or display the interactive guide included with InteractEmbed.';
$string['sourcetypeguide'] = 'Included interactive guide';
$string['sourcetypepackage'] = 'HTML5 package';
$string['sourcetypeurl'] = 'Web address or iframe';
$string['sourceurl'] = 'Web address or iframe code';
$string['sourceurl_help'] = 'Paste an HTTP(S) address or complete iframe code. InteractEmbed extracts only the iframe source and safely converts supported providers.';
$string['sourceurlinvalid'] = 'Enter a valid, permitted HTTP(S) address or iframe.';
$string['aspectratio'] = 'Display format';
$string['coveralt'] = 'Cover image description';
$string['coveralt_help'] = 'Briefly describe the meaningful content of the cover image for people using assistive technology.';
$string['coveraltrequired'] = 'Describe the cover image, or remove the image.';
$string['coverimage'] = 'Cover image';
$string['coverimage_help'] = 'Optional image displayed in compact-card or click-to-load mode. Maximum size: 5 MB.';
$string['coverimageguidance'] = 'Recommended: WebP under 500 KB. Suggested dimensions: 1600 × 900 px (16:9), 1600 × 1200 px (4:3), 1200 × 1200 px (1:1), 1200 × 1600 px (3:4), or 900 × 1600 px (9:16). Maximum: 5 MB. Keep important content centred because the image may be cropped.';
$string['copyembedcode'] = 'Copy embed code';
$string['customheight'] = 'Custom height (pixels)';
$string['customheighterror'] = 'Enter a height between 200 and 4000 pixels.';
$string['displaymode'] = 'Display mode';
$string['displaymodecard'] = 'Compact card, then open the player';
$string['displaymodeinline'] = 'Inline player';
$string['embedcode'] = 'Embed code';
$string['embedcodecopied'] = 'Embed code copied.';
$string['embedcodedesc'] = 'Use this code in another HTML-capable area of this Moodle site. Access permissions remain unchanged.';
$string['exitfullscreen'] = 'Exit full screen';
$string['fullscreen'] = 'View full screen';
$string['lazyload'] = 'Load content only when it approaches the screen';
$string['loadcontent'] = 'Load interactive content';
$string['loadcontentdesc'] = 'The content will load after you select the button. External content may connect to another service.';
$string['loadonrequest'] = 'Require a click before loading the content';
$string['loadonrequest_help'] = 'Prevents the iframe from loading until the visitor selects the load button. This can improve initial performance and privacy. Compact card mode always uses this behaviour.';
$string['openexternal'] = 'Open content in a new window';
$string['presentation'] = 'Presentation and behaviour';
$string['ratio16x9'] = 'Widescreen (16:9)';
$string['ratio1x1'] = 'Square (1:1)';
$string['ratio3x4'] = 'Portrait (3:4)';
$string['ratio4x3'] = 'Classic (4:3)';
$string['ratio9x16'] = 'Vertical / mobile (9:16)';
$string['ratiocustom'] = 'Custom height';
$string['sandboxprofile'] = 'Content security profile';
$string['sandboxprofile_help'] = 'Strict is recommended for unknown content. Standard supports most trusted presentations. Trusted permits same-origin access and must be reserved for audited HTML5 files you control.';
$string['sandboxstandard'] = 'Standard';
$string['sandboxstrict'] = 'Strict';
$string['sandboxtrusted'] = 'Trusted package';
$string['showfullscreen'] = 'Show full-screen button';
$string['showembedcode'] = 'Show embed code';
$string['showembedcode_help'] = 'Shows a reusable, access-controlled iframe code only to users who can manage activities. Learners never see this tool.';
$string['sourcenotallowed'] = 'This content source is disabled by the InteractEmbed site policy.';
$string['alloweddomains'] = 'Additional allowed domains';
$string['alloweddomains_desc'] = 'One domain per line. The domain and its subdomains will be accepted. Recognised providers do not need to be listed.';
$string['allowembedcode'] = 'Allow embed code';
$string['allowembedcode_desc'] = 'Allow activity editors to expose copyable iframe code for reuse within the same Moodle site.';
$string['allowgenericurls'] = 'Allow generic URLs';
$string['allowgenericurls_desc'] = 'Allow editors to embed any valid HTTP or HTTPS address that is not a recognised provider or listed below. Enable this only for trusted editors.';
$string['defaultaspectratio'] = 'Default display format';
$string['defaultaspectratio_desc'] = 'Initial format selected for a new InteractEmbed activity.';
$string['defaultcustomheight'] = 'Default custom height';
$string['defaultcustomheight_desc'] = 'Initial custom height in pixels.';
$string['defaultdisplaymode'] = 'Default display mode';
$string['defaultdisplaymode_desc'] = 'Initial display mode selected for new activities.';
$string['defaultlazyload'] = 'Enable deferred loading by default';
$string['defaultlazyload_desc'] = 'Initial value of deferred loading for new activities.';
$string['defaultloadonrequest'] = 'Require a click by default';
$string['defaultloadonrequest_desc'] = 'Initial value of click-to-load for new activities.';
$string['defaultsandboxprofile'] = 'Default security profile';
$string['defaultsandboxprofile_desc'] = 'Initial security profile. Trusted must always be selected explicitly by an authorised editor.';
$string['defaultsheading'] = 'Defaults for new activities';
$string['defaultsheading_desc'] = 'These values initialise new activities. They do not change existing activities unless a site policy overrides them.';
$string['defaultshowembedcode'] = 'Show embed code by default';
$string['defaultshowembedcode_desc'] = 'Initial value of the embed-code option for new activities.';
$string['defaultshowfullscreen'] = 'Show full-screen button by default';
$string['defaultshowfullscreen_desc'] = 'Initial value of the full-screen option for new activities.';
$string['forceremoteconsent'] = 'Always require a click for remote content';
$string['forceremoteconsent_desc'] = 'Force web-address and iframe sources to wait for visitor interaction before loading.';
$string['limitsheading'] = 'HTML5 package limits';
$string['limitsheading_desc'] = 'Safety limits applied when ZIP packages are inspected and extracted.';
$string['maxarchivebytes'] = 'Maximum extracted archive size';
$string['maxarchivebytes_desc'] = 'Maximum total uncompressed size, in bytes, accepted from one ZIP archive.';
$string['maxarchivefiles'] = 'Maximum archive files';
$string['maxarchivefiles_desc'] = 'Maximum number of entries accepted in one ZIP archive.';
$string['policiesheading'] = 'Site policies';
$string['policiesheading_desc'] = 'These settings restrict or enforce sensitive features across all InteractEmbed activities.';
$string['sourcepolicy'] = 'Allowed content sources';
$string['sourcepolicy_desc'] = 'Choose which sources may be configured and displayed. The included guide always remains available.';
$string['sourcepolicyboth'] = 'Web addresses, iframes and HTML5 packages';
$string['sourcepolicypackageonly'] = 'HTML5 packages only';
$string['sourcepolicyurlonly'] = 'Web addresses and iframes only';
$string['packageorigin'] = 'Package origin';
$string['packageoriginupload'] = 'My files';
$string['packageoriginlibrary'] = 'InteractEmbed Library';
$string['creatorpublication'] = 'Library publication';
$string['creatorselectpublication'] = 'Choose a publication…';
$string['creatorpublicationnumber'] = 'Publication {$a}';
$string['creatorpublicationinvalid'] = 'The selected Library publication is unavailable or not permitted in this course.';
$string['creatorunavailable'] = 'The InteractEmbed Library is unavailable or incompatible.';
