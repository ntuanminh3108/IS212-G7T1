<?php

class SectionDAO {

    // returns all sections in the Section table.
    public function getAllSections() { 
        $sql = 'select * from section';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result=[];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Section($row['course'], $row['section'],$row['day'], $row['start'], $row['end'],$row['instructor'],$row['venue'],$row['size'],$row['minBid'],$row['vacancy']);
        }
        $stmt = null; 
        $conn = null;
        return $result;
    }

    // returns all section objects for a specific course.
    public function retrieveAllByCourse($course) { 
        $sql = 'select * from section where course =:course';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->execute();

        $result=[];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Section($row['course'], $row['section'],$row['day'], $row['start'], $row['end'],$row['instructor'],$row['venue'],$row['size'],$row['minBid'],$row['vacancy']);
        }
        $stmt = null;
        $conn = null;
        return $result;
    }

    // returns a specific section.
    public function retrieveBySectionAndCourse($course, $section) { 
        $sql = 'select * from section where course=:course and section=:section';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->execute();
        $result = null;
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = new Section($row['course'], $row['section'],$row['day'], $row['start'], $row['end'],$row['instructor'],$row['venue'],$row['size'],$row['minBid'],$row['vacancy']);
        }
        $stmt = null;
        $conn = null;
        return $result;
    }

    // returns all the sections of a particular course. Only the sections are returned. No other information (e.g. day) is included.
    public function retrieveOnlySectionByCourse($course) { 
        $sql = 'select section from section where course =:course';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->execute();

        $result=[];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row["section"];
        }
        $stmt = null;
        $conn = null;
        return $result;
    }

    // returns all sections which are taught by a specific instructor.
    public function retrieveByInstructor($instrutor) { 
        $sql = 'select * from section where instrutor =:instrutor';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':instrutor', $instrutor, PDO::PARAM_STR);
        $stmt->execute();

        $result=[];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Section($row['course'], $row['section'],$row['day'], $row['start'], $row['end'],$row['instructor'],$row['venue'],$row['size'],$row['minBid'],$row['vacancy']);
        }
        $stmt = null;
        $conn = null;
        return $result;
    }

    // remove all sections from the section table in the database.
    public function removeAll() {
        $sql = 'SET FOREIGN_KEY_CHECKS = 0; 
        TRUNCATE table section; 
        SET FOREIGN_KEY_CHECKS = 1;';
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $count = $stmt->rowCount();
    }

    /**
	 * Returns a course within the database based on it's course code  
     * 
	 * @param string Course ("IS210")
	 * @param string Section ("S1")
	 * @param int Day (1)
	 * @param string Start ("8:30")
	 * @param string End ("10:30")
	 * @param string Instructor ("Sun Jun")
	 * @param string Venue ("SIS SR 3-2")
	 * @param string Size (50)
     * 
	 * @return null
	 */

    public function add($course_code, $section, $day, $start, $end, $instrutor, $venue, $size, $minBid, $vacancy) {
        $sql = 'INSERT INTO section (course, section, day, start, end, instructor, venue, size, minBid, vacancy) VALUES (:course, :section, :day, :start, :end, :instructor, :venue, :size, :minBid, :vacancy)';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course_code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':day', $day, PDO::PARAM_INT);
        $stmt->bindParam(':start', $start, PDO::PARAM_STR);
        $stmt->bindParam(':end', $end, PDO::PARAM_STR);
        $stmt->bindParam(':instructor', $instrutor, PDO::PARAM_STR);
        $stmt->bindParam(':venue', $venue, PDO::PARAM_STR);
        $stmt->bindParam(':size', $size, PDO::PARAM_INT);
        $stmt->bindParam(':minBid', $minBid, PDO::PARAM_STR);
        $stmt->bindParam(':vacancy', $vacancy, PDO::PARAM_INT);

        $stmt->execute();

        $stmt = null; 
        $conn = null;

    }

    // updates the vacancy to a specific number for a specific section.
    public function updateVacancy($course, $section, $vacancy) {
        $sql = 'UPDATE section set vacancy=:vacancy where course=:course and section=:section';
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':vacancy', $vacancy, PDO::PARAM_INT);

        $stmt->execute();

        $stmt = null; 
        $conn = null;

    } 

    // updates the min bid to a specific amount for a specific section.
    public function updateMinBid($course, $section, $minBid) {
        $sql = 'UPDATE section set minBid=:minBid where course=:course and section=:section';
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':minBid', $minBid, PDO::PARAM_STR);

        $stmt->execute();

        $stmt = null; 
        $conn = null;

    } 
}
    ?>