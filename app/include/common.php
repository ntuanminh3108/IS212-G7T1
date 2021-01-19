<?php

    // this will autoload the class that we need in our code
    spl_autoload_register(function($class) {
    
        // we are assuming that it is in the same directory as common.php
        // otherwise we have to do
        // $path = 'path/to/' . $class . ".php"    
        require_once "$class.php"; 
    
    });


    // start a session
    session_start();

    // session-related functions for printing error messages
    function printErrors() {
        if(isset($_SESSION['errors'])){
           // echo "<ul id='errors' style='color:red;'>";
            
            foreach ($_SESSION['errors'] as $value) {
                echo $value;
            }
            
            //echo "</ul>";   
            unset($_SESSION['errors']);
        }    
    }

    function printMessage() {
        if(isset($_SESSION['message'])){
            echo $_SESSION['message'];
            unset($_SESSION['message']);
        }    
    }

    function isMissingOrEmpty($name) {
        if (!isset($_REQUEST[$name])) {
            return "missing $name";
        }

        // client did send the value over
        $value = $_REQUEST[$name];
        if (empty($value)) {
            return "blank $name";
        }
    }

    # check if an int input is an int and non-negative
    function isNonNegativeInt($var) {
        if (is_numeric($var) && $var >= 0 && $var == round($var))
            return TRUE;
    }

    # check if a float input is is numeric and non-negative
    function isNonNegativeFloat($var) {
        if (is_numeric($var) && $var >= 0)
            return TRUE;
    }

    # this is better than empty when use with array, empty($var) returns FALSE even when
    # $var has only empty cells
    function isEmpty($var) {
        if (isset($var) && is_array($var))
            foreach ($var as $key => $value) {
                if (empty($value)) {
                unset($var[$key]);
                }
            }

        if (empty($var))
            return TRUE;
    }

?>