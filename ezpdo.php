<?php
/** 
 *  Ezpdo - A PHP PDO wrapper class
 * 
 *  @Author     Qiang Ji
 *  @version    1.0
 */
class Ezpdo {
    private $_db;
    private $_host;
    private $_username;
    private $_password;
    private $_dsn;
    private $_conn;
    private $_is_error;
    private $_error_message;
    private $_pdo_statement;
	
    /**
     *  Constructor
     *  
     *  @api
     *
     *  @param string $host Database host name
     *  @param string $db Database name
     *  @param string $username Database access username
     *  @param string $password Database access password
     */
    public function __construct($host = "", $db = "", $username = "", $password = "") {
        $this->_db              = $db;
        $this->_host            = $host;
        $this->_username        = $username;
        $this->_password        = $password;
        $this->_is_error        = FALSE;
        $this->_error_message   = "";
        $this->_dsn             = "";
        $this->_conn            = null;
        $this->_pdo_statement   = null;
    }
    
    /**
     *  Connect to database using PDO
     *
     *  @api
     *
     *  @return boolean Indicates database is connected or not
     */
    public function connect() {
        $this->_dsn = "mysql:dbname={$this->_db};host={$this->_host}";
        try {
            $this->_conn = new PDO($this->_dsn, $this->_username, $this->_password);
        } catch (PDOException $e) {
            $_is_error = TRUE;
            $_error_message = $e->getMessage();
        }
        return $this->_is_error;
    }
    
    /**
     *  Construct SQL query and execute it
     *
     *  @api
     *
     *  @param string $sql_statement The SQL statement
     *  @param boolean $is_prepared Indicates to use PDO prepare function. In this case, SQL statement will not be executed.
     *  @return mixed[] If execute query in this function; boolean If using prepare function
     */
    public function query($sql_statement, $is_prepared = FALSE) {
        $result = TRUE;
        if ($is_prepared) {
            $this->_pdo_statement = $this->_conn->prepare($sql_statement);
            if ($this->_pdo_statement === FALSE) {
                $result = FALSE;
                $this->_is_error = TRUE;
                $this->_error_message = $this->_conn->errorInfo();
            }
        } else {
            $result = $this->_conn->query($sql_statement, PDO::FETCH_OBJ);
            if (FALSE === $result) {
                $this->_is_error = TRUE;
                $this->_error_message = $this->_conn->errorInfo();
            }            
        }
        return $result;
    }
    
    /**
     *  Execute prepared SQL query
     *
     *  @api
     *
     * $param array $params Indicates parameters for each execution
     * @return mixed[] If execute query successfully; boolean If execution fails or not
     */
    public function execute($params = array()) {
        $result = FALSE;
        
        if (!is_array($params)) {
            $this->_is_error = TRUE;
            $this->_error_message = "execute() expects an array parameter\n";
            return $result;
        }
        
        if (empty($this->_pdo_statement)) {
            $this->_is_error = TRUE;
            $this->_error_message = "query() has not been called\n";
            return $result;
        }
        
        foreach ($params AS $idx => $param) {
            $result = $this->_pdo_statement->bindParam($idx + 1, $param);
            if ($result === FALSE) {
                $this->_is_error = TRUE;
                $this->_error_message = $this->_pdo_statement->errorInfo()."\n";
                return $result;
            }
        }
        
        $result = $this->_pdo_statement->execute();
        if ($result === FALSE) {
            $this->_is_error = TRUE;
            $this->_error_message = $this->_pdo_statement->errorInfo()."\n";
            return $result;
        }
        
        $result = $this->_pdo_statement->fetchAll(PDO::FETCH_OBJ);
        
        $this->_pdo_statement->closeCursor();
        
        return $result;
    }
    
    /**
     *  Set database name
     *
     *  @api
     *
     *  @param string $db Database name
     */
    public function set_db($db) {
        $this->_db = $db;
    }
    
    /**
     *  Set database host name
     *
     *  @api
     *
     *  @param string $host Database host name
     */
    public function set_host($host) {
        $this->_host = $host;
    }
    
    /**
     *  Set database access username
     *
     *  @api
     *
     *  @param string $username Database access username
     */
    public function set_username($username) {
        $this->_username = $username;
    }
    
    /**
     *  Set database access password
     *
     *  @api
     *
     *  @param string $password Database access password
     */
    public function set_password($password) {
        $this->_password = $password;
    }
    
    /**
     *  Check if there is an error
     *
     *  @api
     *
     *  @return boolean Indicates there is an error or not
     */
    public function is_error() {
        return $this->_is_error;
    }
    
    /**
     *  Get error message
     *
     *  @api
     *
     *  @return string Get error message
     */
    public function error_message() {
        return $this->_error_message;
    }
}
/* end of ezpdo.php */
