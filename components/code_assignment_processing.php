<?php
/**
 * processess data coming from code_assignment.php; add/updates/deletes code assignments in a project
 */
require_once "./database.php";
require_once "./redirect_user.php";

function sortReceivedData(DatabaseAccess $database) {
    $receivedData = [];
    foreach (array_keys($_POST) as $codeID) {
        
        if ($codeID === "navigation") {
            continue;
        }

        if (is_numeric($codeID)) {
            if ($database->codeIdExists($codeID, $_SESSION["projectID"])) {
                $receivedData[] = array("codeID"=>$codeID, "researchQuestionIDs"=>explode(",", $_POST[$codeID])); 
            }
        }
    }
    return $receivedData;
}

function compareReceivedDataWithDatabase(array $receivedData, DatabaseAccess $database) {

    function prepareDatabaseData(array $databaseCodes) {
        $dataToCompare = [];
        foreach ($databaseCodes as $code) {
            if (isset($code["researchQuestions"])) {
                $dataToCompare[] = $code;
            }
        }
        return $dataToCompare;
    }

    $databaseCodes = $database->getCodesWithParent($_SESSION["projectID"], true);
    $dataToCompare = prepareDatabaseData($databaseCodes);
    $assignedCodesToCompare = [];
    $newAssignedCodes = [];
    $codeAssignmentsToRemove = [];

    $foundIndexesInExistingData = [];
    // compare both arrays for the codeID
    for ($i = 0; $i < count($receivedData); $i++) {
        $codeFound = false;
        for ($j = 0; $j < count($dataToCompare); $j++) {
            if ($receivedData[$i]["codeID"] == $dataToCompare[$j]["id"]) {
                $codeFound = true;
                $foundIndexesInExistingData[] = $j;
                // assignments to codes that alreay had assignments->assignments need to be compared  
                $assignedCodesToCompare[] = array("codeID"=>$receivedData[$i]["codeID"], 
                    "received"=>$receivedData[$i]["researchQuestionIDs"], "existing"=>$dataToCompare[$j]["researchQuestions"]);
                break;
            } 
        }   
        // assignments for a before completly unassigned code
        if (!$codeFound) {
            $newAssignedCodes[] = $receivedData[$i]; 
        }
    }

    // codes that need all assignmets to be removed 
    if (count($dataToCompare) > 0) {
        $removeIndices = array_diff(range(0, count($dataToCompare) - 1), $foundIndexesInExistingData);
        if ($removeIndices) {
            foreach ($removeIndices as $index) {
                $codeAssignmentsToRemove[] = array("codeID"=>$dataToCompare[$index]["id"], "researchQuestionIDs"=>$dataToCompare[$index]["researchQuestions"]);
            }
        }
    }

    // compare existings codes assignments
    foreach ($assignedCodesToCompare as $code) {
        ($remove = array_diff($code["existing"], $code["received"])) ? $codeAssignmentsToRemove[] = array("codeID"=>$code["codeID"], "researchQuestionIDs"=>$remove) : null; 
        ($new = array_diff($code["received"], $code["existing"])) ? $newAssignedCodes[] = array("codeID"=>$code["codeID"], "researchQuestionIDs"=>$new) : null;
    }

    // remove code assignments
    foreach ($codeAssignmentsToRemove as $code) {
        removeCodeAssignmentFromDatabase($code["codeID"], $code["researchQuestionIDs"], $database);
    }

    // add new code assignents
    foreach ($newAssignedCodes as $code) {
        addCodeAssignmentToDatabase($code["codeID"], $code["researchQuestionIDs"], $database);
    }
}

function addCodeAssignmentToDatabase(int $codeID, array $rqIDs, DatabaseAccess $database) {
    foreach ($rqIDs as $rqID) {
        $database->addCodeToResearchQuestionRelation($codeID, $rqID);    
    }
}

function removeCodeAssignmentFromDatabase(int $codeID, array $rqIDs, DatabaseAccess $database) {
    foreach ($rqIDs as $rqID) {
        $database->removeCodeToResearchQuestionRelation($codeID, $rqID);    
    }
}

function processData() {
    $database = new DatabaseAccess(true);
    $receivedData = sortReceivedData($database);
    compareReceivedDataWithDatabase($receivedData, $database);
}

session_start();
if (isset($_POST["navigation"]) && $_POST["navigation"] === "save") {
    processData();
}
RedirectUser::returnToProjectPage();
?>