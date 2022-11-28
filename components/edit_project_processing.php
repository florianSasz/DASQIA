<?php
/**
 * processes data coming from edit_project.php; updates a project if there are changes
 */
require_once "./database.php";
require_once "./string_utilities.php";
require_once "./processing.php";
require_once "./redirect_user.php";
require_once "./project_settings_processing.php";

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
        "removeMembers" => array(
            "shadow" => array(),
            "registered" => array()
        ),
        "editShadowAlias" => array()
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

                if (str_starts_with($key, "remove")) {
                    $type = explode("_", $key)[1];
                    
                    switch ($type) {
                        case "shadow":
                            $receivedData["removeMembers"]["shadow"][] = $_POST[$key];
                            break;
                        case "registered":
                            $receivedData["removeMembers"]["registered"][] = $_POST[$key];
                            break;
                    }
                }

                if (str_starts_with($key, "edit")) {
                    $aliases = explode("%%", $_POST[$key]);
                    $name = array_shift($aliases);  
                    $receivedData["editShadowAlias"][] = array("name" => $name, "aliases" => $aliases); 
                }
        }
    }

    return $receivedData;
}

class Compare {

    public static function title(string $title1, string $title2) {
        return $title1 === $title2;
    }
    
    public static function description(string $description1, string $description2) {
        return $description1 === $description2;
    }
    
    public static function status($status1, $status2) {
        return filter_var($status1, FILTER_VALIDATE_BOOLEAN) === filter_var($status2, FILTER_VALIDATE_BOOLEAN);
    }
    
    public static function leader(string $leader1, string $leader2) {
        return $leader1 === $leader2;
    }

    public static function memberChange(array $receivedData){ 
        if ($receivedData["newMembers"]["registered"]) {
            return true;
        }
        if ($receivedData["newMembers"]["shadow"]) {
            return true;
        }
        if ($receivedData["removeMembers"]["registered"]) {
            return true;
        }
        if ($receivedData["removeMembers"]["shadow"]) {
            return true;
        }
    }

    public static function aliasChange(array $receivedAliasChanges, array $databaseShadowMembers, DatabaseAccess $database) {
        if ($receivedAliasChanges) {
            $shadowUserWithAliasChange = array();

            foreach ($databaseShadowMembers as $i=>$shadowMember) {
                // get aliases in database
                $databaseShadowMembers[$i]["aliases"] = $database->getAliases($shadowMember["id"], "shadowuser");
                // set shadow names as array keys for easier access to shadow user
                $databaseShadowMembers[$shadowMember["name"]] = $databaseShadowMembers[$i];
                unset($databaseShadowMembers[$i]);
            }

            foreach ($receivedAliasChanges as $shadowMember) {
                $removedAliases = array_diff($databaseShadowMembers[$shadowMember["name"]]["aliases"], $shadowMember["aliases"]);
                $addedAliases = array_diff($shadowMember["aliases"], $databaseShadowMembers[$shadowMember["name"]]["aliases"]);

                $shadowUserWithAliasChange[$shadowMember["name"]] = array("removed" => $removedAliases, "new" => $addedAliases, 
                                                                          "databaseID" => $databaseShadowMembers[$shadowMember["name"]]["id"]);
            }
            return $shadowUserWithAliasChange;
        } 
        return false;
    }

}

function compareReceivedDataWithDatabase(array $receivedData, array $databaseData, DatabaseAccess $database) {
    $change = array();

    if (!Compare::title($receivedData["title"], $databaseData["title"])) {
        $change["title"] = $receivedData["title"];  
    }

    if (!Compare::description($receivedData["description"], $databaseData["description"])) {
        $change["description"] = $receivedData["description"];  
    }

    if (!Compare::leader($receivedData["leader"], $databaseData["leader"]["email"])) {
        $change["leader"] = $receivedData["leader"];  
    }

    if (!Compare::status($receivedData["status"], $databaseData["status"])) {
        $change["finished"] = (filter_var($receivedData["status"], FILTER_VALIDATE_BOOLEAN)) ? 1 : 0;  
    }

    if (Compare::memberChange($receivedData)) {
        $change["newMembers"] = true;
    }

    if ($aliasChanges = Compare::aliasChange($receivedData["editShadowAlias"], $databaseData["shadowMembers"], $database)) {
        $change["shadowuserAliasChange"] = $aliasChanges;
    }

    return $change;
}

function updateDatabase(array $update, array $newMembers, array $removeMembers, DatabaseAccess $database) {
    $currentUserIsLeader = $database->getLeaderStatus($_SESSION["user"]["id"], $_SESSION["projectID"]);
    if ($currentUserIsLeader) {

        if ($removeMembers["registered"]) {
            foreach ($removeMembers["registered"] as $member) {
                $user = $database->getUserShort($member);
                $database->removeUserFromProject($user["id"], $_SESSION["projectID"], $user["email"]);
            }
        }

        if (isset($update["leader"])) {
            $newLeader = $database->getUserShort($update["leader"]);
            if ($database->isUserInProject($newLeader["id"], $_SESSION["projectID"])) {
                $database->setNewProjectLeader($_SESSION["projectID"], $newLeader, $_SESSION["user"]);
            }
        }
    }
    unset($update["leader"]);
    
    if ($removeMembers["shadow"]) {
        foreach ($removeMembers["shadow"] as $member) {
            $database->removeShadowUserFromProject($member, $_SESSION["projectID"]);
        }
    }

    if (isset($update["shadowuserAliasChange"])) {
        foreach($update["shadowuserAliasChange"] as $shadowMember) {
            if ($shadowMember["new"]) {
                $database->addAliases($shadowMember["databaseID"], $shadowMember["new"], "shadowuser");
            }
            if ($shadowMember["removed"]) {
                $database->removeAliases($shadowMember["databaseID"], $shadowMember["removed"], "shadowuser");
            }
        }
        unset($update["shadowuserAliasChange"]);
    }

    if ($update) {
        $database->updateProject($_SESSION["projectID"], $update);
    }
    addNewMembers($newMembers, $database, $_SESSION["projectID"]);
}

function processData() {
    $receivedData = sortReceivedData();
    $database = new DatabaseAccess(true);
    if ($errors = inputsAreInvalid($receivedData, $database)) {
        $_SESSION["previouseAttempt"] = $receivedData;
        returnWithErrorMessage("../public/edit_project.php", "Not all inputs are valid:", $errors);
    }
    $databaseData = $database->getProjectsettings($_SESSION["projectID"]);

    if ($duplicates = duplicateMembersExist($receivedData["newMembers"]["registered"], $receivedData["newMembers"]["shadow"], $databaseData["registeredMembers"], $databaseData["shadowMembers"])) {
        $_SESSION["previouseAttempt"] = $receivedData;
        returnWithErrorMessage("../public/edit_project.php", "Not all members are unique:", $duplicates);
    }

    if ($change = compareReceivedDataWithDatabase($receivedData, $databaseData, $database)) {
        unset($change["newMembers"]);
        updateDatabase($change, $receivedData["newMembers"], $receivedData["removeMembers"], $database);
    }
}

session_start();

if (isset($_POST["navigation"]) && $_POST["navigation"] == "save") {
    processData();
}
RedirectUser::returnToProjectPage();
?>