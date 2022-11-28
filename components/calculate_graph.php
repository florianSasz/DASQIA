<?php
/**
 * class calculates for the data saturation diagrams on the project page
 */
require_once "../components/calculate_analysis_data.php";

class CalculateGraph extends CalculateAnalysisData{
    public array $newCodesPerDocument;
    public array $totalCodeSaturation;
    public array $researchQuestionsSaturation;

    function __construct(array $projectDocuments, array $projectReseachQuestions, array $projectCodes, DatabaseAccess $database) {
        parent::__construct($projectDocuments, $projectReseachQuestions, $projectCodes, $database);
        $this->newCodesPerDocument = $this->calculateNewCodesPerDocument();
        $this->totalCodeSaturation = $this->calculateTotalCodeSaturation();
        $this->researchQuestionsSaturation = $this->calculateRQSaturation();
    }

    private function calculateNewCodesPerDocument() {
        $newCodes = array();
        $usedCodes = array();
        foreach ($this->documents as $i=>$document) {
            $numberNewCodes = 0;
            foreach ($document["codes"]["codeIDs"] as $codeID) {
                if (!in_array($codeID, $usedCodes)) {
                    $numberNewCodes++;
                    $usedCodes[] = $codeID;
                }
            }
            $newCodes[] = array("documentIndex" => $document["originalIndex"], "numberCodes" => $numberNewCodes);
        }
        return $newCodes;
    }

    // requires to run calculateNewCodesPerDocument() first 
    private function calculateTotalCodeSaturation() {
        $codeSaturation = array();
        $totalNumberOfCodes = 0;
        foreach ($this->newCodesPerDocument as $document) {
            $totalNumberOfCodes += $document["numberCodes"];
            $codeSaturation[] = array("documentIndex" => $document["documentIndex"], "numberCodes" => $totalNumberOfCodes);
        }
        return $codeSaturation;
    }

    private function calculateRQSaturation() {
        $saturationPerRQ = array();
        foreach ($this->researchQuestions as $rq) {
            $codeSaturation = array();
            $usedCodes = array();
            $numberNewCodes = 0;
            foreach ($this->documents as $i=>$document) {
                foreach ($document["codes"]["codeIDs"] as $codeID) {
                    if (array_key_exists("researchQuestions", $this->codes[$codeID])) {
                        if (in_array($rq["id"], $this->codes[$codeID]["researchQuestions"])) {
                            if (!in_array($codeID, $usedCodes)) {
                                $numberNewCodes++;
                                $usedCodes[] = $codeID;
                            }
                        }
                    }
                }
                $codeSaturation[] = array("documentIndex" => $document["originalIndex"], "numberCodes" => $numberNewCodes);
            }
            $saturationPerRQ[$rq["question"]] = $codeSaturation;
        }
        return $saturationPerRQ;
    }
}
?>