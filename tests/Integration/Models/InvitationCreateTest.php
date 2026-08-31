<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\Models\PasswordReset;
use App\Models\User;
use Tests\IntegrationTestCase;

/**
 * Integration tests for Sub-fase 2.2:
 * - Invited user is created with status=pending and an unusable password
 * - Invitation token is created with 48 h TTL
 * - Normal user creation flow is unaffected (regression)
 */
class InvitationCreateTest extends IntegrationTestCase
{
    private User          $userModel;
    private PasswordReset $resets;

    /** Base data shared across tests — override per-test as needed */
    private array $baseData = [
        'name'            => 'Invited',
        'first_surname'   => 'User',
        'document_type'   => 'DNI',
        'document_number' => '99999901',
        'email'           => 'invited@test.com',
        'role_id'         => 2,
        'image'           => null,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
        $this->resets    = new PasswordReset();
    }

    // -------------------------------------------------------------------------
    // Invited user — status = pending, password unusable
    // -------------------------------------------------------------------------

    public function test_create_invited_user_sets_status_pending(): void
    {
        // prepareUserData stores a pre-hashed placeholder for pending users;
        // User::create() hashes it again — that's fine, the password is unusable by design.
        $data = array_merge($this->baseData, [
            'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            'status'   => User::STATUS_PENDING,
        ]);

        $this->assertTrue($this->userModel->create($data));

        $userId = $this->userModel->getLastInsertId();
        $row    = $this->userModel->getById($userId);

        $this->assertSame(User::STATUS_PENDING, (int) $row['status']);
    }

    public function test_invited_user_has_unusable_password(): void
    {
        $unusableHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

        $data = array_merge($this->baseData, [
            'password' => $unusableHash,
            'status'   => User::STATUS_PENDING,
        ]);

        $this->assertTrue($this->userModel->create($data));

        $userId = $this->userModel->getLastInsertId();
        $row    = $this->userModel->getById($userId);

        // The stored hash must NOT verify against the original random bytes
        // (we can't reverse it — just confirm the hash is present and well-formed)
        $this->assertNotEmpty($row['password']);
        $this->assertStringStartsWith('$2y$', $row['password']);

        // Must not verify against a known string (not a trivial password)
        $this->assertFalse(password_verify('password', $row['password']));
        $this->assertFalse(password_verify('admin123', $row['password']));
    }

    public function test_create_invited_user_creates_invitation_token_48h(): void
    {
        $data = array_merge($this->baseData, [
            'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            'status'   => User::STATUS_PENDING,
        ]);

        $this->assertTrue($this->userModel->create($data));
        $userId = $this->userModel->getLastInsertId();

        $token = $this->resets->create($userId, 'invitation');
        $this->assertNotEmpty($token);

        $row = $this->resets->findValidByToken($token, 'invitation');
        $this->assertIsArray($row);
        $this->assertSame($userId, (int) $row['user_id']);
        $this->assertSame('invitation', $row['type']);

        // expires_at should be ~48 h from now (allow ±5 min drift)
        $expiresAt = strtotime($row['expires_at']);
        $expected  = time() + (48 * 3600);
        $this->assertEqualsWithDelta($expected, $expiresAt, 300);
    }

    // -------------------------------------------------------------------------
    // Regression — normal user creation unaffected
    // -------------------------------------------------------------------------

    public function test_create_normal_user_still_active_with_real_password(): void
    {
        // User::create() hashes $data['password'] internally — pass plain text
        $data = array_merge($this->baseData, [
            'document_number' => '99999902',
            'email'           => 'normal_new@test.com',
            'password'        => 'securePass1',
            'status'          => User::STATUS_ACTIVE,
        ]);

        $this->assertTrue($this->userModel->create($data));

        $userId = $this->userModel->getLastInsertId();
        $row    = $this->userModel->getById($userId);

        $this->assertSame(User::STATUS_ACTIVE, (int) $row['status']);
        $this->assertTrue(password_verify('securePass1', $row['password']));
    }

    // -------------------------------------------------------------------------
    // validateData — pending skips password requirement
    // -------------------------------------------------------------------------

    public function test_validate_data_allows_empty_password_for_pending(): void
    {
        $data = array_merge($this->baseData, [
            'document_number' => '99999904',
            'email'           => 'pending_no_pass@test.com',
            'password'        => '',
            'status'          => User::STATUS_PENDING,
        ]);

        $errors = $this->userModel->validateData($data);

        // No error should mention "password"
        $passwordErrors = array_filter(
            $errors,
            fn ($e) => stripos($e, 'password') !== false
        );
        $this->assertEmpty($passwordErrors, 'No password error expected for pending users. Got: ' . implode(', ', $errors));
    }

    public function test_validate_data_requires_password_for_active_user(): void
    {
        $data = array_merge($this->baseData, [
            'document_number' => '99999903',
            'email'           => 'active_no_pass@test.com',
            'password'        => '',
            'status'          => User::STATUS_ACTIVE,
        ]);

        $errors = $this->userModel->validateData($data);

        $this->assertNotEmpty($errors);
        // The "required fields" error should fire
        $this->assertStringContainsString('required', strtolower($errors[0]));
    }
}
