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
 * Interactive controls for one InteractEmbed player.
 *
 * @module     mod_interactembed/player
 * @copyright  2026 Michel Cardinal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string as getString} from 'core/str';

const progressKeys = ['type', 'version', 'nonce', 'status', 'score', 'scoremax'];

/**
 * Validates the progress message envelope and nonce.
 *
 * @param {*} message message received from the embedded content
 * @param {Object} tracking progress endpoint configuration
 * @returns {boolean}
 */
const isProgressMessage = (message, tracking) => {
    if (!message || typeof message !== 'object' || Array.isArray(message)) {
        return false;
    }
    return !Object.keys(message).some(key => !progressKeys.includes(key)) &&
        message.type === 'interactembed:progress' &&
        message.version === 1 &&
        typeof message.nonce === 'string' &&
        message.nonce === tracking.messagenonce &&
        typeof message.status === 'string' &&
        ['inprogress', 'completed'].includes(message.status);
};

/**
 * Validates an optional score pair.
 *
 * @param {Object} message validated progress message
 * @returns {boolean}
 */
const hasValidScore = message => {
    const hasScore = Object.prototype.hasOwnProperty.call(message, 'score');
    const hasScoreMax = Object.prototype.hasOwnProperty.call(message, 'scoremax');
    if (hasScore !== hasScoreMax) {
        return false;
    }
    return !hasScore || (Number.isFinite(message.score) &&
        Number.isFinite(message.scoremax) &&
        message.score >= 0 &&
        message.scoremax > 0 &&
        message.score <= message.scoremax);
};

/**
 * Creates the form-encoded progress payload.
 *
 * @param {Object} message validated progress message
 * @param {Object} tracking progress endpoint configuration
 * @returns {string}
 */
const createProgressPayload = (message, tracking) => {
    const values = {
        cmid: tracking.cmid,
        sesskey: tracking.sesskey,
        nonce: tracking.messagenonce,
        status: message.status === 'completed' ? 'completed' : 'inprogress',
    };
    if (Object.prototype.hasOwnProperty.call(message, 'score')) {
        values.score = message.score;
        values.scoremax = message.scoremax;
    }
    return new URLSearchParams(values).toString();
};

/**
 * Initialises one isolated InteractEmbed player.
 *
 * @param {string} rootId unique player element id
 * @param {Object} tracking progress endpoint configuration
 */
export const init = (rootId, tracking = {}) => {
    const root = document.getElementById(rootId);
    if (!root || root.dataset.initialised === 'true') {
        return;
    }
    root.dataset.initialised = 'true';

    const iframe = root.querySelector('.mod-interactembed-iframe');
    let lastPayload = '';
    const receiveProgress = async event => {
        const message = event.data;
        if (!iframe || event.source !== iframe.contentWindow || !isProgressMessage(message, tracking)) {
            return;
        }
        const originMatches = event.origin === tracking.expectedorigin ||
            (tracking.allowopaqueorigin && event.origin === 'null');
        if (!originMatches || !tracking.endpoint || !tracking.sesskey) {
            return;
        }
        if (!hasValidScore(message)) {
            return;
        }
        const payload = createProgressPayload(message, tracking);
        if (payload === lastPayload) {
            return;
        }
        lastPayload = payload;
        try {
            const response = await fetch(tracking.endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'},
                body: payload,
            });
            if (!response.ok) {
                lastPayload = '';
            }
        } catch (error) {
            lastPayload = '';
        }
    };
    window.addEventListener('message', receiveProgress);
    const initialiseContent = () => {
        if (!iframe || !iframe.contentWindow || !tracking.messagenonce) {
            return;
        }
        iframe.contentWindow.postMessage({
            type: 'interactembed:init',
            version: 1,
            nonce: tracking.messagenonce,
        }, '*');
    };
    iframe.addEventListener('load', initialiseContent);
    initialiseContent();
    const loadButton = root.querySelector('[data-action="load-content"]');
    if (loadButton) {
        loadButton.addEventListener('click', () => {
            const frame = root.querySelector('.mod-interactembed-frame');
            const launch = root.querySelector('.mod-interactembed-launch');
            const actions = root.querySelector('.mod-interactembed-actions');
            if (iframe.dataset.src) {
                iframe.src = iframe.dataset.src;
                delete iframe.dataset.src;
            }
            launch.hidden = true;
            frame.hidden = false;
            actions.hidden = false;
            iframe.addEventListener('load', () => iframe.focus(), {once: true});
        });
    }
    const fullscreen = root.querySelector('[data-action="fullscreen"]');
    if (fullscreen) {
        let nativeFullscreenActive = false;
        const label = fullscreen.querySelector('[data-role="fullscreen-label"]');
        const fallbackClass = 'mod-interactembed-player--fullscreen-fallback';
        const bodyClass = 'mod-interactembed-body-locked';
        const focusableSelector = 'a[href], button:not([disabled]), textarea:not([disabled]), ' +
            'input:not([disabled]), select:not([disabled]), iframe, [tabindex]:not([tabindex="-1"])';
        const fallbackFocusables = () => Array.from(root.querySelectorAll(focusableSelector)).filter(element =>
            !element.closest('[hidden]') && element.getAttribute('aria-hidden') !== 'true'
        );
        const updateFullscreenControl = active => {
            fullscreen.setAttribute('aria-pressed', active ? 'true' : 'false');
            const stringId = active ? 'exitfullscreen' : 'fullscreen';
            return getString(stringId, 'mod_interactembed').then(value => {
                label.textContent = value;
                return value;
            });
        };
        const exitFallback = () => {
            if (!root.classList.contains(fallbackClass)) {
                return;
            }
            root.classList.remove(fallbackClass);
            document.body.classList.remove(bodyClass);
            root.removeAttribute('role');
            root.removeAttribute('aria-modal');
            root.removeAttribute('aria-label');
            updateFullscreenControl(false);
            fullscreen.focus();
        };
        const enterFallback = () => {
            root.classList.add(fallbackClass);
            document.body.classList.add(bodyClass);
            root.setAttribute('role', 'dialog');
            root.setAttribute('aria-modal', 'true');
            root.setAttribute('aria-label', root.dataset.fullscreenLabel);
            updateFullscreenControl(true);
        };
        const trapFallbackFocus = event => {
            if (event.key !== 'Tab' || !root.classList.contains(fallbackClass)) {
                return;
            }
            const focusables = fallbackFocusables();
            if (!focusables.length) {
                event.preventDefault();
                fullscreen.focus();
                return;
            }
            const first = focusables[0];
            const last = focusables[focusables.length - 1];
            const outside = !root.contains(document.activeElement);
            if (event.shiftKey && (document.activeElement === first || outside)) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && (document.activeElement === last || outside)) {
                event.preventDefault();
                first.focus();
            }
        };

        fullscreen.addEventListener('click', async() => {
            if (root.classList.contains(fallbackClass)) {
                exitFallback();
                return;
            }
            if (document.fullscreenElement === root) {
                await document.exitFullscreen();
                return;
            }
            if (!root.requestFullscreen) {
                enterFallback();
                return;
            }
            try {
                await root.requestFullscreen();
            } catch (error) {
                enterFallback();
            }
        });
        document.addEventListener('fullscreenchange', () => {
            const active = document.fullscreenElement === root;
            if (active) {
                nativeFullscreenActive = true;
                updateFullscreenControl(true);
            } else if (nativeFullscreenActive) {
                nativeFullscreenActive = false;
                updateFullscreenControl(false);
                fullscreen.focus();
            }
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                exitFallback();
                return;
            }
            trapFallbackFocus(event);
        });
    }

    const copyButton = root.querySelector('[data-action="copy-embed"]');
    if (copyButton) {
        copyButton.addEventListener('click', async() => {
            const textarea = root.querySelector('textarea');
            const status = root.querySelector('.mod-interactembed-copy-status');
            try {
                await navigator.clipboard.writeText(textarea.value);
                status.textContent = await getString('embedcodecopied', 'mod_interactembed');
            } catch (error) {
                textarea.focus();
                textarea.select();
                document.execCommand('copy');
                status.textContent = await getString('embedcodecopied', 'mod_interactembed');
            }
        });
    }
};
