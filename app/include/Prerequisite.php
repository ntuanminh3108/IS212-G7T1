<?php

    class Prerequisite {
        // property declaration
        private $course;
        private $prerequisite;
        
        // constructor
        public function __construct($course, $prerequisite) {
            $this->course = $course;
            $this->prerequisite = $prerequisite;    
        }
        
        // getters for each property of a Prerequisite Object.
        public function getCourse() {
            return $this->course;
        }
        
        public function getPrerequisite() {
            return $this->prerequisite;
        }
    }
?>