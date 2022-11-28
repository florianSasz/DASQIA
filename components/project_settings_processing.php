<?php
/**
 * methods to proccess data coming from edit_project_processing.php and new_project_processing.php; methods here are used on both files
 */
require_once "./string_utilities.php";

function inputsAreInvalid(array &$receivedData, DatabaseAccess $database) {
    $errors = array();
    if (!trim($receivedData["title"])) {
        $errors[] = "A title is requiered.";
    }

    foreach ($receivedData["newMembers"]["registered"] as $i=>$newMember) {
        
        if (!isEmail($newMember)) {
            $errors[] = "Email '" . $newMember . "' is not an email format.";

        } else if (!$user = $database->getUserShort($newMember)) {
            $errors[] = "Email '" . $newMember . "' is not registered.";

        } else {
            $receivedData["newMembers"]["registered"][$i] = $user;
        }
    }

    foreach ($receivedData["newMembers"]["shadow"] as $newMember) {
        if (!validName($newMember["name"])) {
            $errors[] = "The shadow member name '" . $newMember["name"] . "' is not valid. At least one character is required.";
        }

        foreach ($newMember["aliases"] as $alias) {
            if (!validName($alias))
            $errors[] = "The alias '" . $alias . "' of '" . $newMember["name"] . "' is not valid. At leadt one character is requiered.";
        }
    }

    if (isset($receivedData["editShadowAlias"])) {
        foreach ($receivedData["editShadowAlias"] as $shadowMember) {
            foreach ($shadowMember["aliases"] as $alias) {
                if (!validName($alias)) {
                    $errors[] = "The alias '" . $alias . "' of '" . $shadowMember["name"] . "' is not valid. At leadt one character is requiered.";
                }
            }
        }
    }

    return $errors;
}

function duplicateMembersExist(array $newRegisteredMembers, array $newShadowMembers, array $existingRegisteredMembers, array $existingShadowMembers=null) {
    $duplicates = array();
    
    // adding an existing member as new member
    $existingRegistered = array_column($existingRegisteredMembers, "email");
    foreach (array_column($newRegisteredMembers,"email") as $newMember) {
        if (in_array($newMember, $existingRegistered)) {
            $duplicates[] = "Registered member '" . $newMember . "' already exists in the project.";
        }
    }

    // adding a new member twice
    $temp = array_column($newRegisteredMembers, "email");
    $uniqueNewRegistered = array_unique($temp);
    if ($diff = array_diff_assoc($temp, $uniqueNewRegistered)) {
        foreach ($diff as $duplicate) {
            $duplicates[] = "New registered member '" . $duplicate . "' already exists as a new member.";
        }
    }

    // adding an existing shadow member as new shadow member
    if ($existingShadowMembers) {
        $existingShadow = array_column($existingShadowMembers, "name");
        foreach ($newShadowMembers as $newMember) {
            if (in_array($newMember, $existingShadow)) {
                $duplicates[] = "Shadow member '" . $newMember . "' already exists in the project.";
            }
        }
    }
    
    // adding a new shadow member twice
    $newShadowMembersNames = array_column($newShadowMembers, "name");
    $uniqueNewShadow = array_unique($newShadowMembersNames);
    if ($diff = array_diff_assoc($newShadowMembersNames, $uniqueNewShadow)) {
        foreach ($diff as $duplicate) {
            $duplicates[] = "New shadow member '" . $duplicate . "' already exists as a new member.";
        }
    }

    return $duplicates;
}

function addNewMembers(array $newMembers, DatabaseAccess $database, int $projectID) {
    if ($newMembers["shadow"]) {
        foreach ($newMembers["shadow"] as $newMember) {
            $database->addNewShadowUser($newMember["name"], $newMember["aliases"], $projectID);
        }
    }

    if ($newMembers["registered"]) {
        foreach ($newMembers["registered"] as $newMember) {
            $database->addUserToProject($newMember["id"], $projectID, $newMember["email"]);
        }
    }
}
?>