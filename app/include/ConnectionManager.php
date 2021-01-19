<?php

class ConnectionManager {
   
    public function getConnection() {
        
        $host = "localhost";
        $username = "root";

        if (PHP_OS === "Darwin") {
            $password = "root";
        } 
        elseif (PHP_OS === "WINNT") {
            $password = "";
        }

        $dbname = "spm_database"; 
        $port = 3306;    

        $url  = "mysql:host={$host};dbname={$dbname};port={$port}";
        
        $conn = new PDO($url, $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
        return $conn;  
        
    }
    
}
