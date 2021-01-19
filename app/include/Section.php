<?php

    class Section {
        // property declaration
        private $course;
        private $section;
        private $day;    
        private $start;
        private $end;
        private $instructor;
        private $venue;
        private $size;
        private $minBid;
        private $vacancy;
        
        // constructor
        public function __construct($course, $section, $day, $start, $end, $instructor, $venue, $size,$minBid, $vacancy) {
            $this->course = $course;
            $this->section = $section;
            $this->day = $day;
            $this->start = $start;
            $this->end = $end;
            $this->instructor = $instructor;
            $this->venue = $venue;
            $this->size = $size;
            $this->minBid = $minBid;
            $this->vacancy = $vacancy;
        }

        // getters for each property in the Section object.
        public function getCourse() {
            return $this->course;
        }
        public function getSection() {
            return $this->section;
        }
        public function getDay() {
            return $this->day;
        }
        public function getStart() {
            return $this->start;
        }
        public function getEnd() {
            return $this->end;
        }
        public function getInstructor() {
            return $this->instructor;
        }
        public function getVenue() {
            return $this->venue;
        }
        public function getSize() {
            return $this->size;
        }
        public function getMinBid() {
            return $this->minBid;
        }
        public function getVacancy() {
            return $this->vacancy;
        }
        public function getVacancyDrop() {
            return $this->vacancy - 1;
        }
    }
    
?>