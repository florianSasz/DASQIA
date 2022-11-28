<?php
/**
 * base class to check if a user is authorization to access certain pages; contains methods to retrieve the information that is needed to make a decision;
 * most methods work with a DatabaseAccess Objects or ProjectData Objects, usage depends on wheter a ProjectData Object already exists when calling a method
 */
class EvaluateAccess {
    /**
     * minmum requierments to access certain project sections:
     * - edit research questions: not finished
     * - edit documents: not finished
     * - edit codes: not finished, documents exist
     * - analysis data available: documents exist, codes exist
     * - assign codes: not finished, codes exist, research questions exist
     * - export assignment: codes exist, research questions exist
     */
    protected static function projectIsFinished($dataObject) {
        switch (get_class($dataObject)) {
            case "DatabaseAccess":
                return $dataObject->getProjectStatus($_SESSION["projectID"]);
                break;
            case "projectData":
                return $dataObject->finished;
                break;
            default:
                throw new Exception("dataObject class must be of type 'DatabaseAccess' or 'projectData'");
        }
    }

    protected static function userIsLeader(DatabaseAccess $database) {
        return $_SESSION["user"]["id"] === $database->getProjectLeader($_SESSION["projectID"])["id"];
    }

    protected static function documentsExist($dataObject) {
        switch (get_class($dataObject)) {
            case "DatabaseAccess":
                return count($dataObject->getDocuments($_SESSION["projectID"])) > 0;
                break;
            case "projectData":
                return $dataObject->documents;
                break;
            default:
                throw new Exception("dataObject class must be of type 'DatabaseAccess' or 'projectData'");
        }
    }

    protected static function researchQuestionsExist($dataObject) {
        switch (get_class($dataObject)) {
            case "DatabaseAccess":
                return count($dataObject->getResearchQuestions($_SESSION["projectID"])) > 0;
                break;
            case "projectData":
                return $dataObject->researchQuestions;
                break;
            default:
                throw new Exception("dataObject class must be of type 'DatabaseAccess' or 'projectData'");
        }
    }

    protected static function codesExist($dataObject) {
        switch (get_class($dataObject)) {
            case "DatabaseAccess":
                return count($dataObject->getCodesWithParent($_SESSION["projectID"], false)) > 0;
                break;
            case "projectData":
                return $dataObject->codes;
                break;
            default:
                throw new Exception("dataObject class must be of type 'DatabaseAccess' or 'projectData'");
        }
    }

    protected static function analysisDataExist($dataObject) {
        return (self::documentsExist($dataObject) && self::codesExist($dataObject));
    }

    protected static function codeAssignmentDataExist($dataObject) {
        return (self::codesExist($dataObject) && self::researchQuestionsExist($dataObject));
    }
}
?>