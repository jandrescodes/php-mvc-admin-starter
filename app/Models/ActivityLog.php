<?php

/**
 * ActivityLog Model
 *
 * Handles read/write operations for the activity_logs table (append-only).
 * Never issues UPDATE or DELETE from the application layer.
 *
 * @package ProyectoBase
 * @subpackage App\Models
 * @author jandrescodes
 * @version 1.0
 */

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class ActivityLog extends Model
{
    /** @var string */
    private string $tabla = 'activity_logs';

    // -------------------------------------------------------------------------
    // Write
    // -------------------------------------------------------------------------

    /**
     * Inserts a new activity log entry.
     *
     * @param array $data  Keys: actor_id (nullable), actor_label (nullable),
     *                     module, action, description (nullable), details (nullable array),
     *                     ip_address (nullable), user_agent (nullable)
     * @return int|false   Inserted row ID or false on failure
     */
    public function create(array $data): int|false
    {
        try {
            $stmt = $this->connection->prepare(
                "INSERT INTO {$this->tabla}
                    (actor_id, actor_label, module, action, description, details, ip_address, user_agent)
                 VALUES
                    (:actor_id, :actor_label, :module, :action, :description, :details, :ip_address, :user_agent)"
            );

            $stmt->execute([
                ':actor_id'    => $data['actor_id']   ?? null,
                ':actor_label' => $data['actor_label'] ?? null,
                ':module'      => $data['module'],
                ':action'      => $data['action'],
                ':description' => $data['description'] ?? null,
                ':details'     => isset($data['details']) ? json_encode($data['details']) : null,
                ':ip_address'  => $data['ip_address']  ?? null,
                ':user_agent'  => $data['user_agent']  ?? null,
            ]);

            return (int) $this->connection->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    /**
     * Returns all activity log entries with optional filters, ordered by most recent first.
     *
     * @param array $filters  Optional keys: module, action, actor_id, date_from, date_to
     * @return array
     */
    public function getAll(array $filters = []): array
    {
        try {
            $where  = [];
            $params = [];

            if (!empty($filters['module'])) {
                $where[]              = 'al.module = :module';
                $params[':module']    = $filters['module'];
            }

            if (!empty($filters['action'])) {
                $where[]              = 'al.action = :action';
                $params[':action']    = $filters['action'];
            }

            if (!empty($filters['actor_id'])) {
                $where[]              = 'al.actor_id = :actor_id';
                $params[':actor_id']  = (int) $filters['actor_id'];
            }

            if (!empty($filters['date_from'])) {
                $where[]                = 'al.created_at >= :date_from';
                $params[':date_from']   = $filters['date_from'] . ' 00:00:00';
            }

            if (!empty($filters['date_to'])) {
                $where[]              = 'al.created_at <= :date_to';
                $params[':date_to']   = $filters['date_to'] . ' 23:59:59';
            }

            $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

            $stmt = $this->connection->prepare(
                "SELECT al.*,
                        u.name          AS user_name,
                        u.first_surname AS user_first_surname,
                        u.email         AS user_email
                 FROM {$this->tabla} al
                 LEFT JOIN users u ON u.id = al.actor_id
                 {$whereClause}
                 ORDER BY al.created_at DESC, al.id DESC"
            );

            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns distinct module values present in the log (for filter dropdowns).
     *
     * @return array  List of module strings
     */
    public function getDistinctModules(): array
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT DISTINCT module FROM {$this->tabla} ORDER BY module"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns distinct action values present in the log (for filter dropdowns).
     *
     * @return array  List of action strings
     */
    public function getDistinctActions(): array
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT DISTINCT action FROM {$this->tabla} ORDER BY action"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns all actors (users) who have at least one log entry.
     *
     * @return array  Each row: actor_id, actor_label
     */
    public function getActorsWithLogs(): array
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT DISTINCT actor_id, actor_label
                 FROM {$this->tabla}
                 WHERE actor_id IS NOT NULL
                 ORDER BY actor_label"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns the count of log entries created today (UTC).
     *
     * @return int
     */
    public function countToday(): int
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT COUNT(*) FROM {$this->tabla}
                 WHERE DATE(created_at) = CURDATE()"
            );
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return 0;
        }
    }

    /**
     * Deletes entries older than the given number of days.
     * This is the only DELETE allowed in the model and is intended for
     * scheduled retention purges, not UI-driven actions.
     *
     * @param int $days  Rows older than this many days are removed
     * @return int       Number of rows deleted
     */
    public function purgeOlderThan(int $days): int
    {
        try {
            $stmt = $this->connection->prepare(
                "DELETE FROM {$this->tabla}
                 WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)"
            );
            $stmt->execute([':days' => $days]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return 0;
        }
    }
}
