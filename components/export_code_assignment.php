<?php
/**
 * class to export the code assignments as .csv file
 */
class ExportCodeAssignment {

    public static function buildCSV(ProjectData $projectData) {
        $codes = self::setCodeIDToArrayKey($projectData->codes);
        $rqIndicies = array(); // lookup table to know corresponding index for certain researchQuestion
        $csvHeaderValues = array("code"); 
        foreach ($projectData->researchQuestions as $i=>$rq) {
            $rqIndicies[$rq["id"]] = $i;
            $csvHeaderValues[] = $rq["question"];
        }
    
        $csvData = array();
        foreach ($codes as $i=>$code) {
            $csvData[$i] = array("code"=>self::getFullCodeName($code["id"], $code["name"], $codes), "assignment"=>array_fill(0, count($projectData->researchQuestions), 0));
            if (isset($code["researchQuestions"])) {
                foreach ($code["researchQuestions"] as $rqAssignment) {
                    $csvData[$i]["assignment"][$rqIndicies[$rqAssignment]] = 1;
                }
            }
        }
        $csvBuilder = new CreateCSV("code", "assignment", $csvHeaderValues);
        return $csvBuilder->createCSVData($csvData, "codeAssignment");
    }

    /**
     * TODO: create CodeUtilities class
     * -> setCodeIDToArrayKey() and getFullCodeName() are nearly copies of CalculateDetailedDocumentInformation/CalculateAnalysisData methods
     */
    private static function setCodeIDToArrayKey(array $codeCollection) {
        $result = [];
        foreach ($codeCollection as $code) {
            $result[$code["id"]] = $code;
        }
        return $result;
    }

    private static function getFullCodeName(int $codeID, string $codeName, array $codeCollection) {
        if ($codeCollection[$codeID]["parentID"]) {
            $parentName = $codeCollection[$codeCollection[$codeID]["parentID"]]["name"];
            $codeName = $parentName . "\\" . $codeName;
            $codeName = self::getFullCodeName($codeCollection[$codeID]["parentID"], $codeName, $codeCollection);
        }        
        return $codeName;
    }
}
?>