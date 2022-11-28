<?php
/**
 * projectData Class bundels different database queries to make it easier to get all the important and frequently used data in a project  
 */

class projectData {
    public $id;
    public $title;
    public $description;
    public $xLabel;
    public $yLabel;
    public $xResolution;
    public $yResolution;
    public $axisFontSize;
    public $labelFontSize;
    public $vGridDivision;
    public $graphColor;
    public $finished;
    public $teamMembers = [];

    public $researchQuestions = [];
    public $documents = [];
    public $codes = [];
    public $numberOfCodes = [];
    public $numberOfCodesInDocuments = [];

    function __construct(int $projectID, string $flag, DatabaseAccess $database) {
        $temp = $database->getProjectData($projectID);
        $this->id = $temp["id"];
        $this->title = $temp["title"];
        $this->description = $temp["description"];
        $this->finished = $temp["finished"];
        $this->xLabel = $temp["x_axis"];
        $this->yLabel = $temp["y_axis"];
        $this->xResolution = $temp["x_resolution"];
        $this->yResolution = $temp["y_resolution"];
        $this->axisFontSize = $temp["axisFontSize"];
        $this->labelFontSize = $temp["labelFontSize"];
        $this->vGridDivision = $temp["vGridDivision"];
        $this->graphColor = $temp["graphColor"];
        $this->teamMembers = array_merge($database->getProjectMembers($this->id), $database->getProjectShadowMembers($this->id));
        
        switch ($flag) {
            case "all":
                $this->researchQuestionData($database);
                $this->documentsData($database);
                $this->codesData($database, true);
                $this->codesDataForProjectPage($database);
                break;
            case "researchQuestions":
                $this->researchQuestionData($database);
                break;
            case "documents":
                $this->documentsData($database);
                break;
            case "codes":
                $this->codesData($database, false);
                break;
            case "codeAssignment":
                $this->codesData($database, true);
                $this->researchQuestionData($database);
                break;
        }
    }

    private function researchQuestionData(DatabaseAccess $database) {
        $this->researchQuestions = $database->getResearchQuestions($this->id);
    }

    private function documentsData(DatabaseAccess $database) {
        $this->documents = $database->getDocuments($this->id);
    }

    private function codesData(DatabaseAccess $database, bool $RQAssignment) {
        $this->codes = $database->getCodesWithParent($this->id, $RQAssignment);
    }

    private function codesDataForProjectPage(DatabaseAccess $database) { 
        $this->numberOfCodes = count($this->codes);
        $this->numberOfCodesInDocuments = 0;
        for ($i = 0; $i < count($this->codes); $i++) {
            $temp = $database->getCodeDocumentRelation($this->codes[$i]["id"]); // TODO: replace with SQL query 
            if ($temp) {
                foreach ($temp as $document) {
                    $this->numberOfCodesInDocuments += $document["frequency"];
                }
            }
        }
    }
} 
?>