<?php
/**
 * Database Connection Class
 * This class handles database connections using the configuration from conf.php
 */

class Database {
    private $pdo;
    private $conf;
    private $stubMode = false;
    
    public function __construct($conf) {
        if ($conf === null || !is_array($conf)) {
            throw new Exception("Database configuration is required and must be an array.");
        }
        
        // Validate required configuration keys
        $required_keys = ['db_host', 'db_port', 'db_name', 'db_user', 'db_pass'];
        foreach ($required_keys as $key) {
            if (!isset($conf[$key])) {
                throw new Exception("Database configuration missing required key: $key");
            }
        }
        
        $this->conf = $conf;
        $this->connect();
    }
    
    /**
     * Establish database connection
     * Supports both MySQL and PostgreSQL (Supabase)
     */
    private function connect() {
        // If configuration explicitly disables DB or required credentials are missing,
        // enter stub mode so the application can run without a database.
        $driver = isset($this->conf['db_driver']) ? $this->conf['db_driver'] : 'mysql';

        if ($driver === 'none' || empty($this->conf['db_host']) || empty($this->conf['db_user']) || empty($this->conf['db_name'])) {
            // Stub mode: no PDO connection, methods will return safe defaults
            $this->pdo = null;
            $this->stubMode = true;
            error_log("Database running in stub mode: no connection established.");
            return;
        }

        try {
            // Create DSN (Data Source Name) based on driver
            if ($driver === 'pgsql') {
                // PostgreSQL DSN for Supabase
                $dsn = "pgsql:host=" . $this->conf['db_host'] . 
                       ";port=" . $this->conf['db_port'] . 
                       ";dbname=" . $this->conf['db_name'] . 
                       ";options='--client_encoding=UTF8'";
            } else {
                // MySQL DSN (original)
                $dsn = "mysql:host=" . $this->conf['db_host'] . 
                       ";port=" . $this->conf['db_port'] . 
                       ";dbname=" . $this->conf['db_name'] . 
                       ";charset=utf8mb4";
            }

            // PDO options for better security and error handling
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
                PDO::ATTR_TIMEOUT            => 10, // 10 second timeout
            ];

            // For PostgreSQL, add connection timeout to DSN
            if ($driver === 'pgsql') {
                $dsn .= ";connect_timeout=10";
            }

            // Create PDO instance
            $this->pdo = new PDO($dsn, $this->conf['db_user'], $this->conf['db_pass'], $options);

        } catch (PDOException $e) {
            // Log error and throw a more user-friendly exception
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }
    }
    
    /**
     * Get PDO instance
     */
    public function getPDO() {
        return $this->pdo;
    }
    
    /**
     * Execute a prepared statement
     */
    public function query($sql, $params = []) {
        // If running in stub mode (no PDO), return a fake statement object with safe defaults
        if ($this->stubMode || $this->pdo === null) {
            return new class {
                public function execute($p = []) { return true; }
                public function fetch() { return false; }
                public function fetchAll() { return []; }
                public function rowCount() { return 0; }
            };
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query failed: " . $e->getMessage());
            throw new Exception("Database operation failed. Please try again later.");
        }
    }
    
    /**
     * Insert data and return last insert ID
     */
    public function insert($sql, $params = []) {
        if ($this->stubMode || $this->pdo === null) {
            // Return 0 to indicate no real insert occurred
            return 0;
        }

        $this->query($sql, $params);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Fetch a single row
     */
    public function fetchOne($sql, $params = []) {
        if ($this->stubMode || $this->pdo === null) {
            return false;
        }

        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $params = []) {
        if ($this->stubMode || $this->pdo === null) {
            return [];
        }

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Execute a statement (for INSERT, UPDATE, DELETE)
     */
    public function execute($sql, $params = []) {
        if ($this->stubMode || $this->pdo === null) {
            return 0;
        }

        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Get last inserted ID
     */
    public function lastInsertId() {
        if ($this->stubMode || $this->pdo === null) return 0;
        return $this->pdo->lastInsertId();
    }

    /**
     * Returns whether the Database is running in stub (no-db) mode
     */
    public function isStubMode() {
        return $this->stubMode === true;
    }
    
    /**
     * Count rows
     */
    public function count($sql, $params = []) {
        if ($this->stubMode || $this->pdo === null) return 0;
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Check if table exists
     */
    public function tableExists($tableName) {
        try {
            // Use information_schema to reliably check existence
            $sql = "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :t LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':t' => $tableName]);
            return (bool) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("tableExists check failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * Close connection
     */
    public function close() {
        $this->pdo = null;
    }
}
?>