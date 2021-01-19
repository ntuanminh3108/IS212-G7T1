<?php

    class PrerequisiteDAO {

        /*  returns all prerequisites, sorted by:
            1. ascending order of course
            2. ascending order of prerequisites.
        */
        public function getAll(){
            $sql = 'select * from prerequisite order by course asc, prerequisite asc;';
            
            $connMgr = new ConnectionManager();
            $conn = $connMgr->getConnection();
            
                
            $stmt = $conn->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute();
            
            $result = [];
            
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                
                $result[] = new Prerequisite($row['course'], $row['prerequisite']);
            }
            $stmt = null;
            $conn = null; 
            return $result;

        }
        
        // returns all prerequisites for a specific course.
        public function retrieve($course) { 
            $sql = 'select course, prerequisite from prerequisite where course=:course';
            
            $connMgr = new ConnectionManager();
            $conn = $connMgr->getConnection();
            
                
            $stmt = $conn->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->bindParam(':course', $course, PDO::PARAM_STR);
            $stmt->execute();
            
            $result = [];
            
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                
                $result[] = new Prerequisite($row['course'], $row['prerequisite']);
            }
            $stmt = null;
            $conn = null; 
            return $result;

        }

        // add a prerequisite for a specific course
        public function add($course_code, $prerequisite) {
            $sql = 'INSERT INTO prerequisite (course, prerequisite) VALUES (:course, :prerequisite)';
            
            $connMgr = new ConnectionManager();
            $conn = $connMgr->getConnection();
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':course', $course_code, PDO::PARAM_STR);
            $stmt->bindParam(':prerequisite', $prerequisite, PDO::PARAM_STR);

            $stmt->execute();

            $stmt = null; 
            $conn = null;

        } 

        // remove all prerequisites from the prerequisite table.
        public function removeAll() {
            $sql = 'TRUNCATE TABLE prerequisite';
                    
            $connMgr = new ConnectionManager();
            $conn = $connMgr->getConnection();
                    
            $stmt = $conn->prepare($sql);
                    
            $stmt->execute();
            $count = $stmt->rowCount();

            $stmt = null; 
            $conn = null;
            }    
        }

?>