<?php

class StudentDAO {
    
    // returns the student object for a specific userid.
    public  function retrieveByUserID($userid) { 
        $sql = 'SELECT userid, password, name, school, edollar FROM student WHERE userid=:userid';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();

        $result = null;

        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = new Student($row['userid'], $row['password'],$row['name'], $row['school'], $row['edollar']);
        }

        $stmt = null; 
        $conn = null;
        return $result;
    }

    /**
	 * Returns a course within the database based on it's course code  
     * 
	 * @param string User ID ("john.tan.2019")
	 * @param string Password ("qwerty123")
	 * @param string Name ("John")
	 * @param string School("SIS")
	 * @param decimal E Dollar ("100.00")
     * 
	 * @return bool Status of the insertion statement execution
	 */

    public function add($userid, $password, $name, $school, $edollar) {
        $sql = 'INSERT INTO student (userid, password, name, school, edollar) VALUES (:userid, :password, :name, :school, :edollar)';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':school', $school, PDO::PARAM_STR);
        $stmt->bindParam(':edollar', $edollar, PDO::PARAM_STR);

        $status = $stmt->execute();

        $stmt = null; 
        $conn = null;

        return $status;

    } 

    // returns all student objects.
    public function retrieveAll() {
        $sql = 'SELECT * FROM student';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Student($row['userid'], $row['password'],$row['name'], $row['school'], $row['edollar']);
        }

        $stmt = null; 
        $conn = null;

        return $result;
    }

    // removes all students from the student table in the database.
    public function removeAll() {
        $sql = 'SET FOREIGN_KEY_CHECKS = 0; 
        TRUNCATE table student; 
        SET FOREIGN_KEY_CHECKS = 1;';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();

        $stmt = null; 
        $conn = null;

    }
    
    // update the e-dollar balance to a specific amount for a specific userid.
    public function updateBalance($userid, $amount) {
        $sql = 'UPDATE student set edollar=:edollar where userid=:userid';
                
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':edollar', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);        
        $stmt->execute();
        $stmt = null; 
        $conn = null;    
    }
}
?>