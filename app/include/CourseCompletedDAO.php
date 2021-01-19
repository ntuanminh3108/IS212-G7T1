<?php
    
    class CourseCompletedDAO{

        // removes all course_completed records in the course_completed table of the database.
        public function removeAll() {
            $sql = 'TRUNCATE TABLE course_completed';
            
            $connMgr = new ConnectionManager();
            $conn = $connMgr->getConnection();
            
            $stmt = $conn->prepare($sql);
            
            $status = $stmt->execute();
            $stmt = null; 
            $conn = null;
            return $status;
    
        } 

        // add a course_completed record into the course_completed table of the database.
        public function add($userid, $course_code) {
            $sql = 'INSERT INTO course_completed (userid, code) VALUES (:userid, :code)';
            
            $connMgr = new ConnectionManager();
            $conn = $connMgr->getConnection();
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
            $stmt->bindParam(':code', $course_code, PDO::PARAM_STR);

            $stmt->execute();

            $stmt = null; 
            $conn = null;

        } 

        /*  returns all records from the course_completed table, sorted by:
            1. Ascending order of course code.
            2. Ascending order of userid.
        */
        public  function getAll() { 
            $sql = 'select * from course_completed order by code asc, userid asc;';
            
            $connMgr = new ConnectionManager();
            $conn = $connMgr->getConnection();
            
                
            $stmt = $conn->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute();
            $result = [];
    
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] =  new CourseCompleted($row['userid'], $row['code']);
            }
            $stmt = null;
            $conn = null;
            return $result;
    
        }

        // returns the records of a specific student completing a specific course.
        public function getStudentComplete($userid, $course) {
            $sql = 'select * from course_completed where userid=:userid and code=:code';
            
            $connMgr = new ConnectionManager();
            $conn = $connMgr->getConnection();
            
                
            $stmt = $conn->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
            $stmt->bindParam(':code', $course, PDO::PARAM_STR);
            $stmt->execute();
            $result = null;
    
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result =  new CourseCompleted($row['userid'], $row['code']);
            }
            $stmt = null;
            $conn = null;
            return $result;
        }
    }
?>