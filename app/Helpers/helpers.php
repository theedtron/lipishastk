<?php

if(!function_exists('transactionNumberGenerator')) {

    /**
     * @return string
     */
    function transactionNumberGenerator(){
        $string = "AAD7RYZY9B";

        $last_char=substr($string,-1);
        $rest=substr($string, 0, -1);
        switch ($last_char) {
            case '':
                $next= 'A';
                break;
            case 'Z':
                $next = '0';
                $unique = ++$rest;
                $rest = $unique;
                break;
            case '9':
                $next= 'A';
                break;
            default:
                $next = ++$last_char;
                break;
        }
        $string=$rest.$next;
        return $string;
    }
}
