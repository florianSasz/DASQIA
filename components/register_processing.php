<?php
/**
 * processes data from register.php; checks if all the inputs are valid and if so will create a new user
 */

require_once "./string_utilities.php";
require_once "./database.php";
require_once "./redirect_user.php";

function backToRegister($error) {
    $_SESSION["error"] = $error;
    $_SESSION["email"] = $_POST["email"];
    $_SESSION["name"] = $_POST["name"];
    RedirectUser::returnTo("../public/register.php");    
}

function process() {
    if (!isEmail($_POST["email"])) {
        backToRegister("Please enter a valid e-mail.");
    }
    
    if (!validName($_POST["name"])) {
        backToRegister("Please enter a name.");
    }
    
    if ($_POST["password"] !== $_POST["re_password"]) {
        backToRegister("Passwords are not equal.");
    }
    
    if (!checkMinPasswordLength($_POST["password"])) {
        backToRegister("Password is to short. At least 7 characters are required.");
    }
    
    $database = new DatabaseAccess();
    if ($database->getUserShort($_POST["email"])) {
        backToRegister("The e-mail is already registered.");
    }

    $database->addNewUser($_POST["email"], $_POST["name"], $_POST["password"]);
    $_SESSION["registration"] = true;
}

session_start();
if (isset($_POST["navigation"]) && $_POST["navigation"] === "register") {
    process();
}
RedirectUser::returnToLoginPage();
?>