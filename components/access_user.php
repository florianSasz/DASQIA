<?php
/**
 * class checks if a user is authorization to access certain pages; checks for login status, project selection and project member status
 * is the first thing that is called in a script; will redirect user if requirements are not met
 */
require_once "../components/redirect_user.php";

class UserAccess {

    public static function userIsLoggedIn() {
        if (!isset($_SESSION["user"])) {
            RedirectUser::returnToLoginPage();
        }
    }

    public static function userHasProjectSelected() {
        if (!isset($_SESSION["projectID"])) {
            RedirectUser::returnToHomePage();
        }
    }

    public static function userIsInProject(int $projectID, DatabaseAccess $database) {
        return $database->isUserInProject($_SESSION["user"]["id"], $projectID);
    }
}
?>