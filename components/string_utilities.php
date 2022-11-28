<?php
/** 
 * different functions to process or validate strings
 */

function isEmail(string $email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function checkMinPasswordLength(string $password) {
    $minPasswordLength = 7;
    return mb_strlen($password) >= $minPasswordLength;
}

function validName(string $name) {
    return !empty(trim($name));
}

function convertDateFormat(string $date) { // yyyy-mm-dd -> dd.mm.yyyy
    $date = explode("-", $date);
    return $date[2] . "." . $date[1] . "." . $date[0];
}

function isHexColor(string $hexColor) {
    if (strlen($hexColor) !== 7) {
        return false;
    }
    if ($hexColor[0] !== "#") {
        return false;
    }
    $hexNumbers = ltrim($hexColor, "#");
    if (!ctype_xdigit($hexNumbers)) {
        return false;
    }
    return true;
}
?>