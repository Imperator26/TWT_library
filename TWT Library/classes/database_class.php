<?php

abstract class Database extends Logger
{
    // Visibility modifiers available from PHP 7.1.0
    const HOSTNAME = "localhost";
    const USERNAME = "frozenoak";
    const PASSWORD = "";
    const DATABASE_NAME = "my_frozenoak";
    
    protected $mysqli;
    protected $stmt;
    protected $insert_id;
    
    // Getters and Setters
    public function getLastInsertedId()
    {
        return $this->insert_id;
    }
    
    protected function openConnection()
    {
        // Initialize connection
        $this->mysqli = new mysqli(self::HOSTNAME, self::USERNAME, self::PASSWORD, self::DATABASE_NAME);

        // Check connection
        if ($this->mysqli->connect_errno) {
            printf("Connect failed: %s\n", $this->mysqli->connect_error);
            exit();
        }
    }
    
    protected function bindParameters($types, $parameters)
    {
        if (!is_array($parameters)) {
            // Single parameter
            if (!$this->stmt->bind_param($types, $parameters)) $this->logError("Prepare failed.");
        } else {
            // Multiple parameters
            array_unshift($parameters, $types);
            $references = array();
            foreach ($parameters as $key => $value) $references[$key] = &$parameters[$key];
            call_user_func_array(array($this->stmt, "bind_param"), $references);
        }
    }
    
    public function executeQuery($statement, $types=NULL, $parameters=NULL)
    {
        // Establish connection
        $this->openConnection();
        $this->logStatus("Connection ok.");
        
        // Prepare statement
        if (!($this->stmt = $this->mysqli->prepare($statement))) $this->logError("Prepare failed.");
        // Bind parameters if there's any
        if ($types) $this->bindParameters($types, $parameters);
        // Execute query
        if (!$this->stmt->execute()) $this->reportError();
        
        // Get result
        $result = $this->stmt->get_result();
        
        // Save the last inserted id
        $this->insert_id = $this->stmt->insert_id;
        
        // Close statement and connection
        $this->stmt->close();
        
        return $result;
    }
    
    protected function reportError()
    {
        $this->logError("Error: ".$this->stmt->error);
        die($this->stmt->error);
    }
}
