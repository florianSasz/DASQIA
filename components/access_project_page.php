<?php
/**
 * class checks if a user is authorization to access certain pages; checks if the minimum requiered data exists in a project;
 * will store these results and alter the project page (project.php) to disable certain function 
 */
require_once "../components/access_evaluate.php";

class ProjectPageAccess extends EvaluateAccess {
    public $editResearchQuestionsDisable;

    public $editDocumentsDisable;
    public $documentsExist;
    public $exportDocumentsDisable;

    public $editCodesDisable;
    public $codesExist;
    public $assignCodesDisable;
    public $assignCodesExportDisable;

    public $analysisDataDisable;
    public $analysisDataExist;

    function __construct(projectdata $projectData) {
        $projectFinished = parent::projectIsFinished($projectData);
        $documentsExist = parent::documentsExist($projectData);
        $researchQuestionsExist = parent::researchQuestionsExist($projectData);
        $codesExist = parent::codesExist($projectData);

        $this->editResearchQuestionsDisable = ($projectFinished) ? "disabled" : "";

        $this->editDocumentsDisable = ($projectFinished) ? "disabled" : "";
        $this->documentsExist = $documentsExist;
        $this->exportDocumentsDisable = ($documentsExist) ? "" : "disabled";

        $this->editCodesDisable = (!$projectFinished && $documentsExist) ? "" : "disabled";
        $this->codesExist = $codesExist;
        $this->assignCodesDisable = (!$projectFinished && $codesExist && $researchQuestionsExist) ? "" : "disabled";
        $this->assignCodesExportDisable = ($codesExist && $researchQuestionsExist) ? "" : "disabled";

        $this->analysisDataExist = ($documentsExist && $codesExist);
        $this->analysisDataDisable = ($this->analysisDataExist) ? "" : "disabled";
    }
}
?>