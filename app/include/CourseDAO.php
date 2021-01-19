<?php

class CourseDAO{

    /**
	 * Returns all the courses within the database as an array of course objects
	 * 
	 * @return array An array of course objects
	 */
    public  function retrieveAll() { 
        $sql = 'SELECT * FROM course';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Course($row['course'], $row['school'], $row['title'], $row['description'], $row['exam_date'], $row['exam_start'], $row['exam_end']);
        }
        $stmt = null; 
        $conn = null;
        return $result;

    }

    /**
	 * Returns all the courses provided by a specified school within the database as an array of course objects
     * 
	 * @param string School ("SOB", "SIS", "SOL", "SOA", "SOE", "SOSS")
     * 
	 * @return array An array of course objects
	 */
    public function retrieveBySchool($school){
        $sql = "SELECT * FROM course WHERE school = :school";

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':school', $school, PDO::PARAM_STR);
        $stmt->execute();
        $result = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Course($row['course'], $row['title'],$row['description'], $row['exam_date'], $row['exam_start'], $row['exam_end']);
        }
        $stmt = null;
        $conn = null;
        return $result;

    }

    /**
	 * Returns a course within the database based on it's title 
     * 
	 * @param string Title ("Software Project Management")
     * 
	 * @return array An array of course object 
	 */
    public function retrieveByTitle($title){
        $sql = "SELECT * FROM course WHERE title = :title";

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->execute();

        $result = null;
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result =  new Course($row['course'], $row['title'],$row['description'], $row['exam_date'], $row['exam_start'], $row['exam_end']);
        }
        $stmt = null;
        $conn = null;
        return $result;
    }

    /**
	 * Returns a course within the database based on it's course code  
     * 
	 * @param string Title ("IS210")
     * 
	 * @return array An array of course object 
	 */
    public function retrieveByCourse($course){
        $sql = "SELECT * FROM course WHERE course = :course";

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->execute();
        $result = null;

        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = new Course($row['course'], $row['school'], $row['title'],$row['description'], $row['exam_date'], $row['exam_start'], $row['exam_end']);
        }
        
        $stmt = null;
        $conn = null;
        return $result;
    }

    /**
	 * Deletes the entire table  
     * 
	 * @return bool Status of the truncation statement execution
	 */
    public function removeAll() {
        $sql = 'SET FOREIGN_KEY_CHECKS = 0; 
        TRUNCATE table course; 
        SET FOREIGN_KEY_CHECKS = 1;';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        
        /*
            Uncomment to find out the number of rows affected:
            $count = $stmt->rowCount();
        */

        $stmt = null; 
        $conn = null;

    } 

    /**
	 * Returns a course within the database based on it's course code  
     * 
	 * @param string Course ("IS210")
	 * @param string School ("SIS")
	 * @param string Title ("Software Project Management")
	 * @param string Description ("This course is a hell of a ride.")
	 * @param string Exam Date ("20191015")
	 * @param string Exam Start ("8:30")
	 * @param string Exam End ("10:30")
     * 
	 * @return bool Status of the insertion statement execution
	 */
    public function add($course_code, $school, $title, $description, $exam_date, $exam_start, $exam_end) {
        $sql = 'INSERT INTO course (course, school, title, description, exam_date, exam_start, exam_end) VALUES (:course, :school, :title, :description, :exam_date, :exam_start, :exam_end)';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course_code, PDO::PARAM_STR);
        $stmt->bindParam(':school', $school, PDO::PARAM_STR);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':exam_date', $exam_date, PDO::PARAM_STR);
        $stmt->bindParam(':exam_start', $exam_start, PDO::PARAM_STR);
        $stmt->bindParam(':exam_end', $exam_end, PDO::PARAM_STR);

        $stmt->execute();

        $stmt = null; 
        $conn = null;
    } 
    
    public function getCourse($course){
        $sql = "SELECT * FROM course WHERE course = :course";

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->execute();
        $result = '';

        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = new Course($row['course'], $row['school'], $row['title'],$row['description'], $row['exam_date'], $row['exam_start'], $row['exam_end']);
        }
        
        $stmt = null;
        $conn = null;
        return $result;
    }
}


?>