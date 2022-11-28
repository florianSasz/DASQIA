<?php
/**
 * class checks if a user is authorization to access certain pages; checks if the minimum requiered data exists in a project to enable certain functions in a project; 
 * is called after UserAccess; will redirect user if requirements are not met 
 */
require_once "../components/redirect_user.php";
require_once "../components/access_evaluate.php";

class ProjectAccess extends EvaluateAccess {
    public static function checkProjectIsNotFinished(DatabaseAccess $database) {
        if (parent::projectIsFinished($database)) {
            RedirectUser::returnToProjectPage();
        }
    }
    
    public static function checkUserIsNotLeader(DatabaseAccess $database) {
        if (parent::userIsLeader($database)) {
            RedirectUser::returnToProjectPage();
        }
    }

    public static function checkUserIsLeader(DatabaseAccess $database) {
        if (!parent::userIsLeader($database)) {
            RedirectUser::returnToProjectPage();
        }
    }

    public static function checkDocumentsExist(DatabaseAccess $database) {
        if (!parent::documentsExist($database)) {
            RedirectUser::returnToProjectPage();
        }
    }

    public static function checkResearchQuestionsExist(DatabaseAccess $database) {
        if (!parent::researchQuestionsExist($database)) {
            RedirectUser::returnToProjectPage();
        }
    }

    public static function checkCodesExist(DatabaseAccess $database) {
        if (parent::codesExist($database)) {
            RedirectUser::returnToProjectPage();
        }
    }

    public static function checkAnalysisDataExist(DatabaseAccess $database) {
        if (!parent::analysisDataExist($database)) {
            RedirectUser::returnToProjectPage();
        }
    }

    public static function checkCodeAssignmentDataExist(DatabaseAccess $database) {
        if (!parent::codeAssignmentDataExist($database)) {
            RedirectUser::returnToProjectPage();
        }
    }
}
?>