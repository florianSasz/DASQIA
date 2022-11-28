<?php
/**
 * processess data coming from change_password.php; changes password if user verified the current password before
 * not the processing file when creating a new password by getting a reset e-mail -> that is: create_new_password_processing.php
 */
require_once "./database.php";
require_once "./redirect_user.php";
require_once "./string_utilities.php";

function setNewPassword() {
    if ($_POST["newPassword"] !== $_POST["newPasswordRepeat"]) {
        $_SESSION["passwordError"] = "Entered passwords are not the same.";
        RedirectUser::returnTo("../public/change_password.php");
    }

    if (!checkMinPasswordLength($_POST["newPassword"])) {
        $_SESSION["passwordError"] = "Entered password is to short.<br>At least 7 characters are required.";
        RedirectUser::returnTo("../public/change_password.php");
    }

    $database = new DatabaseAccess();
    $database->updatePassword($_SESSION["user"]["id"], $_POST["newPassword"]);
    $_SESSION["user"] = $database->getUser($_SESSION["user"]["email"]);
}

function processData() {
    if (!isset($_SESSION["passwordVerfied"]) || !$_SESSION["passwordVerfied"]) {
        RedirectUser::returnTo("../public/change_password.php");
    }
    setNewPassword();
}

session_start();
if (isset($_POST["navigation"]) && $_POST["navigation"] === "set new password") {
    processData();
}
unset($_SESSION["passwordVerfied"]);
unset($_SESSION["passwordError"]);
RedirectUser::returnToHomePage();
?>