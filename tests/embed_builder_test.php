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

namespace mod_interactembed;

use mod_interactembed\local\embed\embed_builder;

/**
 * Tests for safe URL extraction and provider conversion.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class embed_builder_test extends \basic_testcase {
    /**
     * Tests direct YouTube conversion.
     */
    public function test_youtube_share_url_is_converted(): void {
        $result = embed_builder::build('https://youtu.be/dQw4w9WgXcQ', false);
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $result->get_url());
        $this->assertSame('youtube', $result->get_provider());
    }

    /**
     * Tests that iframe attributes other than src are discarded.
     */
    public function test_only_iframe_src_is_used(): void {
        $input = '<iframe src="https://example.org/demo" onload="alert(1)" style="position:fixed"></iframe>';
        $result = embed_builder::build($input, true);
        $this->assertSame('https://example.org/demo', $result->get_url());
        $this->assertSame('generic', $result->get_provider());
    }

    /**
     * Tests common Google Slides sharing URLs.
     */
    public function test_google_slides_is_converted(): void {
        $result = embed_builder::build('https://docs.google.com/presentation/d/abc_123/edit?usp=sharing', false);
        $this->assertSame('https://docs.google.com/presentation/d/abc_123/embed', $result->get_url());
    }

    /**
     * Tests Google document sharing URLs.
     */
    public function test_google_document_is_converted(): void {
        $this->assert_provider_url(
            'https://docs.google.com/document/d/document_123/edit',
            'https://docs.google.com/document/d/document_123/preview',
            'googleworkspace'
        );
    }

    /**
     * Tests Google spreadsheet sharing URLs.
     */
    public function test_google_spreadsheet_is_converted(): void {
        $this->assert_provider_url(
            'https://docs.google.com/spreadsheets/d/sheet_123/edit',
            'https://docs.google.com/spreadsheets/d/sheet_123/preview',
            'googleworkspace'
        );
    }

    /**
     * Tests Google form sharing URLs.
     */
    public function test_google_form_is_converted(): void {
        $this->assert_provider_url(
            'https://docs.google.com/forms/d/e/form_123/viewform',
            'https://docs.google.com/forms/d/e/form_123/viewform?embedded=true',
            'googleworkspace'
        );
    }

    /**
     * Tests Canva sharing URLs.
     */
    public function test_canva_is_converted(): void {
        $this->assert_provider_url(
            'https://www.canva.com/design/DESIGN_123/view?utm_content=share',
            'https://www.canva.com/design/DESIGN_123/view?embed',
            'canva'
        );
    }

    /**
     * Tests Genially sharing URLs.
     */
    public function test_genially_is_converted(): void {
        $this->assert_provider_url(
            'https://view.genially.com/GENIALLY_123/interactive-content-demo',
            'https://view.genially.com/GENIALLY_123/interactive-content-demo',
            'genially'
        );
    }

    /**
     * Tests Padlet sharing URLs.
     */
    public function test_padlet_is_converted(): void {
        $this->assert_provider_url(
            'https://padlet.com/example/demo',
            'https://padlet.com/example/demo',
            'padlet'
        );
    }

    /**
     * Tests Wayground sharing URLs.
     */
    public function test_wayground_is_converted(): void {
        $this->assert_provider_url(
            'https://wayground.com/join?gc=123456',
            'https://wayground.com/join?gc=123456',
            'wayground'
        );
    }

    /**
     * Tests active URL schemes are rejected.
     */
    public function test_active_scheme_is_rejected(): void {
        $this->expectException(\InvalidArgumentException::class);
        embed_builder::build('javascript:alert(1)', true);
    }

    /**
     * Tests arbitrary markup is rejected.
     */
    public function test_non_iframe_markup_is_rejected(): void {
        $this->expectException(\InvalidArgumentException::class);
        embed_builder::build('<script>alert(1)</script>', true);
    }

    /**
     * Tests that credentials cannot be hidden in an otherwise valid URL.
     */
    public function test_url_credentials_are_rejected(): void {
        $this->expectException(\InvalidArgumentException::class);
        embed_builder::build('https://user:password@example.org/demo', true);
    }

    /**
     * Tests that deceptive provider suffixes are not treated as trusted domains.
     */
    public function test_deceptive_provider_domain_is_not_recognised(): void {
        $result = embed_builder::build('https://youtube.com.example.org/watch?v=dQw4w9WgXcQ', true);
        $this->assertSame('generic', $result->get_provider());
    }

    /**
     * Tests unknown providers can be administratively disabled.
     */
    public function test_generic_url_can_be_disabled(): void {
        $this->expectException(\InvalidArgumentException::class);
        embed_builder::build('https://example.org/demo', false, '');
    }

    /**
     * Tests that an administrator allow-list enables a generic provider.
     */
    public function test_generic_url_can_be_allowed_by_domain(): void {
        $result = embed_builder::build('https://content.example.org/demo', false, 'example.org');
        $this->assertSame('generic', $result->get_provider());
    }

    /**
     * Asserts a smart-provider conversion.
     *
     * @param string $source Shared URL.
     * @param string $expected Expected iframe URL.
     * @param string $provider Expected provider identifier.
     */
    private function assert_provider_url(string $source, string $expected, string $provider): void {
        $result = embed_builder::build($source, false);
        $this->assertSame($expected, $result->get_url());
        $this->assertSame($provider, $result->get_provider());
    }
}
