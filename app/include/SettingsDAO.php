<?php

class SettingsDAO {

    // returns admin password.
    public function getPassword() {
        $sql = 'SELECT * FROM settings';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = null;

        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = $row['adminpassword'];
        }

        $stmt = null; 
        $conn = null;
        return $result;
    }

    // returns round number.
    public function getRoundNumber() {
        $sql = 'SELECT * FROM settings';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = null;

        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = $row['roundnumber'];
        }

        $stmt = null; 
        $conn = null;
        return $result;
    }

    /* returns the status (in the form of 0 or 1) whether bidding is allowed.
        If 0, bidding is not allowed.
        If 1, bidding is allowed.
    */
    public function getBiddingAllowed() {
        $sql = 'SELECT * FROM settings';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = null;

        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = $row['biddingallowed'];
        }

        $stmt = null; 
        $conn = null;
        return $result;
    }

    // setter for round number.
    public function setRoundNumber($round_num) {
        $sql = 'UPDATE settings set roundnumber=:roundnumber';
                
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':roundnumber', $round_num, PDO::PARAM_INT);  
        $stmt->execute();
        $stmt = null; 
        $conn = null;
    }

    /*  Setter for whether bidding is allowed.
        If 0, bidding is not allowed.
        If 1, bidding is allowed.
    */
    public function setBiddingAllowed($num) {
        $sql = 'UPDATE settings set biddingallowed=:biddingallowed';
                
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':biddingallowed', $num, PDO::PARAM_INT);  
        $stmt->execute();
        $stmt = null; 
        $conn = null;

    }

}