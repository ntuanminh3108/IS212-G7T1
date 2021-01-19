<?php

class BidDAO {
    
    // returns all bids made by a specific userid.
    public function retrieve($userid) { 
        $sql = 'select * from bid where userid=:userid';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();
        
        $result = [];
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Bid($row['userid'], $row['amount'], $row['code'],$row['section'],$row['status'], $row['round']);
        }
        $stmt = null;
        $conn = null; 
        return $result;

    }

    // returns all bids made by a specific userid for a specific course.
    public function retrieveBidByUseridAndCourse($userid, $course) {
        $sql = 'select * from bid where userid=:userid and code=:course';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->execute();
        $result = null;
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = new Bid($row['userid'], $row['amount'], $row['code'],$row['section'],$row['status'], $row['round']);
        }
        $stmt = null;
        $conn = null; 
        return $result;
    }

    // returns all bids made by a specific userid for a specific course in a specific round.
    public function retrieveBidByUserIDAndCourseAndRound($userid, $course, $round) {
        $sql = 'select * from bid where userid=:userid and code=:course and round=:round';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':round', $round, PDO::PARAM_INT);
        $stmt->execute();
        $result = null;
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = new Bid($row['userid'], $row['amount'], $row['code'],$row['section'],$row['status'], $row['round']);
        }
        $stmt = null;
        $conn = null; 
        return $result;
    }

    // returns all bids made for a specific section and round with a specific status.
    public function retrieveBidBySectionForRound($course,$section,$status,$round) { 
        $sql = "select * from bid where code=:code and section=:section and status=:status and round=:round";
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
                    
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':code', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':round', $round, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = [];
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Bid($row['userid'], $row['amount'], $row['code'],$row['section'],$row['status'], $row['round']);
        }

        $stmt = null;
        $conn = null; 
        return $result;

    }

    // returns all bids that are made in a specific round and has a specific status.
    public function retrieveBiddedSectionsByStatusAndRound($status, $round) {
        $sql = 'select * from bid where status=:status and round=:round';
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
                    
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':round', $round, PDO::PARAM_INT);
        $stmt->execute();

        $result = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $section = [$row['code'],$row['section']];
            if (!in_array($section, $result)) {
                $result[] = [$row['userid'], $row['amount'], $row['code'],$row['section'],$row['status'], $row['round']];
            }
        }
        $stmt = null;
        $conn = null; 
        return $result;
    }

    // returns all bids.
    public function retrieveAll() { 
        $sql = 'select * from bid';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        
        $result = [];
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Bid($row['userid'], $row['amount'], $row['code'],$row['section'],$row['status'], $row['round']);
        }
        $stmt = null;
        $conn = null; 

        return $result;
    }

    // remove all bids.
    public function removeAll() {
        $sql = 'TRUNCATE TABLE bid';
                
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
                
        $stmt = $conn->prepare($sql);
                
        $stmt->execute();
        $stmt = null; 
        $conn = null;
        
    }

    // add a bid into the bid table of the database.
    public function add($userid, $amount, $course_code, $section_no, $status, $round) {
        $sql = 'INSERT INTO bid (userid, amount, code, section, status, round) VALUES (:userid, :amount, :code, :section, :status, :round)';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':code', $course_code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section_no, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':round', $round, PDO::PARAM_INT);

        $stmt->execute();

        $stmt = null; 
        $conn = null;

    }

    // updates a bid status given a specific userid, course, section and round.
    public function updateBidStatus($userid, $course, $section,$status,$round) {
        $sql = 'UPDATE bid set status=:status where userid=:userid and code=:course and section=:section and round=:round';
                
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':round', $round, PDO::PARAM_INT);        
        $stmt->execute();

        $stmt = null; 
        $conn = null;
        
    }

    // returns all bids made for a specific section that has the same specific amount and status.
    public function getBidsWithSameAmount($course, $section, $status, $amount) {
        $sql = 'select * from bid where code=:code and section=:section and amount=:amount and status=:status';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':code', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        
        $stmt->execute();
        
        $result = [];
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Bid($row['userid'], $row['amount'], $row['code'],$row['section'],$row['status'], $row['round']);
        }
        $stmt = null;
        $conn = null; 

        return $result;

    }

    // remove bids made by a specific userid for a specific course.
    public function removeBid($userid, $course) {
        $sql = 'DELETE FROM bid WHERE userid=:userid and code=:course';
                
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);       
        $stmt->execute();

        $stmt = null; 
        $conn = null;
    }

    /*  The following function is obsolete.
    public function verifySameBid($userid, $course, $round) {
        $sql = 'select * from bid where userid=:userid and code=:code and round=:round';
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $course, PDO::PARAM_STR);
        $stmt->bindParam(':round', $round, PDO::PARAM_INT);
        $stmt->execute();

        $result = False;
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {    
            $result = True;
        }
        
        $stmt = null; 
        $conn = null;
        return $result;
    }
    */
    
    /*  returns all bids made in round. Bids are sorted by:
        1. Ascending order of course and section.
        2. Descending order of bid amount.
        3. Ascending order of userid.
    */
    public function getAllBidsInRound($round) {
        $sql = 'select * from bid where round=:round order by code asc, section asc, amount desc, userid asc;';

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':round', $round, PDO::PARAM_INT);
        
        $stmt->execute();
        
        $result = [];
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            
            $result[] = new Bid($row['userid'], $row['amount'], $row['code'],$row['section'],$row['status'], $row['round']);
        }

        $stmt = null;
        $conn = null; 
        return $result;
    }

    // returns all successful bids. Bids are sorted by ascending order of course code and userid. 
    public function getAllSuccessfulBids() { 
        $sql = "select * from bid where status='S' order by code asc, userid asc;";
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
                    
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        
        $result = [];
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            
            $result[] = new Bid($row['userid'], $row['amount'], $row['code'],$row['section'],$row['status'], $row['round']);
        }

        $stmt = null;
        $conn = null; 
        return $result;

    }

    // returns all bids made in a specific round for a specific section.
    public function getAllBidsInRoundCourseSection($round, $course, $section) {
        $sql = 'select * from bid where code=:code and section=:section and round=:round order by amount desc, userid asc;';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':code', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':round', $round, PDO::PARAM_INT);
        
        $stmt->execute();
        
        $result = [];
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            
            $result[] = new Bid($row['userid'], $row['amount'], $row['code'],$row['section'],$row['status'], $row['round']);
        }

        $stmt = null;
        $conn = null; 
        return $result;
    }
    
    // returns all bids for a specific section that has a specific status.
    public function getBidBySection($course,$section,$status) { 
        $sql = "select * from bid where code=:code and section=:section and status=:status order by userid asc";
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
                    
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':code', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();
        
        $result = [];
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            
            $result[] = new Bid($row['userid'], $row['amount'], $row['code'],$row['section'],$row['status'], $row['round']);
        }
        
        $stmt = null;
        $conn = null; 
        return $result;

    }

    // get all bids made by a specific userid for a specific course and has a specific status
    public function getBidByUserIDCourseStatus($userid, $course,$status) {
        $sql = 'select * from bid where userid=:userid and code=:course and status=:status';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();
        $result = null;
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = new Bid($row['userid'], $row['amount'], $row['code'],$row['section'],$row['status'], $row['round']);
        }
        $stmt = null;
        $conn = null; 
        return $result;
    }
}
?>