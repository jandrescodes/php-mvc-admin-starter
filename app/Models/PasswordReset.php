<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

/**
 * PasswordReset
 *
 * Manages the password_resets table for both "reset" and "invitation" flows.
 *
 * Tokens are stored as SHA-256 hashes. The raw token travels only in the
 * email link and is never persisted or logged in plain text.
 *
 * @package ProyectoBase
 * @subpackage App\Models
 */
class PasswordReset extends Model
{
    protected $table = 'password_resets';

    /** TTL for password-reset tokens (strtotime-compatible string) */
    public const TTL_RESET = '+1 hour';

    /** TTL for invitation tokens (strtotime-compatible string) */
    public const TTL_INVITATION = '+48 hours';

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Invalidates any live tokens of the same type for the user, then creates
     * a new token row and returns the raw (plain-text) token.
     *
     * The raw token is returned ONCE and must be embedded in the email link
     * immediately. It is never stored and cannot be recovered afterwards.
     *
     * @param  int    $userId
     * @param  string $type  'reset' | 'invitation'
     * @return string        Raw token (64 hex chars)
     */
    public function create(int $userId, string $type): string
    {
        $this->invalidateExisting($userId, $type);

        $token     = bin2hex(random_bytes(32));
        $tokenHash = $this->hashToken($token);
        $ttl       = $type === 'invitation' ? self::TTL_INVITATION : self::TTL_RESET;
        $expiresAt = date('Y-m-d H:i:s', strtotime($ttl));

        $this->insert([
            'user_id'    => $userId,
            'token_hash' => $tokenHash,
            'type'       => $type,
            'expires_at' => $expiresAt,
        ]);

        return $token;
    }

    /**
     * Looks up a valid (non-expired, non-used) token row matching the given
     * raw token and type.
     *
     * @param  string $token  Raw token from the URL
     * @param  string $type   'reset' | 'invitation'
     * @return array|false    Full DB row or false when invalid / not found
     */
    public function findValidByToken(string $token, string $type): array|false
    {
        try {
            $tokenHash = $this->hashToken($token);

            $stmt = $this->connection->prepare(
                "SELECT * FROM {$this->table}
                 WHERE token_hash = :hash
                   AND type       = :type
                   AND used_at    IS NULL
                   AND expires_at > NOW()
                 LIMIT 1"
            );
            $stmt->bindParam(':hash', $tokenHash, PDO::PARAM_STR);
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Marks a token row as used by setting used_at to the current timestamp.
     *
     * @param  int  $id  Primary key of the password_resets row
     * @return bool
     */
    public function markUsed(int $id): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE {$this->table}
                 SET used_at = NOW()
                 WHERE id = :id"
            );
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Returns the count of invitation tokens that are active (not used, not expired).
     *
     * @return int
     */
    public function getPendingInvitationsCount(): int
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT COUNT(*) FROM {$this->table}
                 WHERE type       = 'invitation'
                   AND used_at    IS NULL
                   AND expires_at > NOW()"
            );
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return 0;
        }
    }

    /**
     * Returns the count of password-reset tokens created in the last 7 days.
     *
     * @return int
     */
    public function getResetRequestsThisWeek(): int
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT COUNT(*) FROM {$this->table}
                 WHERE type       = 'reset'
                   AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return 0;
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Returns the SHA-256 hex digest of a raw token.
     *
     * @param  string $token
     * @return string  64-character hex string
     */
    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Marks all live (unused) tokens of the given type for a user as used,
     * effectively invalidating them before a new token is issued.
     *
     * @param  int    $userId
     * @param  string $type
     * @return void
     */
    private function invalidateExisting(int $userId, string $type): void
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE {$this->table}
                 SET used_at = NOW()
                 WHERE user_id = :user_id
                   AND type    = :type
                   AND used_at IS NULL"
            );
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            // Non-fatal: log and continue; the new token will still be created.
            $this->lastError = $e->getMessage();
        }
    }
}
