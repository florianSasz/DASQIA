<?php
/**
 * general functions used in different processing steps
 */

function stringIsValid(string $string) {
    // '%%' is not allowed, as it is needed to seperate data 
    if (strpos($string, "%%") !== false) {
        return false;
    }
    return true;
}

function returnWithErrorMessage(string $filePath, string $errorMessage, array $additionalContext=null) {
    $_SESSION["error"] = $errorMessage;
    if ($additionalContext) {
        $_SESSION["errorInformation"] = $additionalContext; 
    }
    header("Location: " . $filePath);
    exit();
}
?>