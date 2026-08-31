<?php

/**
 * Connection class
 *
 * Manages the database connection using the Singleton pattern
 * to ensure a single connection instance throughout the application.
 *
 * @package ProyectoBase
 * @subpackage App\Config
 * @author jandrescodes
 * @version 1.0
 */

namespace App\Config;

use PDO;
use PDOException;
use Exception;
use DateTime;
use DateTimeZone;

class Connection
{
    /**
     * Unique instance of the Connection class
     *
     * @var Connection
     */
    private static $instance = null;

    /**
     * PDO object for the database connection
     *
     * @var PDO
     */
    private $connection;

    /**
     * Private constructor to prevent direct object creation
     */
    private function __construct()
    {
        try {
            // Attempt to load the configuration from config.php
            $config_file = __DIR__ . '/config.php';

            if (!file_exists($config_file)) {
                throw new Exception("The configuration file does not exist");
            }

            $config = require $config_file;

            // Validate the configuration
            if (!is_array($config) || !isset($config['database']) || !is_array($config['database'])) {
                throw new Exception("The database configuration is not valid");
            }

            $db_config = $config['database'];

            // Ensure all required parameters are present
            if (
                !isset($db_config['host']) || !isset($db_config['name']) ||
                !isset($db_config['user']) || !isset($db_config['pass'])
            ) {
                throw new Exception("Missing database configuration parameters");
            }

            // Use utf8 instead of utf8mb4 to avoid compatibility issues
            $dsn = "mysql:host={$db_config['host']};dbname={$db_config['name']};charset=utf8";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->connection = new PDO($dsn, $db_config['user'], $db_config['pass'], $options);

            // Get the timezone from the app configuration
            $timezone = isset($config['app']['timezone']) ? $config['app']['timezone'] : 'America/La_Paz';

            // Set the MariaDB timezone based on the app configuration
            $timezone_offset = $this->getTimezoneOffset($timezone);
            $this->connection->exec("SET time_zone = '{$timezone_offset}'");

            // Optional verification (can be removed in production)
            $stmt = $this->connection->query("SELECT @@session.time_zone");
            $set_tz = $stmt->fetchColumn();
            if ($set_tz !== $timezone_offset) {
                error_log("Warning: Could not correctly set the MariaDB timezone. Requested: {$timezone_offset}, Current: {$set_tz}");
            }
        } catch (PDOException $e) {
            die("Database connection error: " . $e->getMessage());
        } catch (Exception $e) {
            die("Configuration error: " . $e->getMessage());
        }
    }

    /**
     * Returns the unique instance of the Connection class
     *
     * @return Connection The connection instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Returns the PDO connection object
     *
     * @return PDO The PDO connection object
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Executes an SQL query and returns the result
     *
     * @param string $sql    SQL query to execute
     * @param array  $params Parameters for the prepared statement
     * @return \PDOStatement Query result
     */
    public function query($sql, $params = [])
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Converts a timezone name to an offset string for MariaDB
     *
     * @param string $timezone Timezone name (e.g. America/La_Paz)
     * @return string Offset in '+HH:MM' or '-HH:MM' format
     */
    private function getTimezoneOffset($timezone)
    {
        try {
            $dateTimeZone = new DateTimeZone($timezone);
            $dateTime = new DateTime('now', $dateTimeZone);
            $offset = $dateTimeZone->getOffset($dateTime);

            // Convert seconds to +/-HH:MM format
            $hours = intval(abs($offset) / 3600);
            $minutes = intval((abs($offset) % 3600) / 60);
            $sign = $offset < 0 ? '-' : '+';

            return $sign . sprintf('%02d:%02d', $hours, $minutes);
        } catch (Exception $e) {
            error_log("Error calculating timezone offset: " . $e->getMessage());
            return '-04:00'; // Default value for Bolivia on error
        }
    }
}
