<?php

/**
 * Role Model
 *
 * Handles all database operations related to roles.
 *
 * @package ProyectoBase
 * @subpackage App\Models
 * @author jandrescodes
 * @version 1.0
 */

namespace App\Models;

use App\Core\Model;
use App\Services\DashboardCache;
use PDO;
use PDOException;

class Role extends Model
{
    /** @var string */
    protected $table = 'roles';

    /**
     * Returns all roles with the count of assigned users.
     *
     * @return array  Each row includes total_users
     */
    public function getAllWithUserCount(): array
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT r.*, COUNT(u.id) AS total_users
                 FROM {$this->table} r
                 LEFT JOIN users u ON u.role_id = r.id
                 GROUP BY r.id
                 ORDER BY r.name"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns a single role by ID.
     *
     * @param int $id
     * @return array|false
     */
    public function getById(int $id)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT * FROM {$this->table} WHERE id = :id"
            );
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Creates a new role.
     *
     * @param array $data  Keys: name, description
     * @return bool
     */
    public function create(array $data): bool
    {
        try {
            if ($this->nameExists($data['name'])) {
                $this->lastError = 'A role with this name already exists.';
                return false;
            }

            $description = !empty($data['description']) ? $data['description'] : null;
            $stmt = $this->connection->prepare(
                "INSERT INTO {$this->table} (name, description) VALUES (:name, :description)"
            );
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            if ($description === null) {
                $stmt->bindValue(':description', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            }

            if ($stmt->execute()) {
                DashboardCache::forget('role_stats');
                return true;
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getCode() == 23000
                ? 'A role with this name already exists.'
                : $e->getMessage();
            return false;
        }
    }

    /**
     * Updates an existing role.
     *
     * @param int   $id
     * @param array $data  Keys: name, description
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        try {
            if ($this->nameExists($data['name'], $id)) {
                $this->lastError = 'A role with this name already exists.';
                return false;
            }

            $description = !empty($data['description']) ? $data['description'] : null;
            $stmt = $this->connection->prepare(
                "UPDATE {$this->table} SET name = :name, description = :description WHERE id = :id"
            );
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            if ($description === null) {
                $stmt->bindValue(':description', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                DashboardCache::forget('role_stats');
                return true;
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getCode() == 23000
                ? 'A role with this name already exists.'
                : $e->getMessage();
            return false;
        }
    }

    /**
     * Updates the status of a role (logical delete).
     *
     * @param int $id
     * @param int $status  1 = active, 0 = inactive
     * @return bool
     */
    public function updateStatus(int $id, int $status): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE {$this->table} SET status = :status WHERE id = :id"
            );
            $stmt->bindParam(':status', $status, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                DashboardCache::forget('role_stats');
                return true;
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Checks whether a role with the given name already exists.
     *
     * @param string   $name
     * @param int|null $excludeId
     * @return bool
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        try {
            if ($excludeId) {
                $stmt = $this->connection->prepare(
                    "SELECT COUNT(*) FROM {$this->table} WHERE name = :name AND id != :id"
                );
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':id', $excludeId, PDO::PARAM_INT);
            } else {
                $stmt = $this->connection->prepare(
                    "SELECT COUNT(*) FROM {$this->table} WHERE name = :name"
                );
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Returns the number of users assigned to a role.
     *
     * @param int $roleId
     * @return int
     */
    public function countUsers(int $roleId): int
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT COUNT(*) FROM users WHERE role_id = :id"
            );
            $stmt->bindParam(':id', $roleId, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return 0;
        }
    }

    /**
     * Returns statistics: total, active, inactive counts (single query).
     *
     * @return array  Keys: total, active, inactive
     */
    public function getStatistics(): array
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS inactive
                FROM {$this->table}
            ");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'total'    => (int) $row['total'],
                'active'   => (int) $row['active'],
                'inactive' => (int) $row['inactive'],
            ];
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return ['total' => 0, 'active' => 0, 'inactive' => 0];
        }
    }


    /**
     * Returns the permission IDs currently assigned to a role.
     *
     * @param int $roleId
     * @return array  Flat array of permission_id integers
     */
    public function getAssignedPermissionIds(int $roleId): array
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT permission_id FROM role_permissions WHERE role_id = :role_id"
            );
            $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
            $stmt->execute();
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'permission_id');
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns the names of active permissions assigned to a role.
     * Used by Auth::resolvePermNames() to build the UNION cache.
     *
     * @param int $roleId
     * @return array  Flat array of permission name strings
     */
    public function getPermissionNames(int $roleId): array
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT p.name
                FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.id
                WHERE rp.role_id = :role_id AND p.status = 1
            ");
            $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
            $stmt->execute();
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Replaces all permissions for a role in a single transaction (DELETE + INSERT).
     * Invalidates the permission cache for every user of the role.
     *
     * @param int   $roleId
     * @param array $permissionIds
     * @return bool
     */
    public function syncPermissions(int $roleId, array $permissionIds): bool
    {
        try {
            $this->connection->beginTransaction();

            $stmt = $this->connection->prepare(
                "DELETE FROM role_permissions WHERE role_id = :role_id"
            );
            $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
            $stmt->execute();

            foreach ($permissionIds as $permId) {
                $permId = (int) $permId;
                if ($permId <= 0) {
                    continue;
                }
                $stmt = $this->connection->prepare(
                    "INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)"
                );
                $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
                $stmt->bindParam(':permission_id', $permId, PDO::PARAM_INT);
                $stmt->execute();
            }

            $this->connection->commit();
            DashboardCache::forget('role_stats');

            // Invalidate permission cache for every user of this role
            $userModel = new User();
            foreach ($this->getUserIdsByRole($roleId) as $uid) {
                $userModel->updatePermissionsTimestamp((int) $uid);
            }

            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Returns the IDs of all users assigned to a role.
     * Used to bulk-invalidate permission caches after syncPermissions().
     *
     * @param int $roleId
     * @return array  Flat array of user_id integers
     */
    public function getUserIdsByRole(int $roleId): array
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT id FROM users WHERE role_id = :role_id"
            );
            $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
            $stmt->execute();
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns id and name of all active roles (for user profile selects).
     *
     * @return array
     */
    public function getAllActive(): array
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT id, name FROM {$this->table} WHERE status = 1 ORDER BY name"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }
}
