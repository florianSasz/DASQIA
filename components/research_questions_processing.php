<?php
/**
 * processes data comming from research_questions.php; will add/update/delete research questions in a project
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
            returnWithErrorMessage("../public/research_questions.php", "The character combination '%%' is not allowed.");
        }

        $keyType = explode("_", $key);
        switch ($keyType[0]) {
            case "id":
                if ($database->rqIDExists($_POST[$key], $_SESSION["projectID"])) {
                    $receivedData[$keyType[1]]["id"] = $_POST[$key];
                } else {
                    returnWithErrorMessage("../public/research_questions.php", "something went wrong :/");
                }
                break;
            case "question":
                $receivedData[$keyType[1]]["question"] = $_POST[$key];
                break;
            case "description":
                $receivedData[$keyType[1]]["description"] = $_POST[$key];
                break;
        }
    }
    return $receivedData;
}

function compareReceivedDataWithDatabase(array $receivedData, DatabaseAccess $database) {
    $databaseData = $database->getResearchQuestions($_SESSION["projectID"]);
    
    $newResearchQuestions = array();
    $changedResearchQuestions = array();
    $foundDatabaseRQ = array();

    foreach ($receivedData as $receivedRQ) {
        // new research questions
        if (!array_key_exists("id", $receivedRQ)) {
            $newResearchQuestions[] = $receivedRQ;
            continue;
        }
        
        foreach ($databaseData as $databaseRQ) {
            // changed research questions
            if ($receivedRQ["id"] == $databaseRQ["id"]) {
                if (isUnequal($receivedRQ, $databaseRQ)) {
                    $changedResearchQuestions[] = $receivedRQ;
                }
                $foundDatabaseRQ[] = $receivedRQ["id"];
            }
        }
    }
    
    $deletedResearchQuestions = array();
    // deleted reseach questions
    foreach ($databaseData as $databaseRQ) {
        $found = false;
        foreach ($foundDatabaseRQ as $rqID) {
            if ($databaseRQ["id"] == $rqID) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $deletedResearchQuestions[] = $databaseRQ;
        }
    }

    addResearchQuestionsToDatabase($newResearchQuestions, $database);
    deleteResearchQuestionsFromDatabase($deletedResearchQuestions, $database);
    changeResearchQuestionsinDatabase($changedResearchQuestions, $database);
}

function isUnequal(array $RQ1, array $RQ2) {
    if (strcmp($RQ1["question"], $RQ2["question"]) !== 0 || 
        strcmp($RQ1["description"], $RQ2["description"]) !== 0) {
        return true;
    }
    return false;
}

function addResearchQuestionsToDatabase(array $newResearchQuestions, DatabaseAccess $database) {
    foreach ($newResearchQuestions as $rq) {
        $database->addNewResearchQuestion($_SESSION["projectID"], $rq["question"], $rq["description"]);
    }
}

function deleteResearchQuestionsFromDatabase(array $deletedResearchQuestions, DatabaseAccess $database) {
    foreach ($deletedResearchQuestions as $rq) {
        $database->deleteResearchQuestion($_SESSION["projectID"], $rq["id"], $rq["question"]);
    }
}

function changeResearchQuestionsinDatabase(array $changedResearchQuestions, DatabaseAccess $database) {
    foreach ($changedResearchQuestions as $rq) {
        $database->updateResearchQuestion($_SESSION["projectID"], $rq["id"], $rq["question"], $rq["description"]);
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