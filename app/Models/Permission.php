<?php

/**
 * Permission Model
 *
 * Handles all database operations related to permissions.
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

class Permission extends Model
{
    /**
     * Permissions table name
     * @var string
     */
    protected $table = 'permissions';

    /**
     * Returns all permissions.
     *
     * @param bool $onlyActive  When true, returns only active permissions
     * @return array
     */
    public function getAll($onlyActive = false)
    {
        try {
            $query = "SELECT * FROM {$this->table}";
            if ($onlyActive) {
                $query .= " WHERE status = 1";
            }
            $query .= " ORDER BY name";

            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns all permissions with the count of assigned users (single query).
     *
     * @param bool $onlyActive
     * @return array  Each row includes total_users
     */
    public function getAllWithUserCount($onlyActive = false)
    {
        try {
            $where = $onlyActive ? "WHERE p.status = 1" : "";
            $query = "SELECT p.*, COUNT(up.user_id) AS total_users
                      FROM {$this->table} p
                      LEFT JOIN user_permissions up ON p.id = up.permission_id
                      {$where}
                      GROUP BY p.id
                      ORDER BY p.name";

            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns a single permission by ID.
     *
     * @param int $id
     * @return array|false
     */
    public function getById($id)
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
     * Creates a new permission.
     *
     * @param array $data  Keys: name, description
     * @return bool
     */
    public function create(array $data): bool
    {
        try {
            if ($this->nameExists($data['name'])) {
                $this->lastError = 'A permission with this name already exists.';
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
                DashboardCache::forget('perm_stats');
                DashboardCache::forget('top_permissions');
                return true;
            }
            return false;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $this->lastError = 'A permission with this name already exists.';
            } else {
                $this->lastError = $e->getMessage();
            }
            return false;
        }
    }

    /**
     * Updates an existing permission.
     *
     * @param int   $id
     * @param array $data  Keys: name, description
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        try {
            if ($this->nameExists($data['name'], $id)) {
                $this->lastError = 'A permission with this name already exists.';
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
                DashboardCache::forget('perm_stats');
                DashboardCache::forget('top_permissions');
                return true;
            }
            return false;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $this->lastError = 'A permission with this name already exists.';
            } else {
                $this->lastError = $e->getMessage();
            }
            return false;
        }
    }

    /**
     * Updates the status of a permission.
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
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                DashboardCache::forget('perm_stats');
                DashboardCache::forget('top_permissions');
                return true;
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Checks whether a permission with the given name already exists.
     *
     * @param string   $name
     * @param int|null $excludeId
     * @return bool
     */
    public function nameExists($name, $excludeId = null)
    {
        try {
            if ($excludeId) {
                $query = "SELECT COUNT(*) FROM {$this->table} WHERE name = :name AND id != :id";
                $stmt  = $this->connection->prepare($query);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':id', $excludeId, PDO::PARAM_INT);
            } else {
                $query = "SELECT COUNT(*) FROM {$this->table} WHERE name = :name";
                $stmt  = $this->connection->prepare($query);
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
     * Returns the number of users assigned to a permission.
     *
     * @param int $permissionId
     * @return int
     */
    public function countUsers($permissionId)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT COUNT(*) FROM user_permissions WHERE permission_id = :id"
            );
            $stmt->bindParam(':id', $permissionId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return 0;
        }
    }

    /**
     * Returns the users assigned to a specific permission.
     *
     * @param int $permissionId
     * @return array
     */
    public function getUsersByPermission($permissionId)
    {
        try {
            $query = "SELECT u.id, u.name, u.first_surname, u.second_surname, u.email, u.status,
                             r.name AS role_name
                      FROM users u
                      LEFT JOIN roles r ON u.role_id = r.id
                      INNER JOIN user_permissions up ON u.id = up.user_id
                      WHERE up.permission_id = :permission_id
                      ORDER BY u.name, u.first_surname";

            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':permission_id', $permissionId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns active users who do NOT have a specific permission assigned.
     *
     * @param int $permissionId
     * @return array
     */
    public function getUsersWithoutPermission($permissionId)
    {
        try {
            $query = "SELECT u.id, u.name, u.first_surname, u.second_surname
                      FROM users u
                      WHERE u.status = 1
                        AND u.id NOT IN (
                            SELECT user_id FROM user_permissions WHERE permission_id = :permission_id
                        )
                      ORDER BY u.name, u.first_surname";

            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':permission_id', $permissionId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns users without this permission formatted for Select2 (id + text).
     *
     * @param int $permissionId
     * @return array  [['id' => int, 'text' => string], ...]
     */
    public function getUsersWithoutFormatted(int $permissionId): array
    {
        $result = [];
        foreach ($this->getUsersWithoutPermission($permissionId) as $u) {
            $parts = [$u['name'], $u['first_surname']];
            if (!empty($u['second_surname'])) {
                $parts[] = $u['second_surname'];
            }
            $name     = htmlspecialchars(trim(implode(' ', $parts)), ENT_QUOTES, 'UTF-8');
            $result[] = ['id' => (int) $u['id'], 'text' => $name];
        }
        return $result;
    }

    /**
     * Returns statistics: total, active, inactive counts (single query).
     *
     * @return array  Keys: total, active, inactive, most_used
     */
    public function getStatistics()
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
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt2 = $this->connection->prepare("
                SELECT p.id, p.name, COUNT(up.user_id) AS total_users
                FROM permissions p
                LEFT JOIN user_permissions up ON p.id = up.permission_id
                GROUP BY p.id
                ORDER BY total_users DESC
                LIMIT 5
            ");
            $stmt2->execute();
            $mostUsed = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            return [
                'total'     => (int) $counts['total'],
                'active'    => (int) $counts['active'],
                'inactive'  => (int) $counts['inactive'],
                'most_used' => $mostUsed,
            ];
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return ['total' => 0, 'active' => 0, 'inactive' => 0, 'most_used' => []];
        }
    }

    /**
     * Returns the top N active permissions by number of assigned users for the bar chart.
     *
     * @param int $limit
     * @return array  Each element: ['id' => int, 'name' => string, 'total_users' => int]
     */
    public function getTopAssigned(int $limit = 5): array
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT p.id, p.name, COUNT(up.user_id) AS total_users
                FROM {$this->table} p
                LEFT JOIN user_permissions up ON p.id = up.permission_id
                WHERE p.status = 1
                GROUP BY p.id, p.name
                ORDER BY total_users DESC, p.name ASC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return array_map(static function (array $row): array {
                return [
                    'id'          => (int) $row['id'],
                    'name'        => $row['name'],
                    'total_users' => (int) $row['total_users'],
                ];
            }, $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }


    /**
     * Returns id and name of all active permissions.
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

    /**
     * Returns the permission IDs assigned to a user.
     *
     * @param int $userId
     * @return array
     */
    public function getAssignedIds(int $userId): array
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT permission_id FROM user_permissions WHERE user_id = :user_id"
            );
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'permission_id');
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns id and name of active permissions assigned to a user.
     *
     * @param int $userId
     * @return array
     */
    public function getByUserId(int $userId): array
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT p.id, p.name
                FROM user_permissions up
                JOIN {$this->table} p ON up.permission_id = p.id
                WHERE up.user_id = :user_id AND p.status = 1
            ");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Replaces all permissions for a user in a single transaction.
     * Returns the resulting permission names (used to refresh the session cache).
     *
     * @param int   $userId
     * @param array $ids  Permission IDs to assign
     * @return array  Permission names after sync
     */
    public function syncForUser(int $userId, array $ids): array
    {
        try {
            $this->connection->beginTransaction();

            $stmt = $this->connection->prepare(
                "DELETE FROM user_permissions WHERE user_id = :user_id"
            );
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            foreach ($ids as $permId) {
                $permId = (int) $permId;
                if ($permId <= 0) {
                    continue;
                }
                $stmt = $this->connection->prepare(
                    "INSERT INTO user_permissions (user_id, permission_id) VALUES (:user_id, :permission_id)"
                );
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':permission_id', $permId, PDO::PARAM_INT);
                $stmt->execute();
            }

            $this->connection->commit();
            return array_column($this->getByUserId($userId), 'name');
        } catch (PDOException $e) {
            $this->connection->rollBack();
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Assigns a permission to a user (idempotent).
     */
    public function assign(int $userId, int $permissionId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT COUNT(*) FROM user_permissions WHERE user_id = :uid AND permission_id = :pid"
            );
            $stmt->bindParam(':uid', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':pid', $permissionId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                return true;
            }

            $stmt = $this->connection->prepare(
                "INSERT INTO user_permissions (user_id, permission_id) VALUES (:uid, :pid)"
            );
            $stmt->bindParam(':uid', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':pid', $permissionId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Revokes a permission from a user (idempotent).
     */
    public function revoke(int $userId, int $permissionId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "DELETE FROM user_permissions WHERE user_id = :uid AND permission_id = :pid"
            );
            $stmt->bindParam(':uid', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':pid', $permissionId, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }
}
