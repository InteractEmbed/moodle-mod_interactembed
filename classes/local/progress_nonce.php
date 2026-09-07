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

/**
 * Per-display nonce management for learner progress messages.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_interactembed\local;

/**
 * Stores short-lived progress nonces in the authenticated Moodle session.
 */
final class progress_nonce {
    /** Nonce lifetime in seconds. */
    private const LIFETIME = 7200;

    /** Maximum active nonces retained per session. */
    private const MAX_NONCES = 20;

    /**
     * Issues a fresh nonce for one course module.
     *
     * @param int $cmid Course-module id.
     * @return string Fresh nonce.
     */
    public static function issue(int $cmid): string {
        global $SESSION;
        self::prune();
        $nonce = random_string(32);
        if (empty($SESSION->interactembedprogressnonces)) {
            $SESSION->interactembedprogressnonces = [];
        }
        $SESSION->interactembedprogressnonces[$nonce] = ['cmid' => $cmid, 'time' => time()];
        if (count($SESSION->interactembedprogressnonces) > self::MAX_NONCES) {
            uasort($SESSION->interactembedprogressnonces, static function (array $left, array $right): int {
                return $left['time'] <=> $right['time'];
            });
            while (count($SESSION->interactembedprogressnonces) > self::MAX_NONCES) {
                array_shift($SESSION->interactembedprogressnonces);
            }
        }
        return $nonce;
    }

    /**
     * Validates a nonce against its course module and session lifetime.
     *
     * @param int $cmid Course-module id.
     * @param string $nonce Submitted nonce.
     * @return bool Whether the nonce is current and correctly scoped.
     */
    public static function validate(int $cmid, string $nonce): bool {
        global $SESSION;
        self::prune();
        $record = $SESSION->interactembedprogressnonces[$nonce] ?? null;
        return is_array($record) && (int) $record['cmid'] === $cmid;
    }

    /**
     * Removes expired nonce records.
     */
    private static function prune(): void {
        global $SESSION;
        if (
            empty($SESSION->interactembedprogressnonces) ||
                !is_array($SESSION->interactembedprogressnonces)
        ) {
            $SESSION->interactembedprogressnonces = [];
            return;
        }
        $minimum = time() - self::LIFETIME;
        foreach ($SESSION->interactembedprogressnonces as $nonce => $record) {
            if (!is_array($record) || ($record['time'] ?? 0) < $minimum) {
                unset($SESSION->interactembedprogressnonces[$nonce]);
            }
        }
    }
}
