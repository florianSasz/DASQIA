<?php
/**
 * processes data coming from documents.php; will add/update/delete documents in a project
 */

require_once "./database.php";
require_once "./processing.php";
require_once "./redirect_user.php";

function sortReceivedData(DatabaseAccess $database) {
    $receivedData = array();
    foreach (array_keys($_POST) as $key) {
        
        if ($key == "navigation") {
            continue;
        }

        if (!stringIsValid($_POST[$key])) {
            returnWithErrorMessage("../public/documents.php", "The character combination '%%' is not allowed.");
        }

        $keyType = explode("_", $key);
        switch ($keyType[0]) {
            case "dbIndex":
                if (is_numeric($_POST[$key])) {
                    if ($database->documentIDExists($_POST[$key], $_SESSION["projectID"])) {
                        $receivedData[$keyType[1]]["id"] = $_POST[$key];
                    } else {
                        returnWithErrorMessage("../public/documents.php", "something went wrong :/");
                    }
                } else {
                    returnWithErrorMessage("../public/documents.php", "something went wrong :/");
                }
                break;
            case "type":
                $receivedData[$keyType[1]]["type"] = $_POST[$key];
                break;
            case "title":
                $receivedData[$keyType[1]]["title"] = $_POST[$key];
                break;
            case "interviewer":
                $receivedData[$keyType[1]]["interviewer"] = $_POST[$key];
                break;
            case "interviewDate":
                $receivedData[$keyType[1]]["interview_date"] = $_POST[$key];
                break;
            case "evaluator":
                $receivedData[$keyType[1]]["evaluator"] = $_POST[$key];
                break;
            case "evaluationDate":
                $receivedData[$keyType[1]]["evaluation_date"] = $_POST[$key];
                break;
            case "originalInterviewer":
                $receivedData[$keyType[1]]["originalInterviewer"] = $_POST[$key];
                break;
            case "originalEvaluator":
                $receivedData[$keyType[1]]["originalEvaluator"] = $_POST[$key];
                break;
            case "newName":
                if ($keyType[1] === "interviewer") {
                    $receivedData[$keyType[2]]["interviewer"] = $_POST[$key];
                } else if ($keyType[1] === "evaluator") {
                    $receivedData[$keyType[2]]["evaluator"] = $_POST[$key];
                }
            /*case "numberCodes":
                $receivedData[$keyType[1]]["codes"] = $_POST[$key];
                break;*/
        }
    }
    return $receivedData;
}

function compareReceivedDataWithDatabase(array $receivedData, DatabaseAccess $database) {
    $databaseData = $database->getDocuments($_SESSION["projectID"]);

    $newDocuments = array();
    $changedDocuments = array();
    $foundDatabaseDocuments = array();

    foreach ($receivedData as $receivedDocument) {
        // new documents
        if (!array_key_exists("id", $receivedDocument)) {
            $newDocuments[] = $receivedDocument;
            continue;
        }

        foreach ($databaseData as $databaseDocument) {
            // changed documents
            if ($receivedDocument["id"] == $databaseDocument["id"]) {
                if ($change = isUnequal($receivedDocument, $databaseDocument)) {
                    $changedDocuments[count($changedDocuments)] = $receivedDocument;
                    $changedDocuments[count($changedDocuments) - 1]["changedKeys"] = $change;
                }   
                $foundDatabaseDocuments[] = $receivedDocument["id"];
            }
        }
    }

    $deletedDocuments = array();
    // deleted reseach questions
    foreach ($databaseData as $databaseDocument) {
        $found = false;
        foreach ($foundDatabaseDocuments as $documentID) {
            if ($databaseDocument["id"] == $documentID) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $deletedDocuments[] = $databaseDocument;
        }
    }

    addDocumentsToDatabase($newDocuments, $database);
    deleteDocumentsFromDatabase($deletedDocuments, $database);
    changeDocumentsinDatabase($changedDocuments, $database);
}

// always use $document1 as the receivedData, because databaseData has additional keys
function isUnequal(array $document1, array $document2) {
    $changedKeys = array();
    foreach (array_keys($document1) as $key) {
        if ($key == "id") {
            continue;
        }
        if (strcmp($document1[$key], $document2[$key]) !== 0) {
            $changedKeys[] = $key;
        }
    }
    return $changedKeys;
}

function addDocumentsToDatabase(array $newDocuments, DatabaseAccess $database) {
    foreach ($newDocuments as $document) {
        $database->addDocument($document["type"], $document["title"], $document["interviewer"], $document["originalInterviewer"],
         $document["interview_date"], $document["evaluator"], $document["originalEvaluator"], $document["evaluation_date"], 
         0, $_SESSION["projectID"]);
    }
}

function deleteDocumentsFromDatabase(array $deletedDocuments, DatabaseAccess $database) {
    foreach ($deletedDocuments as $document) {
        $database->deleteDocument($_SESSION["projectID"], $document["id"], $document["title"]);
    }
}

function changeDocumentsinDatabase(array $changedDocuments, DatabaseAccess $database) {
    foreach ($changedDocuments as $document) {
        $elementsToUpdate = array();
        foreach ($document["changedKeys"] as $changedKeys) {
            $elementsToUpdate[$changedKeys] = $document[$changedKeys];
        }
        $database->updateDocument($_SESSION["projectID"], $document["id"], $elementsToUpdate, $document["title"]);
    }
}

function processData() {
    $database = new DatabaseAccess(true);
    $sortedData = sortReceivedData($database);
    compareReceivedDataWithDatabase($sortedData, $database);
}

session_start();

if (isset($_POST["navigation"]) && $_POST["navigation"] === "save") {
    processData();
}
RedirectUser::returnToProjectPage();
?>