<?php
/**
 * processess data coming from edit_alias.php
 */
require_once "./redirect_user.php";
require_once "./processing.php";
require_once "./database.php";

function sortReceivedData() {
    $data = array("newAliases" => array(), "aliasesToRemove" => array());
    $error = false;
    foreach (array_keys($_POST) as $key) {

        if ($_POST[$key] === "navigation") {
            continue;
        }
        if (!stringIsValid($_POST[$key])) {
            $error = true;
        }

        $keyPrefix = explode("_", $key)[0];
        switch ($keyPrefix) {
            case "newAlias":
                $data["newAliases"][] = $_POST[$key];
                break;
            case "aliasToRemove":
                $data["aliasesToRemove"][] = $_POST[$key];
        }
    }
    if ($error) {
        returnWithErrorMessage("../public/edit_alias.php", "The character combination '%%' is not allowed.", $data);
    }
    $data["newAliases"] = array_unique($data["newAliases"]);
    $data["aliasesToRemove"] = array_unique($data["aliasesToRemove"]);
    return $data;
}

function removeAliasesInDatabase(array $aliasesToRemove, DatabaseAccess $database) {
    $database->removeAliases($_SESSION["user"]["id"], $aliasesToRemove, "user");
}

function addNewAliasesToDatabase(array $newAliases, DatabaseAccess $database) {
    $database->addAliases($_SESSION["user"]["id"], $newAliases, "user");
}
 
function processData() {
    $data = sortReceivedData();
    $database = new DatabaseAccess();
    if ($data["aliasesToRemove"]) {
        removeAliasesInDatabase($data["aliasesToRemove"], $database);
    }
    if ($data["newAliases"]) {
        addNewAliasesToDatabase($data["newAliases"], $database);
    }
    $_SESSION["user"]["aliases"] = $database->getAliases($_SESSION["user"]["id"], "user");
}   

session_start();

if (isset($_POST["navigation"]) && $_POST["navigation"] === "save") {
    processData();
}
RedirectUser::returnToHomePage();
?>