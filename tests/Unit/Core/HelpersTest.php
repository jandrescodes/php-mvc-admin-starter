<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Tests\TestCase;

class HelpersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clean env keys used in tests
        unset($_ENV['TEST_KEY'], $_SERVER['TEST_KEY']);
        putenv('TEST_KEY');
    }

    protected function tearDown(): void
    {
        unset($_ENV['TEST_KEY'], $_SERVER['TEST_KEY']);
        putenv('TEST_KEY');
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // env()
    // -------------------------------------------------------------------------

    public function test_env_returns_default_when_key_missing(): void
    {
        $this->assertSame('fallback', env('TEST_KEY', 'fallback'));
    }

    public function test_env_returns_null_default_when_no_default_given(): void
    {
        $this->assertNull(env('TEST_KEY'));
    }

    public function test_env_casts_true_string(): void
    {
        $_ENV['TEST_KEY'] = 'true';
        $this->assertTrue(env('TEST_KEY'));
    }

    public function test_env_casts_true_with_parens(): void
    {
        $_ENV['TEST_KEY'] = '(true)';
        $this->assertTrue(env('TEST_KEY'));
    }

    public function test_env_casts_false_string(): void
    {
        $_ENV['TEST_KEY'] = 'false';
        $this->assertFalse(env('TEST_KEY'));
    }

    public function test_env_casts_false_with_parens(): void
    {
        $_ENV['TEST_KEY'] = '(false)';
        $this->assertFalse(env('TEST_KEY'));
    }

    public function test_env_casts_null_string(): void
    {
        $_ENV['TEST_KEY'] = 'null';
        $this->assertNull(env('TEST_KEY'));
    }

    public function test_env_casts_null_with_parens(): void
    {
        $_ENV['TEST_KEY'] = '(null)';
        $this->assertNull(env('TEST_KEY'));
    }

    public function test_env_casts_empty_string(): void
    {
        $_ENV['TEST_KEY'] = 'empty';
        $this->assertSame('', env('TEST_KEY'));
    }

    public function test_env_casts_empty_with_parens(): void
    {
        $_ENV['TEST_KEY'] = '(empty)';
        $this->assertSame('', env('TEST_KEY'));
    }

    public function test_env_returns_raw_string_value(): void
    {
        $_ENV['TEST_KEY'] = 'hello-world';
        $this->assertSame('hello-world', env('TEST_KEY'));
    }

    public function test_env_reads_from_server_superglobal(): void
    {
        $_SERVER['TEST_KEY'] = 'from_server';
        $this->assertSame('from_server', env('TEST_KEY'));
    }

    public function test_env_prefers_env_over_server(): void
    {
        $_ENV['TEST_KEY']    = 'from_env';
        $_SERVER['TEST_KEY'] = 'from_server';
        $this->assertSame('from_env', env('TEST_KEY'));
    }

    // -------------------------------------------------------------------------
    // generateCSRFToken()
    // -------------------------------------------------------------------------

    public function test_generate_csrf_token_returns_64_char_hex(): void
    {
        $token = generateCSRFToken();
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token);
    }

    public function test_generate_csrf_token_stores_in_session(): void
    {
        $token = generateCSRFToken();
        $this->assertSame($token, $_SESSION['csrf_token']);
    }

    public function test_generate_csrf_token_is_idempotent_when_already_set(): void
    {
        $_SESSION['csrf_token'] = 'existing_token';
        $token = generateCSRFToken();
        $this->assertSame('existing_token', $token);
    }

    // -------------------------------------------------------------------------
    // verifyCSRFToken()
    // -------------------------------------------------------------------------

    public function test_verify_csrf_token_returns_true_on_match(): void
    {
        $_SESSION['csrf_token'] = 'abc123';
        $this->assertTrue(verifyCSRFToken('abc123'));
    }

    public function test_verify_csrf_token_returns_false_on_mismatch(): void
    {
        $_SESSION['csrf_token'] = 'abc123';
        $this->assertFalse(verifyCSRFToken('wrong'));
    }

    public function test_verify_csrf_token_returns_false_for_non_string_input(): void
    {
        $_SESSION['csrf_token'] = 'abc123';
        $this->assertFalse(verifyCSRFToken(['abc123']));
        $this->assertFalse(verifyCSRFToken(null));
    }

    public function test_verify_csrf_token_returns_false_when_session_missing(): void
    {
        $this->assertFalse(verifyCSRFToken('anything'));
    }

    // -------------------------------------------------------------------------
    // regenerateCSRFToken()
    // -------------------------------------------------------------------------

    public function test_regenerate_csrf_token_replaces_existing_token(): void
    {
        $_SESSION['csrf_token'] = 'old_token';
        $new = regenerateCSRFToken();
        $this->assertNotSame('old_token', $new);
        $this->assertSame($new, $_SESSION['csrf_token']);
    }

    public function test_regenerate_csrf_token_returns_64_char_hex(): void
    {
        $token = regenerateCSRFToken();
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token);
    }
}
