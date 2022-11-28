<?php
/**
 * processess data coming from change_name.php; changes name if user verified the current password before
 */
require_once "./string_utilities.php";
require_once "./database.php";
require_once "./redirect_user.php";

function setNewName() {
    if (validName($_POST["newName"])) {
        $database = new DatabaseAccess();
        $database->updateName($_SESSION["user"]["id"], $_POST["newName"]);
        $_SESSION["user"] = $database->getUser($_SESSION["user"]["email"]);
    } else {
        $_SESSION["nameToShort"] = true;
        RedirectUser::returnTo("../public/change_name.php");
    }
}

function processData() {
    if (!isset($_SESSION["passwordVerfied"]) || !$_SESSION["passwordVerfied"]) {
        RedirectUser::returnTo("../public/change_name.php");
    }
    setNewName();
}

session_start();
if (isset($_POST["navigation"]) && $_POST["navigation"] == "change name") {
    processData();
}
unset($_SESSION["passwordVerfied"]);
unset($_SESSION["nameToShort"]);
RedirectUser::returnToHomePage();
?>