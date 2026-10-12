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

/**
 * Same-origin parent-page resizer for copied InteractEmbed iframe code.
 *
 * @module     mod_interactembed/embed_resizer
 * @copyright  2026 Michel Cardinal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Compatibility URL for snippets copied before 1.0.1.
// Resizing is initialised by Moodle through mod_interactembed/embed_resizer.
/**
 * Initialises resizing of same-site copied iframe snippets.
 */
export const init = () => {
    if (window.modInteractEmbedResizerInitialised) {
        return;
    }
    window.modInteractEmbedResizerInitialised = true;
    window.addEventListener('message', function(event) {
        if (event.origin !== window.location.origin || !event.data || event.data.type !== 'mod_interactembed:resize') {
            return;
        }
        const cmid = Number(event.data.cmid);
        const height = Number(event.data.height);
        if (!Number.isInteger(cmid) || !Number.isFinite(height) || height < 200 || height > 4000) {
            return;
        }
        document.querySelectorAll('iframe[data-interactembed-id="' + cmid + '"]').forEach(function(frame) {
            if (frame.contentWindow === event.source) {
                frame.style.height = Math.ceil(height) + 'px';
                frame.setAttribute('height', String(Math.ceil(height)));
            }
        });
    });
};
