<?php

/**
 * User Model
 *
 * Handles all database operations related to users.
 * Auth, password, and stats concerns are split into focused traits:
 *  - UserAuthTrait     — login, lookups, uniqueness checks, throttle
 *  - UserPasswordTrait — password reset token, remember-me token, password verification
 *  - UserStatsTrait    — dashboard aggregation queries
 *
 * @package ProyectoBase
 * @subpackage App\Models
 * @author jandrescodes
 * @version 1.0
 */

namespace App\Models;

use App\Core\Model;
use App\Models\Traits\UserAuthTrait;
use App\Models\Traits\UserPasswordTrait;
use App\Models\Traits\UserStatsTrait;
use App\Services\DashboardCache;
use PDO;
use PDOException;

class User extends Model
{
    use UserAuthTrait;
    use UserPasswordTrait;
    use UserStatsTrait;

    /** @var string */
    protected $table = 'users';

    // -------------------------------------------------------------------------
    // Status constants
    // -------------------------------------------------------------------------

    /** Account is deactivated by an administrator */
    const STATUS_INACTIVE = 0;

    /** Account is active and can log in */
    const STATUS_ACTIVE = 1;

    /**
     * Account is pending — invitation sent but password not yet set.
     * Pending users cannot log in, request a password reset, or be
     * auto-logged in via remember-me cookie.
     */
    const STATUS_PENDING = 2;

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    /**
     * Returns all users with their role name, ordered by ID descending.
     *
     * @return array
     */
    public function getAll()
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT u.*, r.name AS role_name
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                ORDER BY u.id DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns a single user by ID, including role metadata.
     *
     * @param int $id
     * @return array|false
     */
    public function getById($id)
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT u.*, r.name AS role_name, r.is_system AS role_is_system
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.id = :id
            ");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Write
    // -------------------------------------------------------------------------

    /**
     * Creates a new user.
     *
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool
    {
        try {
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

            $stmt = $this->connection->prepare("
                INSERT INTO {$this->table}
                    (name, first_surname, second_surname, document_type, document_number,
                     address, phone, email, password, image, status, role_id)
                VALUES
                    (:name, :first_surname, :second_surname, :document_type, :document_number,
                     :address, :phone, :email, :password, :image, :status, :role_id)
            ");

            $stmt->bindParam(':name',          $data['name'],          PDO::PARAM_STR);
            $stmt->bindParam(':first_surname', $data['first_surname'], PDO::PARAM_STR);

            empty($data['second_surname'])
                ? $stmt->bindValue(':second_surname', null, PDO::PARAM_NULL)
                : $stmt->bindParam(':second_surname', $data['second_surname'], PDO::PARAM_STR);

            $stmt->bindParam(':document_type',   $data['document_type'],   PDO::PARAM_STR);
            $stmt->bindParam(':document_number', $data['document_number'], PDO::PARAM_STR);

            empty($data['address'])
                ? $stmt->bindValue(':address', null, PDO::PARAM_NULL)
                : $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);

            empty($data['phone'])
                ? $stmt->bindValue(':phone', null, PDO::PARAM_NULL)
                : $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);

            empty($data['email'])
                ? $stmt->bindValue(':email', null, PDO::PARAM_NULL)
                : $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);

            empty($data['image'])
                ? $stmt->bindValue(':image', null, PDO::PARAM_NULL)
                : $stmt->bindParam(':image', $data['image'], PDO::PARAM_STR);

            $stmt->bindParam(':password', $passwordHash,   PDO::PARAM_STR);
            $stmt->bindParam(':status',   $data['status'], PDO::PARAM_INT);

            empty($data['role_id'])
                ? $stmt->bindValue(':role_id', null, PDO::PARAM_NULL)
                : $stmt->bindValue(':role_id', (int) $data['role_id'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                $this->forgetUserCaches();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Updates an existing user (admin edit, no password change here).
     *
     * @param int   $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->connection->prepare("
                UPDATE {$this->table} SET
                    name            = :name,
                    first_surname   = :first_surname,
                    second_surname  = :second_surname,
                    document_type   = :document_type,
                    document_number = :document_number,
                    address         = :address,
                    phone           = :phone,
                    email           = :email,
                    image           = :image,
                    status          = :status,
                    role_id         = :role_id
                WHERE id = :id
            ");

            $stmt->bindParam(':name',          $data['name'],          PDO::PARAM_STR);
            $stmt->bindParam(':first_surname', $data['first_surname'], PDO::PARAM_STR);

            empty($data['second_surname'])
                ? $stmt->bindValue(':second_surname', null, PDO::PARAM_NULL)
                : $stmt->bindParam(':second_surname', $data['second_surname'], PDO::PARAM_STR);

            $stmt->bindParam(':document_type',   $data['document_type'],   PDO::PARAM_STR);
            $stmt->bindParam(':document_number', $data['document_number'], PDO::PARAM_STR);

            empty($data['address'])
                ? $stmt->bindValue(':address', null, PDO::PARAM_NULL)
                : $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);

            empty($data['phone'])
                ? $stmt->bindValue(':phone', null, PDO::PARAM_NULL)
                : $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);

            empty($data['email'])
                ? $stmt->bindValue(':email', null, PDO::PARAM_NULL)
                : $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);

            empty($data['image'])
                ? $stmt->bindValue(':image', null, PDO::PARAM_NULL)
                : $stmt->bindParam(':image', $data['image'], PDO::PARAM_STR);

            $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);

            empty($data['role_id'])
                ? $stmt->bindValue(':role_id', null, PDO::PARAM_NULL)
                : $stmt->bindValue(':role_id', (int) $data['role_id'], PDO::PARAM_INT);

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $this->forgetUserCaches();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Updates only the fields a user can edit from their own profile.
     *
     * @param int   $id
     * @param array $data  Keys: phone, address, image
     * @return bool
     */
    public function updateProfile($id, $data)
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE {$this->table} SET phone = :phone, address = :address, image = :image WHERE id = :id"
            );

            empty($data['phone'])
                ? $stmt->bindValue(':phone', null, PDO::PARAM_NULL)
                : $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);

            empty($data['address'])
                ? $stmt->bindValue(':address', null, PDO::PARAM_NULL)
                : $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);

            empty($data['image'])
                ? $stmt->bindValue(':image', null, PDO::PARAM_NULL)
                : $stmt->bindParam(':image', $data['image'], PDO::PARAM_STR);

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Updates a user's password.
     *
     * @param int    $id
     * @param string $password  Plain-text password (will be hashed)
     * @return bool
     */
    public function updatePassword($id, $password)
    {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare(
                "UPDATE {$this->table} SET password = :password WHERE id = :id"
            );
            $stmt->bindParam(':password', $hash, PDO::PARAM_STR);
            $stmt->bindParam(':id',       $id,   PDO::PARAM_INT);
            if ($stmt->execute()) {
                $this->clearRememberToken($id);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Updates a user's status.
     *
     * @param int $id
     * @param int $status  1 = active, 0 = inactive
     * @return bool
     */
    public function updateStatus($id, $status)
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE {$this->table} SET status = :status WHERE id = :id"
            );
            $stmt->bindParam(':status', $status, PDO::PARAM_INT);
            $stmt->bindParam(':id',     $id,     PDO::PARAM_INT);

            if ($stmt->execute()) {
                $this->forgetUserCaches();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Activates a user.
     *
     * @param int $id
     * @return bool
     */
    public function activate($id)
    {
        return $this->updateStatus($id, 1);
    }

    /**
     * Deactivates a user.
     *
     * @param int $id
     * @return bool
     */
    public function deactivate($id)
    {
        return $this->updateStatus($id, 0);
    }

    /**
     * Updates only the image of a user.
     *
     * @param int         $id
     * @param string|null $image  Filename or null to clear
     * @return bool
     */
    public function updateImage($id, $image)
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE {$this->table} SET image = :image WHERE id = :id"
            );
            $image === null
                ? $stmt->bindValue(':image', null, PDO::PARAM_NULL)
                : $stmt->bindParam(':image', $image, PDO::PARAM_STR);

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Permission cache invalidation
    // -------------------------------------------------------------------------

    /**
     * Updates the permissions timestamp for a user.
     * Call after any permission change to invalidate the session cache.
     *
     * @param int $userId
     * @return bool
     */
    public function updatePermissionsTimestamp($userId)
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE {$this->table} SET permissions_updated_at = NOW() WHERE id = :id"
            );
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Returns the permissions_updated_at timestamp for a user.
     *
     * @param int $userId
     * @return string|null  Timestamp string or null
     */
    public function getPermissionsTimestamp($userId)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT permissions_updated_at FROM {$this->table} WHERE id = :id"
            );
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn() ?: null;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    /**
     * Validates user data before create or update.
     *
     * @param array    $data
     * @param int|null $excludeId
     * @return array  List of error messages (empty = valid)
     */
    public function validateData($data, $excludeId = null)
    {
        $errors   = [];
        $isUpdate = ($excludeId !== null);

        if (!$isUpdate) {
            $isPending = isset($data['status']) && (int) $data['status'] === self::STATUS_PENDING;
            if (
                empty($data['name'])            ||
                empty($data['first_surname'])   ||
                empty($data['document_type'])   ||
                empty($data['document_number']) ||
                empty($data['email'])           ||
                (!$isPending && empty($data['password']))
            ) {
                $errors[] = 'All required fields must be filled in.';
            }
            if (empty($data['role_id'])) {
                $errors[] = 'A role must be assigned to the user.';
            }
        } else {
            $required = ['name', 'first_surname', 'document_type', 'document_number', 'email'];
            $missing  = [];
            foreach ($required as $field) {
                if (isset($data[$field]) && empty($data[$field])) {
                    $missing[] = $field;
                }
            }
            if (!empty($missing)) {
                $errors[] = 'The following required fields cannot be empty: ' . implode(', ', $missing);
            }
            if (isset($data['role_id']) && empty($data['role_id'])) {
                $errors[] = 'A role must be assigned to the user.';
            }
        }

        if (!empty($data['email'])) {
            if ($this->emailExists($data['email'], $excludeId)) {
                $errors[] = 'This email address is already registered.';
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'The email address format is invalid.';
            }
        }

        if (!empty($data['document_type']) && !empty($data['document_number'])) {
            if ($this->documentTypeExists($data['document_type'], $data['document_number'], $excludeId)) {
                $errors[] = 'A user with this document type and number already exists.';
            }
        }

        $isPendingCreate = !$isUpdate && isset($data['status']) && (int) $data['status'] === self::STATUS_PENDING;
        if (!$isPendingCreate && ((!$isUpdate && isset($data['password'])) || !empty($data['password']))) {
            if (strlen($data['password']) < 8) {
                $errors[] = 'Password must be at least 8 characters long.';
            }
        }

        return $errors;
    }

    /**
     * Validates profile-editable fields (phone, address).
     *
     * @param array $data
     * @return array
     */
    public function validateProfileData($data)
    {
        $errors = [];

        if (!empty($data['phone']) && !preg_match('/^\+?[0-9]{7,15}$/', $data['phone'])) {
            $errors[] = 'Phone must contain between 7 and 15 numeric digits.';
        }

        if (!empty($data['address']) && strlen($data['address']) > 255) {
            $errors[] = 'Address cannot exceed 255 characters.';
        }

        return $errors;
    }

    /**
     * Validates a self-service password change (requires current password verification).
     *
     * @param int    $id       User ID
     * @param string $current  Current plain-text password
     * @param string $new      New plain-text password
     * @param string $confirm  Confirmation of new password
     * @return array           Empty on success, error messages on failure
     */
    public function validatePasswordChange(int $id, string $current, string $new, string $confirm): array
    {
        $errors = [];

        if (empty($current) || empty($new) || empty($confirm)) {
            $errors[] = 'All password fields are required.';
            return $errors;
        }

        if (!$this->verifyCurrentPassword($id, $current)) {
            $errors[] = 'The current password is incorrect.';
        }

        if ($new !== $confirm) {
            $errors[] = 'New passwords do not match.';
        }

        if (strlen($new) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }

        return $errors;
    }

    /**
     * Validates a new password pair (admin reset or token-based reset — no current password required).
     *
     * @param string $password  New plain-text password
     * @param string $confirm   Confirmation
     * @return array            Empty on success, error messages on failure
     */
    public function validateNewPassword(string $password, string $confirm): array
    {
        $errors = [];

        if (empty($password) || empty($confirm)) {
            $errors[] = 'Both password fields are required to change the password.';
            return $errors;
        }

        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }

        return $errors;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Invalidates all dashboard cache keys that depend on user data.
     * Called after any write that changes the user table.
     */
    private function forgetUserCaches(): void
    {
        DashboardCache::forget('user_stats');
        DashboardCache::forget('users_by_status');
        DashboardCache::forget('recent_users');
        DashboardCache::forget('users_by_month');
    }
}
