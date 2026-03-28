<?php
/**
 * MoneyFlow - Configuración de Base de Datos (EJEMPLO)
 * 
 * INSTRUCCIONES:
 * 1. Renombra este archivo a "database.php"
 * 2. Completa los valores con tu configuración real
 * 3. NO commitees el archivo database.php al repositorio
 */

class Database {
    // Configuración de conexión
    private $host = 'localhost';          // Host de MySQL
    private $db_name = 'moneyflow';       // Nombre de la base de datos
    private $username = 'root';           // Usuario de MySQL (CAMBIAR)
    private $password = '';               // Contraseña de MySQL (CAMBIAR)
    private $charset = 'utf8mb4';
    private $conn = null;
    
    /**
     * Obtener conexión a la base de datos
     * @return PDO|null
     */
    public function getConnection() {
        if ($this->conn !== null) {
            return $this->conn;
        }
        
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            return $this->conn;
            
        } catch(PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            die("Error de conexión a la base de datos. Por favor, verifica la configuración.");
        }
    }
    
    /**
     * Cerrar conexión
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
