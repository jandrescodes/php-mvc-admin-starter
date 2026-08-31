<?php

namespace App\Models\Traits;

use PDO;
use PDOException;

/**
 * UserPasswordTrait
 *
 * Groups password-related operations: current password verification,
 * password reset token lifecycle, and remember-me token management.
 *
 * @package ProyectoBase
 * @subpackage App\Models\Traits
 */
trait UserPasswordTrait
{
    // -------------------------------------------------------------------------
    // Password verification
    // -------------------------------------------------------------------------

    /**
     * Verifies whether the given plain-text password matches the stored hash.
     *
     * @param int    $id
     * @param string $currentPassword
     * @return bool
     */
    public function verifyCurrentPassword($id, $currentPassword)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT password FROM {$this->table} WHERE id = :id"
            );
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $hash = $stmt->fetchColumn();
            return password_verify($currentPassword, $hash);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Password reset
    // -------------------------------------------------------------------------

    /**
     * Resets the user's password by ID.
     *
     * Token lifecycle is managed by PasswordReset model — this method only
     * updates the password column.
     *
     * @param int    $id
     * @param string $newPassword Plain-text password (will be hashed)
     * @return bool
     */
    public function resetPassword($id, $newPassword)
    {
        try {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare(
                "UPDATE {$this->table}
                 SET password = :password
                 WHERE id = :id"
            );
            $stmt->bindParam(':password', $hash, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmt->execute();
            if ($result) {
                $this->clearRememberToken($id);
            }
            return $result;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Remember-me token
    // -------------------------------------------------------------------------

    /**
     * Stores a hashed remember-me token and its expiry for a user.
     *
     * @param int    $userId
     * @param string $tokenHash
     * @param string $expires   DateTime string (Y-m-d H:i:s)
     * @return bool
     */
    public function setRememberToken(int $userId, string $tokenHash, string $expires): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE {$this->table}
                 SET remember_token = :token, remember_token_expires = :expires
                 WHERE id = :id"
            );
            $stmt->bindParam(':token', $tokenHash, PDO::PARAM_STR);
            $stmt->bindParam(':expires', $expires, PDO::PARAM_STR);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Finds an active user by a valid (non-expired) remember-me token hash.
     *
     * @param string $tokenHash
     * @return array|false
     */
    public function findByRememberToken(string $tokenHash): array|false
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT u.*, r.name AS role_name, r.is_system AS role_is_system
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.remember_token = :token
                  AND u.remember_token_expires > NOW()
                  AND u.status = 1
                LIMIT 1
            ");
            $stmt->bindParam(':token', $tokenHash, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Clears the remember-me token for a user (on logout).
     *
     * @param int $userId
     * @return bool
     */
    public function clearRememberToken(int $userId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE {$this->table}
                 SET remember_token = NULL, remember_token_expires = NULL
                 WHERE id = :id"
            );
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }
}
