<?php
/**
 * processess data coming from codes_processing.php; add/updates/deletes codes in a project
 */

require_once "./database.php";
require_once "./processing.php";
require_once "./redirect_user.php";

function sortReceivedData() {
    $receivedData = array();
    foreach (array_keys($_POST) as $key) {

        if ($key == "navigation") {
            continue;
        }

        if (!stringIsValid($_POST[$key])) {
            returnWithErrorMessage("../public/codes.php", "The character combination '%%' is not allowed. Found in:", array($_POST[$key]));
        }

        $keyType = explode("_", $key);
        switch ($keyType[0]) {
            case "newCode";
                    $receivedData["codes"][] = $_POST[$key];
                break;
            case "documentName":
                    $receivedData["documents"][$keyType[1]]["title"] = $_POST[$key];
                break;
            case "frequency":
                    $receivedData["documents"][$keyType[1]]["frequency"] = $_POST[$key];
                break;
            case "remove":
                $receivedData["remove"][] = $_POST[$key];
        }
    }
    return $receivedData;
}

function structureReceivedCodes(array $receivedData) {

    function sortArrayBySubArrayLength(array $arrayToSort) {
        // https://stackoverflow.com/questions/9455537/sort-a-multi-dimensional-array-by-the-size-of-its-sub-arrays
        function cmp($a, $b){
            return (count($a["codeName"]) - count($b["codeName"]));
        }
        usort($arrayToSort, 'cmp');
        return $arrayToSort;
    }

    function getParentID(string $parentName, array $searchArray, array &$missingParents) {
        foreach ($searchArray as $element) {
            if ($element["name"] == $parentName) {
                return $element["id"];
            }
        }
        (in_array($parentName, $missingParents)) ? null : $missingParents[] = $parentName;
    }
    // restructre codes
    $codes = array();
    for ($i = 0; $i < count($receivedData["codes"]); $i++) {
        // separates code name by backslash and saves the code/codeParents as an array 
        $codes[$i]["codeName"] = explode("\\", $receivedData["codes"][$i]);
        
        // adds the code frequency for a document to a code
        foreach ($receivedData["documents"] as $document) {
            if ($document["frequency"][$i] > 0) {
                $codes[$i]["frequency"][] = array("title" => $document["title"], "frequency" => $document["frequency"][$i], "databaseID" => $document["databaseID"]);
            }
        }   
    }
    $codes = sortArrayBySubArrayLength($codes); // sorts codes in way that a parent will always appear before its children

    $missingParents = array(); // return with error, if there are missing codes 

    // structure codes into {id=> , name=> , parentID=> , frequency=>[document title, frequency, databaseID]} 
    $structuredCodes = array();
    for ($i = 0; $i < count($codes); $i++) {
        if (count($codes[$i]["codeName"]) == 1) { // absolute parent
            $structuredCodes[] = array(
                "id"=>$i, 
                "name"=>$codes[$i]["codeName"][0], 
                "parentID"=>null, 
                "frequency"=>(isset($codes[$i]["frequency"])) ? $codes[$i]["frequency"] : array()
            );
        } else { // is a child Code
            $parentID = getParentID($codes[$i]["codeName"][count($codes[$i]["codeName"]) - 2], $structuredCodes, $missingParents);
            $structuredCodes[] = array(
                "id"=>$i, 
                "name"=>$codes[$i]["codeName"][count($codes[$i]["codeName"]) - 1], 
                "parentID"=>$parentID, 
                "frequency"=>(isset($codes[$i]["frequency"])) ? $codes[$i]["frequency"] : array()
            );
        }
    }    
    
    if ($missingParents) {
        returnWithErrorMessage("../public/codes.php", "The following parent codes are missing. Please make sure that you include all parental related codes.", $missingParents);
    }

    return $structuredCodes;
}

function getDatabaseCodes(DatabaseAccess $database) {
    $databaseCodes = $database->getCodesWithParent($_SESSION["projectID"], false);

    foreach ($databaseCodes as $i=>$code) {
        $databaseCodes[$i]["frequency"] = $database->getCodeDocumentRelation($code["id"]);
    }
    return $databaseCodes; 
}

function compareReceivedDataWithDatabase(array $receivedData, DatabaseAccess $database) {
    $databaseData = getDatabaseCodes($database);

    $newCodes = array();    
    $changedCodes = array();
    
    foreach ($receivedData as $key=>$receivedCode) {
        $found = false;
        foreach ($databaseData as $databaseCode) {
            if ($databaseCode["name"] == $receivedCode["name"]) {
                $found = true;
                // check if both are equal
                if ($change = isUnequal($receivedCode, $databaseCode, $receivedData, $databaseData)) {
                    $changedCodes[count($changedCodes)]["databaseID"] = $databaseCode["id"];
                    $changedCodes[count($changedCodes) - 1]["change"] = $change;
                }
                $receivedData[$key]["databaseID"] = $databaseCode["id"];
                break;
            }
        }
        // new codes    
        if (!$found) {
            $newCodes[] = $receivedCode;
        }
    }
    
    addCodesToDatabase($newCodes, $database, $receivedData);
    updateCodesInDatabase($changedCodes, $receivedData, $database);
}

function getParentByLocalID(int $parentID, array $searchArray) {
    foreach ($searchArray as $element) {
        if ($element["id"] == $parentID) {
            return $element;
        }
    }
}

function getParentByName(string $codeName, array $searchArray) {
    foreach ($searchArray as $element) {
        if ($element["name"] == $codeName) {
            return $element;  
        }
    }
}

function differntParent(array $code1, array $code2, array $code1Collection, array $code2Collection) {
    $result = array(); // using an array because the return should be true for either int or null -> filled array is alaways true
    if (is_null($code1["parentID"])) {

        if (is_null($code2["parentID"])) {
            return $result; // both null -> no change
        }
        $result[] = null; // new parent was set to null, old was int
        return $result; 
    }

    $receivedParent = getParentByLocalID($code1["parentID"], $code1Collection)["name"];
    if (is_null($code2["parentID"])) { // new parent was set to int, old was null
        $result[] = $receivedParent;
        return $result;
    }
    if ($receivedParent != getParentByLocalID($code2["parentID"], $code2Collection)["name"]) { // both are int but different values 
        $result[] = $receivedParent;
        return $result;
    }
    return $result; // both are the same
}

function haveEqualDocuments(array $code1, array $code2) { // $code1 = received, $code2 = database   
    $newDocuments = array();
    $changedDocuments = array();
    $foundDocuments = array();

    foreach ($code1["frequency"] as $document1) {
        $found = false;
        foreach ($code2["frequency"] as $document2) {
            // same document found
            if ($document1["title"] == $document2["title"]) {
                $found = true;
                $foundDocuments[] = $document1["title"];
                // check if documents are eqaul
                if ($document1["frequency"] != $document2["frequency"]) {
                    $changedDocuments[] = $document1;
                }
                break;
            }
        }
        if (!$found) {
            $newDocuments[] = $document1;
        }
    }

    $deletedDocuments = array();
    foreach ($code2["frequency"] as $document2) {
        $found = false;
        foreach ($foundDocuments as $foundDocument) {
            if ($document2["title"] == $foundDocument) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $deletedDocuments[] = $document2;
        }
    }

    $update = array();
    if ($newDocuments) {
        $update["new"] = $newDocuments;
    }
    if ($changedDocuments) {
        $update["change"] = $changedDocuments;
    }
    if ($deletedDocuments) {
        $update["delete"] = $deletedDocuments;
    }
    return $update;
}

function isUnequal(array $code1, array $code2, array $code1Collection, array $code2Collection) { // $code1 = received, $code2 = database
    $change = array();
    if ($newParent = differntParent($code1, $code2, $code1Collection, $code2Collection)) {
        $change["parent"] = $newParent[0];
    }   
    if ($documents = haveEqualDocuments($code1, $code2)) {
        $change["documents"] = $documents;
    }
    return $change;
}

function prepareDocumentData(array $receivedDocuments) {
    for ($i = 0; $i < count($receivedDocuments); $i++) { 
        // removes the doc type information that is included in the document title of MaxQDA
        $receivedDocuments[$i]["title"] = substr($receivedDocuments[$i]["title"], strpos($receivedDocuments[$i]["title"], "\\") + 1, strlen($receivedDocuments[$i]["title"])); 
        
        // transform string with frequencies to array with frequencies
        $receivedDocuments[$i]["frequency"] = explode(",", $receivedDocuments[$i]["frequency"]);
    }
    return $receivedDocuments;
}

function getTotalCodeFrequencyInDocuments(array $receivedDocuments) {
    $result = [];
    foreach ($receivedDocuments as $document) { 
        $totalNumberCodes = 0;
        foreach ($document["frequency"] as $number) {
            $totalNumberCodes += $number;
        }
        $result[$document["title"]] = $totalNumberCodes;
    }
    return $result;
}

function LookForMissingDocuments(array $receivedData, array $databaseDocuments) {
    $databaseTitles = array_column($databaseDocuments, "title");
    $receivedTitles = array_column($receivedData["documents"], "title");

    if ($diff = array_diff($receivedTitles, $databaseTitles)) {
        returnWithErrorMessage("../public/codes.php", "The following documents are missing. 
         Please add them to your project before adding new codes.", $diff);
    }
}

function addDatabaseIDtoDocuments(array $receivedDocuments, array $databaseDocuments) {
    for ($i = 0; $i < count($receivedDocuments); $i++) {
        for ($j = 0; $j < count($databaseDocuments); $j++) {
            if ($receivedDocuments[$i]["title"] == $databaseDocuments[$j]["title"]) {
                $receivedDocuments[$i]["databaseID"] = $databaseDocuments[$j]["id"];
                break;
            }
        }   
    }
    return $receivedDocuments;
}

function getCollectionIndex(int $localID, array $searchArray) { // better: set code id to array key for direct access 
    foreach ($searchArray as $i=>$code) {
        if ($code["id"] == $localID) {
            return $i;
        }
    }
}

function addCodesToDatabase(array $newCodes, DatabaseAccess $database, array $codeCollection) {
    for ($i = 0; $i < count($newCodes); $i++) {
        if (is_null($newCodes[$i]["parentID"])) {
            $parentID = null;
        } else {
            $parentID = getParentByLocalID($newCodes[$i]["parentID"], $codeCollection)["databaseID"];
        }
        $newDatabaseID = $database->addCode($_SESSION["projectID"], $newCodes[$i]["name"], $newCodes[$i]["frequency"], $parentID);
        $codeCollection[getCollectionIndex($newCodes[$i]["id"], $codeCollection)]["databaseID"] = $newDatabaseID;
    }
}

function updateCodesInDatabase(array $changedCodes, array $codeCollection, DatabaseAccess $database) { // hässlich
    $databaseCodes = $database->getCodesWithParent($_SESSION["projectID"], false);
    foreach ($changedCodes as $code) {

        foreach(array_keys($code["change"]) as $changeKey) {

            switch ($changeKey) {
                case "parent":
                    $database->updateCodeParent($code["databaseID"], getParentByName($code["change"]["parent"], $codeCollection)["databaseID"]);
                    break;
                case "documents":

                    foreach (array_keys($code["change"]["documents"]) as $documentKey) {

                        switch ($documentKey) {
                            case "new":
                                foreach ($code["change"]["documents"]["new"] as $newDocument) {
                                    $database->addCodeToDocumentRelation($code["databaseID"], $newDocument["databaseID"], $newDocument["frequency"]);
                                }
                                break;
                            case "change":
                                foreach ($code["change"]["documents"]["change"] as $changedDocument) {
                                    $database->updateCodeToDocumentRelation($code["databaseID"], $changedDocument["databaseID"], $changedDocument["frequency"]);
                                }
                                break;
                            case "delete":
                                foreach ($code["change"]["documents"]["delete"] as $deletedDocument) {
                                    $database->removeCodeToDocumentRelation($code["databaseID"], $deletedDocument["databaseID"]);
                                }
                        }
                    }
            }
        }
    }
}

function deleteCodesFromDatabase(array $deletedCodes, DatabaseAccess $database) {
    foreach ($deletedCodes as $code) {
        $database->deleteCode($_SESSION["projectID"], $code);
    }
}

function compareReceivedCodeFrequenciesWithDatabase(array $receivedTotalCodeFrequencies, array $databaseDocuments, DatabaseAccess $database) {    
    // find code frequencies that changed
    foreach (array_keys($receivedTotalCodeFrequencies) as $receivedDocument) {
        foreach ($databaseDocuments as $databaseDocument) {
            if ($receivedDocument == $databaseDocument["title"]) {
                // update if there is a change
                if ($receivedTotalCodeFrequencies[$receivedDocument] !== $databaseDocument["codes"]) {
                    $database->updateDocument($_SESSION["projectID"], $databaseDocument["id"], array("codes"=>$receivedTotalCodeFrequencies[$receivedDocument]), $databaseDocument["title"]);
                }
                break;
            }
        }
    }
}

function processData() {
    $database = new DatabaseAccess(true);
    $receivedData = sortReceivedData();    
    if (array_key_exists("remove", $receivedData)) {
        deleteCodesFromDatabase($receivedData["remove"], $database);
    }
    unset($receivedData["remove"]);
    
    if (array_key_exists("codes", $receivedData)) { // TODO: hässlich
        $receivedData["documents"] = prepareDocumentData($receivedData["documents"]);    
        $totalCodeFrequency = getTotalCodeFrequencyInDocuments($receivedData["documents"]);
        pretty_print($receivedData);
        pretty_print($totalCodeFrequency);
        $databaseDocuments = $database->getDocumentsForCodesProcessing($_SESSION["projectID"]);
        LookForMissingDocuments($receivedData, $databaseDocuments);
        $receivedData["documents"] = addDatabaseIDtoDocuments($receivedData["documents"], $databaseDocuments);
        $structuredData = structureReceivedCodes($receivedData);
        compareReceivedDataWithDatabase($structuredData, $database);
        compareReceivedCodeFrequenciesWithDatabase($totalCodeFrequency, $databaseDocuments, $database);
    }
}

session_start();

if (isset($_POST["navigation"]) && $_POST["navigation"] === "save") {
    processData();
}
RedirectUser::returnToProjectPage();
?>