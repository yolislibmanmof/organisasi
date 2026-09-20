<?php
// File: app/Core/Database.php (FINAL v7.0 — EXTENDED + RESILIENT + OBSERVABLE)
declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;
use PDOStatement;
use Throwable;

/**
 * Database Manager — Ultimate Edition v7.0
 *
 * PDO wrapper dengan fitur:
 * - Graceful connection failure (throw exception, bukan die)
 * - Auto-reconnection (jika connection lost)
 * - Query logging (optional, untuk debugging)
 * - Query counter (untuk performance monitoring)
 * - Transaction helper (closure-based)
 * - Health check (ping connection)
 * - Safe error messages (tidak expose credentials)
 * - Multiple named connections support
 * - Backward compatible dengan signature v5.x
 */
class Database
{
    /** @var array<string, PDO> Named connection instances */
    private static array $instances = [];

    /** @var array<string, array> Connection configs */
    private static array $configs = [];

    /** @var bool Enable query logging */
    private static bool $queryLogEnabled = false;

    /** @var array<int, array{query: string, params: array, time: float}> Query log */
    private static array $queryLog = [];

    /** @var int Total query counter */
    private static int $queryCount = 0;

    /** @var float Total query execution time (ms) */
    private static float $totalQueryTime = 0.0;

    /* ============================================================
       PUBLIC API: GET INSTANCE
       ============================================================ */

    /**
     * Get PDO instance (default connection).
     * Backward compat signature v5.x.
     *
     * @throws PDOException Jika koneksi gagal
     */
    public static function getInstance(): PDO
    {
        return self::connection('default');
    }

    /**
     * Get named connection.
     * v7.0 extension: support multiple connections.
     *
     * @param string $name Connection name
     * @throws PDOException
     */
    public static function connection(string $name = 'default'): PDO
    {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = self::createConnection($name);
        }

        // Health check: ping connection
        if (!self::ping(self::$instances[$name])) {
            // Reconnect
            error_log("[Database] Connection '$name' lost, reconnecting...");
            self::$instances[$name] = self::createConnection($name);
        }

        return self::$instances[$name];
    }

    /**
     * Purge (close) specific connection.
     */
    public static function purge(string $name = 'default'): void
    {
        unset(self::$instances[$name]);
    }

    /**
     * Purge semua connections.
     */
    public static function purgeAll(): void
    {
        self::$instances = [];
    }

    /* ============================================================
       PUBLIC API: CONFIGURATION
       ============================================================ */

    /**
     * Add named connection config.
     *
     * @param string $name Connection name
     * @param array{host: string, name: string, user: string, pass: string, charset?: string} $config
     */
    public static function addConnection(string $name, array $config): void
    {
        self::$configs[$name] = $config;
    }

    /**
     * Enable/disable query logging.
     */
    public static function enableQueryLog(bool $enable = true): void
    {
        self::$queryLogEnabled = $enable;
    }

    /**
     * Get query log.
     *
     * @return array<int, array{query: string, params: array, time: float}>
     */
    public static function getQueryLog(): array
    {
        return self::$queryLog;
    }

    /**
     * Get total query count.
     */
    public static function getQueryCount(): int
    {
        return self::$queryCount;
    }

    /**
     * Get total query time (ms).
     */
    public static function getTotalQueryTime(): float
    {
        return self::$totalQueryTime;
    }

    /**
     * Clear query log & counter.
     */
    public static function flushQueryLog(): void
    {
        self::$queryLog = [];
        self::$queryCount = 0;
        self::$totalQueryTime = 0.0;
    }

    /* ============================================================
       PUBLIC API: TRANSACTION HELPER
       ============================================================ */

    /**
     * Run closure dalam transaction (auto commit/rollback).
     *
     * Contoh:
     * Database::transaction(function($pdo) {
     *     $pdo->prepare('INSERT INTO ...')->execute([...]);
     *     $pdo->prepare('UPDATE ...')->execute([...]);
     * });
     *
     * @template T
     * @param callable(PDO): T $callback
     * @param int $attempts Retry attempts untuk deadlock
     * @return T
     * @throws Throwable
     */
    public static function transaction(callable $callback, int $attempts = 1): mixed
    {
        $pdo = self::getInstance();
        $lastException = null;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                $pdo->beginTransaction();

                try {
                    $result = $callback($pdo);
                    $pdo->commit();
                    return $result;
                } catch (Throwable $e) {
                    $pdo->rollBack();
                    throw $e;
                }
            } catch (PDOException $e) {
                $lastException = $e;
                // Retry jika deadlock (error code 1213)
                if ($e->getCode() === '1213' && $attempt < $attempts) {
                    usleep(100000); // 100ms delay sebelum retry
                    continue;
                }
                throw $e;
            }
        }

        throw $lastException ?? new PDOException('Transaction failed');
    }

    /* ============================================================
       PUBLIC API: CONVENIENCE METHODS
       ============================================================ */

    /**
     * Execute raw query dengan logging.
     *
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $pdo = self::getInstance();
        return self::executeWithLog($pdo, $sql, $params);
    }

    /**
     * Check apakah connection masih alive (ping).
     */
    public static function ping(?PDO $pdo = null): bool
    {
        $pdo = $pdo ?? (self::$instances['default'] ?? null);
        if ($pdo === null) return false;

        try {
            $pdo->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /* ============================================================
       PRIVATE: CONNECTION CREATION
       ============================================================ */

    /**
     * Create new PDO connection.
     */
    private static function createConnection(string $name): PDO
    {
        $config = self::$configs[$name] ?? self::getDefaultConfig();

        $host    = $config['host'] ?? 'localhost';
        $dbName  = $config['name'] ?? '';
        $user    = $config['user'] ?? '';
        $pass    = $config['pass'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';
        $port    = $config['port'] ?? 3306;

        $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=$charset";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $charset COLLATE {$charset}_unicode_ci",
        ];

        // Persistent connection untuk performance (optional)
        if (!empty($config['persistent'])) {
            $options[PDO::ATTR_PERSISTENT] = true;
        }

        try {
            return new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Sanitize error message (jangan expose credentials)
            $safeMessage = "Database connection failed for '$name'";
            if (defined('APP_DEBUG') && APP_DEBUG) {
                $safeMessage .= ': ' . $e->getMessage();
            } else {
                $safeMessage .= '. Check your configuration.';
            }

            error_log("[Database] $safeMessage");
            throw new PDOException($safeMessage, (int) $e->getCode(), $e);
        }
    }

    /**
     * Get default config dari constants (backward compat).
     */
    private static function getDefaultConfig(): array
    {
        return [
            'host'    => defined('DB_HOST') ? DB_HOST : 'localhost',
            'name'    => defined('DB_NAME') ? DB_NAME : '',
            'user'    => defined('DB_USER') ? DB_USER : '',
            'pass'    => defined('DB_PASS') ? DB_PASS : '',
            'charset' => 'utf8mb4',
        ];
    }

    /**
     * Execute query dengan logging & timing.
     */
    private static function executeWithLog(PDO $pdo, string $sql, array $params = []): PDOStatement
    {
        $start = microtime(true);

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } catch (PDOException $e) {
            $elapsed = (microtime(true) - $start) * 1000;
            self::logQuery($sql, $params, $elapsed, $e->getMessage());
            throw $e;
        }

        $elapsed = (microtime(true) - $start) * 1000;
        self::logQuery($sql, $params, $elapsed);

        return $stmt;
    }

    /**
     * Log query ke memory.
     */
    private static function logQuery(string $sql, array $params, float $timeMs, ?string $error = null): void
    {
        self::$queryCount++;
        self::$totalQueryTime += $timeMs;

        if (self::$queryLogEnabled) {
            self::$queryLog[] = [
                'query'  => $sql,
                'params' => $params,
                'time'   => round($timeMs, 2),
                'error'  => $error,
            ];
        }

        // Log slow queries (> 1 second)
        if ($timeMs > 1000) {
            error_log(sprintf(
                '[Database] SLOW QUERY (%.2f ms): %s | params: %s',
                $timeMs,
                $sql,
                json_encode($params)
            ));
        }
    }
}