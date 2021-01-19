<?php

    class Course {
        // property declaration
        private $course;
        private $school;
        private $title;
        private $description;
        private $exam_date;
        private $exam_start;
        private $exam_end;

        // constructor
        public function  __construct($course, $school, $title, $description, $exam_date = '', $exam_start = '', $exam_end = '') {
            $this->course = $course;
            $this->school = $school;
            $this->title = $title;
            $this->description = $description;
            $this->exam_date = $exam_date;
            $this->exam_start = $exam_start;
            $this->exam_end = $exam_end;
        }

        // getters for each property of the Course Object.
        public function getCourse() {
            return $this->course;
        }
        
        public function getSchool() {
            return $this->school;
        }

        public function getTitle() {
            return $this->title;
        }

        public function getDescription() {
            return $this->description;
        }

        public function getExamDate() {
            return $this->exam_date;
        }
        
        public function getExamStart() {
            return $this->exam_start;
        }

        public function getExamEnd() {
            return $this->exam_end;
        }


    }
?>