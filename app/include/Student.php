<?php

    class Student {
        // property declaration
        private $userid;
        private $password;
        private $name;    
        private $school;
        private $edollar;
        
        // constructor
        public function __construct($userid, $password, $name, $school, $edollar) {
            $this->userid = $userid;
            $this->password = $password;
            $this->name = $name;
            $this->school = $school;
            $this->edollar = $edollar;
        }

        // getters for each property of a Student object.
        public function getUserID() {
            return $this->userid;
        }
        public function getPassword() {
            return $this->password;
        }
        public function getName() {
            return $this->name;
        }
        public function getSchool() {
            return $this->school;
        }
        public function getEdollar() {
            return $this->edollar;
        }

        // calculates for the e-dollar balance for a Student after adding/subtracting a specific amount.
        public function addEdollar($amount) {
            return $this->edollar + $amount;
        }

        public function minusEdollar($amount) {
            return $this->edollar - $amount;
        }
        
        // check if the userid and password entered in the login page matches the student's userid and actual password)
        public function authenticate($enteredPwd) {
            return ($enteredPwd == $this->password);
        }

    }

?>