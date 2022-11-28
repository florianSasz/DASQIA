<?php
/**
 * processes data coming from leave_project.php
 */
require_once "./database.php";
require_once "./redirect_user.php";

if (isset($_POST["navigation"]) && $_POST["navigation"] === "leave") {
    session_start();
    if (isset($_SESSION["projectID"]) && is_numeric($_SESSION["projectID"])) {
        if (isset($_SESSION["user"]["email"]) && isset($_SESSION["user"]["id"])) {   
            $database = new DatabaseAccess(true);
            $database->removeUserFromProject($_SESSION["user"]["id"], $_SESSION["projectID"], $_SESSION["user"]["email"]);
        }
    }
}
RedirectUser::returnToHomePage();
?>