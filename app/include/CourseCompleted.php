<?php 
    class CourseCompleted{
        // property declaration
        private $userid;
        private $code;

        // constructor
        public function __construct($userid, $code){
            $this->userid = $userid; 
            $this->code = $code;
        }

        // getters for each property in the CourseCompletedObject.
        public function getUserID() {
            return $this->userid;
        }

        public function getCourse() {
            return $this->code;
        }
    }
?>