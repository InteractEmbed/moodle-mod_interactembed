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

use mod_interactembed\local\package\package_response_policy;
use mod_interactembed\local\sandbox_policy;

/**
 * Tests the signed package response and sandbox policy.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class package_response_policy_test extends \basic_testcase {
    /**
     * Verifies the headers required by opaque-origin package fetches.
     */
    public function test_resource_headers_allow_credentialless_opaque_origin_fetches(): void {
        $headers = package_response_policy::headers('project.json', 'standard');

        $this->assertSame('*', $headers['Access-Control-Allow-Origin']);
        $this->assertSame('GET, HEAD, OPTIONS', $headers['Access-Control-Allow-Methods']);
        $this->assertSame('cross-origin', $headers['Cross-Origin-Resource-Policy']);
        $this->assertSame('no-referrer', $headers['Referrer-Policy']);
        $this->assertSame('nosniff', $headers['X-Content-Type-Options']);
        $this->assertArrayNotHasKey('Access-Control-Allow-Credentials', $headers);
        $this->assertArrayNotHasKey('Content-Security-Policy', $headers);
    }

    /**
     * Verifies HTML CSP parity with each iframe sandbox profile.
     */
    public function test_html_csp_uses_the_selected_sandbox_profile(): void {
        foreach (['strict', 'standard', 'trusted'] as $profile) {
            $headers = package_response_policy::headers('INDEX.HTML', $profile);
            $this->assertSame(
                'sandbox ' . sandbox_policy::permissions($profile) . "; frame-ancestors 'self'",
                $headers['Content-Security-Policy']
            );
        }
    }

    /**
     * Verifies least-privilege permissions and a safe legacy fallback.
     */
    public function test_sandbox_permissions_do_not_let_popups_escape(): void {
        $this->assertStringNotContainsString(
            'allow-popups-to-escape-sandbox',
            sandbox_policy::permissions('standard')
        );
        $this->assertStringNotContainsString('allow-same-origin', sandbox_policy::permissions('strict'));
        $this->assertStringNotContainsString('allow-same-origin', sandbox_policy::permissions('standard'));
        $this->assertStringContainsString('allow-same-origin', sandbox_policy::permissions('trusted'));
        $this->assertSame('standard', sandbox_policy::normalise('unexpected'));
    }
}
