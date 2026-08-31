<?php

namespace App\Models\Traits;

use PDO;
use PDOException;

/**
 * UserAuthTrait
 *
 * Groups authentication-related queries: login by credential, lookups
 * without password verification, uniqueness checks, and login throttling.
 *
 * @package ProyectoBase
 * @subpackage App\Models\Traits
 */
trait UserAuthTrait
{
    // -------------------------------------------------------------------------
    // Login
    // -------------------------------------------------------------------------

    /**
     * Verifies login credentials by email.
     *
     * @param string $email
     * @param string $password
     * @return array|false  User row on success, false otherwise
     */
    public function loginByEmail($email, $password)
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT u.*, r.name AS role_name, r.is_system AS role_is_system
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.email = :email
            ");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Verifies login credentials by document number.
     *
     * @param string $documentNumber
     * @param string $password
     * @return array|false
     */
    public function loginByDocumentNumber($documentNumber, $password)
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT u.*, r.name AS role_name, r.is_system AS role_is_system
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.document_number = :document_number
            ");
            $stmt->bindParam(':document_number', $documentNumber, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Lookup (no password check)
    // -------------------------------------------------------------------------

    /**
     * Returns a user row by email without verifying the password.
     * Used by the login flow to resolve the user before throttle checks.
     *
     * @param  string $email
     * @return array|false
     */
    public function findByEmail(string $email): array|false
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT u.*, r.name AS role_name, r.is_system AS role_is_system
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.email = :email
                LIMIT 1
            ");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Returns a user row by document number without verifying the password.
     *
     * @param  string $documentNumber
     * @return array|false
     */
    public function findByDocumentNumber(string $documentNumber): array|false
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT u.*, r.name AS role_name, r.is_system AS role_is_system
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.document_number = :document_number
                LIMIT 1
            ");
            $stmt->bindParam(':document_number', $documentNumber, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Uniqueness / existence checks
    // -------------------------------------------------------------------------

    /**
     * Checks whether an email address is already registered.
     *
     * @param string   $email
     * @param int|null $excludeId  Exclude this user ID from the check
     * @return bool
     */
    public function emailExists($email, $excludeId = null)
    {
        try {
            if ($excludeId) {
                $stmt = $this->connection->prepare(
                    "SELECT COUNT(*) FROM {$this->table} WHERE email = :email AND id != :id"
                );
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':id', $excludeId, PDO::PARAM_INT);
            } else {
                $stmt = $this->connection->prepare(
                    "SELECT COUNT(*) FROM {$this->table} WHERE email = :email"
                );
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Returns the status of a user identified by email.
     *
     * @param string $email
     * @return int|null
     */
    public function getStatusByEmail($email)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT status FROM {$this->table} WHERE email = :email"
            );
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
    }

    /**
     * Returns the status of a user by ID.
     *
     * @param int $id
     * @return int|null
     */
    public function getStatusById($id)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT status FROM {$this->table} WHERE id = :id"
            );
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
    }

    /**
     * Returns a user ID by email address.
     *
     * @param string $email
     * @return int|null
     */
    public function getIdByEmail($email)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT id FROM {$this->table} WHERE email = :email"
            );
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
    }

    /**
     * Returns a user ID by document number.
     *
     * @param string $documentNumber
     * @return int|null
     */
    public function getIdByDocumentNumber($documentNumber)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT id FROM {$this->table} WHERE document_number = :document_number"
            );
            $stmt->bindParam(':document_number', $documentNumber, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
    }

    /**
     * Checks whether a document number is already registered.
     *
     * @param string $documentNumber
     * @return bool
     */
    public function documentNumberExists($documentNumber)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT COUNT(*) FROM {$this->table} WHERE document_number = :document_number"
            );
            $stmt->bindParam(':document_number', $documentNumber, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Checks whether a document type + number combination is already registered.
     *
     * @param string   $documentType
     * @param string   $documentNumber
     * @param int|null $excludeId
     * @return bool
     */
    public function documentTypeExists($documentType, $documentNumber, $excludeId = null)
    {
        try {
            if ($excludeId) {
                $stmt = $this->connection->prepare(
                    "SELECT COUNT(*) FROM {$this->table}
                     WHERE document_type = :document_type
                       AND document_number = :document_number
                       AND id != :id"
                );
                $stmt->bindParam(':document_type', $documentType, PDO::PARAM_STR);
                $stmt->bindParam(':document_number', $documentNumber, PDO::PARAM_STR);
                $stmt->bindParam(':id', $excludeId, PDO::PARAM_INT);
            } else {
                $stmt = $this->connection->prepare(
                    "SELECT COUNT(*) FROM {$this->table}
                     WHERE document_type = :document_type AND document_number = :document_number"
                );
                $stmt->bindParam(':document_type', $documentType, PDO::PARAM_STR);
                $stmt->bindParam(':document_number', $documentNumber, PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Login throttle
    // -------------------------------------------------------------------------

    /**
     * Increments the failed login counter for a user and sets locked_until
     * when the max-attempts threshold is reached.
     *
     * @param  int   $userId
     * @return array Updated row with keys: login_attempts, locked_until, last_attempt_at
     */
    public function recordFailure(int $userId): array
    {
        try {
            $maxAttempts    = (int) env('LOGIN_MAX_ATTEMPTS', 5);
            $lockoutMinutes = (int) env('LOGIN_LOCKOUT_MINUTES', 15);

            $stmt = $this->connection->prepare("
                UPDATE {$this->table}
                SET
                    login_attempts  = login_attempts + 1,
                    last_attempt_at = NOW(),
                    locked_until    = IF(
                        login_attempts >= :max,
                        DATE_ADD(NOW(), INTERVAL :minutes MINUTE),
                        locked_until
                    )
                WHERE id = :id
            ");
            $stmt->bindValue(':max', $maxAttempts, PDO::PARAM_INT);
            $stmt->bindValue(':minutes', $lockoutMinutes, PDO::PARAM_INT);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $stmt2 = $this->connection->prepare("
                SELECT login_attempts, locked_until, last_attempt_at
                FROM {$this->table} WHERE id = :id
            ");
            $stmt2->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt2->execute();
            return $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Resets all throttle columns after a successful login.
     *
     * @param  int  $userId
     * @return void
     */
    public function clearAttempts(int $userId): void
    {
        try {
            $stmt = $this->connection->prepare("
                UPDATE {$this->table}
                SET login_attempts = 0, locked_until = NULL, last_attempt_at = NULL
                WHERE id = :id
            ");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
        }
    }

    /**
     * Manually unlocks a user (admin action). Returns bool so the controller can confirm success.
     *
     * @param  int  $userId
     * @return bool
     */
    public function unlock(int $userId): bool
    {
        try {
            $stmt = $this->connection->prepare("
                UPDATE {$this->table}
                SET login_attempts = 0, locked_until = NULL, last_attempt_at = NULL
                WHERE id = :id
            ");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Evaluates whether a user is currently locked out.
     * Does NOT write to the DB — the Service layer decides whether to reset.
     *
     * @param  int   $userId
     * @return array{locked: bool, remaining_seconds: int}
     */
    public function getLockStatus(int $userId): array
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT
                    locked_until,
                    GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), locked_until)) AS remaining_seconds
                FROM {$this->table}
                WHERE id = :id
            ");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || $row['locked_until'] === null) {
                return ['locked' => false, 'remaining_seconds' => 0];
            }

            $remaining = (int) $row['remaining_seconds'];
            return [
                'locked'            => $remaining > 0,
                'remaining_seconds' => $remaining,
            ];
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return ['locked' => false, 'remaining_seconds' => 0];
        }
    }
}
