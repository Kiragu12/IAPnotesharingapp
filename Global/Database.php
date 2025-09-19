<?php
/**
 * Database Connection Class
 * This class handles database connections using the configuration from conf.php
 */

class Database {
    private $pdo;
    private $conf;
    
    public function __construct($conf) {
        $this->conf = $conf;
        $this->connect();
    }
    
    /**
     * Establish database connection
     */
    private function connect() {
        try {
            // Create DSN (Data Source Name)
            $dsn = "mysql:host=" . $this->conf['db_host'] . 
                   ";port=" . $this->conf['db_port'] . 
                   ";dbname=" . $this->conf['db_name'] . 
                   ";charset=utf8mb4";
            
            // PDO options for better security and error handling
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
            ];
            
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
        $this->query($sql, $params);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Fetch a single row
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Count rows
     */
    public function count($sql, $params = []) {
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