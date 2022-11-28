<?php
/**
 * base class to prepare the data that is needed for the data saturation diagrams and detailed-document-code-information page
 */
class CalculateAnalysisData {
    protected array $documents;
    protected array $researchQuestions;
    protected array $codes;
    
    function __construct(array $projectDocuments, array $projectReseachQuestions, array $projectCodes, DatabaseAccess $database) {
        $this->documents = $projectDocuments;
        $this->researchQuestions = $projectReseachQuestions;
        $this->codes = $projectCodes;
        
        $this->setOriginalIndex();
        $this->sortDocumentsByDate();
        $this->getCodesPerDocument($database);
        $this->setCodeIDToArrayKey();
    }

    private function setOriginalIndex() {
        for ($i = 0; $i < count($this->documents); $i++) {
            $this->documents[$i]["originalIndex"] = $i + 1;
        }
    }

    private function sortDocumentsByDate() {
        $dates = array_column($this->documents, 'evaluation_date');
        array_multisort($dates, SORT_ASC, $this->documents);
    }  

    private function getCodesPerDocument(DatabaseAccess $database) {    
        foreach ($this->documents as $key=>$document) {
            $codes = $database->getDocumentCodes($document["id"]);
            $this->documents[$key]["totalNumberCodes"] = $this->documents[$key]["codes"]; // ugly
            $this->documents[$key]["codes"] = array("codeIDs" => [], "frequency" => []);
            foreach ($codes as $code) {
                $this->documents[$key]["codes"]["codeIDs"][] = $code["codeID"];
                $this->documents[$key]["codes"]["frequency"][] = $code["frequency"];
            }
        }
    }

    private function setCodeIDToArrayKey() {
        $result = [];
        foreach ($this->codes as $i=>$code) {
            $result[$code["id"]] = $code;
        }
        $this->codes = $result;
    }
}

?>