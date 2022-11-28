<?php
/**
 * processes data coming from index.php; login page
 */

require_once "./string_utilities.php";
require_once "./database.php";
require_once "./redirect_user.php";

function failedLogin() {
    $_SESSION["failedLogin"] = true;
    RedirectUser::returnToLoginPage();
}

session_start();    

if (isset($_POST["email"]) && empty(trim($_POST["email"]))) {
    failedLogin();
}

if(isset($_POST["password"]) && empty(trim($_POST["password"]))){
    failedLogin();
}

$database = new DatabaseAccess();
if ($user = $database->getUser($_POST["email"])) { 
    if (password_verify($_POST["password"], $user["password"])) {
        $_SESSION["user"] = $user;
        RedirectUser::returnToHomePage();
    }
}
failedLogin();
?>