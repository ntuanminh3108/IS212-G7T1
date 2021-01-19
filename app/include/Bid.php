<?php

    class Bid {
        // property declaration
        private $userid;
        private $amount;
        private $code;
        private $section;
        private $status;
        private $round;
        
        // constructor
        public function __construct($userid, $amount, $code, $section,$status, $round) {
            $this->userid = $userid;
            $this->amount = $amount;
            $this->code = $code;
            $this->section = $section;
            $this->status = $status;
            $this->round = $round;
        }
        
        // getters for each property of the bid object.
        public function getAmount() {
            return $this->amount;
        }

        public function getUserid() {
            return $this->userid;
        }

        public function getSection() {
            return $this->section;
        }
        public function getCourse() {
            return $this->code;
        }

        public function getStatus() {
            return $this->status;
        }

        public function getRound() {
            return $this->round;
        }

        // setter for the status property of the bid object
        public function setStatus($status) {
            $this->status = $status;
        }
       
    }

?>