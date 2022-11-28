<?php
/**
 * processess data coming from delete_project.php
 */
require_once "./database.php";
require_once "./redirect_user.php";

if (isset($_POST["navigation"]) && $_POST["navigation"] === "delete") {
    session_start();
    
    if (isset($_SESSION["projectID"]) && is_numeric($_SESSION["projectID"])) {
        $database = new DatabaseAccess(true);
        if ($database->getLeaderStatus($_SESSION["user"]["id"], $_SESSION["projectID"])) { // check if user is project leader
            $database->deleteProject($_SESSION["projectID"]);
        }
    }
} 
RedirectUser::returnToHomePage();
?>