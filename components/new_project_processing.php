<?php
/**
 * processes data coming from new_project.php; checks if the inputs are valid and if so will create a new project 
 */
require_once "./database.php";
require_once "./project_settings_processing.php";
require_once "./processing.php";
require_once "./redirect_user.php";

function sortReceivedData() {
    $receivedData = array(
        "title" => null,
        "description" => null,
        "leader" => null,
        "status" => null,
        "newMembers" => array(
            "shadow" => array(),
            "registered" => array()
        ),
    );

    foreach (array_keys($_POST) as $key) {

        switch ($key) {
            case "title":
                $receivedData["title"] = $_POST["title"];
                break;
            case "description":
                $receivedData["description"] = $_POST["description"];        
                break;
            case "leader":
                $receivedData["leader"] = $_POST["leader"];
                break;
            case "status":
                $receivedData["status"] = $_POST["status"];
                break;
            default:

                if (str_starts_with($key, "new")) {
                    $type = explode("_", $key)[1];
                    
                    switch ($type) {
                        case "shadow":
                            $aliases = explode("%%", $_POST[$key]);
                            $name = array_shift($aliases);
                            $receivedData["newMembers"]["shadow"][] = array("name" => $name, "aliases" => $aliases);
                            break;
                        case "registered":
                            $receivedData["newMembers"]["registered"][] = $_POST[$key];
                            break;
                    }
                }
        }
    }
    return $receivedData;
}

function createNewProject(array $receivedData, DatabaseAccess $database) {
    $projectID = $database->createNewProject($receivedData["title"], $receivedData["description"]);
    $database->addUserToProject($_SESSION["user"]["id"], $projectID, $_SESSION["user"]["email"], true);
    addNewMembers($receivedData["newMembers"], $database, $projectID);
}

function processData(){
    $receivedData = sortReceivedData();
    $database = new DatabaseAccess();
    if ($errors = inputsAreInvalid($receivedData, $database)) {
        $_SESSION["previouseAttempt"] = $receivedData;
        returnWithErrorMessage("../public/new_project.php", "Not all inputs are valid:", $errors);
    }

    if ($duplicates = duplicateMembersExist($receivedData["newMembers"]["registered"], $receivedData["newMembers"]["shadow"], array(array("email"=>$_SESSION["user"]["email"])))) { 
    // nested array to form the data into the same structure as it would be in edit_project_processing.php => can use same function 
        $_SESSION["previouseAttempt"] = $receivedData;
        returnWithErrorMessage("../public/new_project.php", "Not all members are unique:", $duplicates);
    }

    unset($database);
    $database = new DatabaseAccess(true);
    createNewProject($receivedData, $database);
}

session_start();

if (isset($_POST["navigation"]) && $_POST["navigation"] == "save") {
    processData();
}
RedirectUser::returnToHomePage();
?>