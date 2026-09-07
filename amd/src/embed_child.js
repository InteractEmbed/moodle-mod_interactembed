// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Reports an embedded activity document's height to its Moodle parent.
 *
 * @module     mod_interactembed/embed_child
 * @copyright  2026 Michel Cardinal
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Starts automatic height reporting for an embedded activity.
 *
 * @param {number|string} cmid Course module identifier.
 */
export const init = cmid => {
    if (window.parent === window) {
        return;
    }
    let previousHeight = 0;
    const report = () => {
        const height = Math.max(200, Math.min(4000, Math.ceil(document.documentElement.scrollHeight)));
        if (height === previousHeight) {
            return;
        }
        previousHeight = height;
        window.parent.postMessage({
            type: 'mod_interactembed:resize',
            cmid: Number(cmid),
            height,
        }, window.location.origin);
    };
    if ('ResizeObserver' in window) {
        new ResizeObserver(report).observe(document.documentElement);
    }
    window.addEventListener('load', report, {once: true});
    report();
};
