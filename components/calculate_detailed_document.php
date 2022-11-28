<?php
/**
 * calculates the data for the detailed code document infomation page (code_documents_information.php)
 */
require_once "../components/calculate_analysis_data.php";

class CalculatedDocument {
    public string $title;
    public array $distribution;
    public array $newCodes;
    public array $codeFrequency;
    public int $totalCodeFrequency;

    function __construct(string $title, int $totalCodeFrequency) {
        $this->title = $title;
        $this->totalCodeFrequency = $totalCodeFrequency;
    }

    function setNewCodes(array $newCodes) {
        $this->newCodes = $newCodes;
    }

    function setcodeFrequency(array $codeFrequencies) {
        $this->codeFrequency = $codeFrequencies;
    }

    function setDistribution(array $distribution) {
        $this->distribution = $distribution;
    }
}

class CalculateDetailedDocumentInformation extends CalculateAnalysisData {
    private array $calculatedDocuments;
    private array $distributionTemplateArray;

    function __construct(array $projectDocuments, array $projectReseachQuestions, array $projectCodes, DatabaseAccess $database) {
        parent::__construct($projectDocuments, $projectReseachQuestions, $projectCodes, $database);

        $this->setupDocuments();
        $this->setupDistributionTemplate();
        $this->setRqIDToArrayKey();
    }
    
    public function calculate(){
        $this->calculateNewCodes();
        $this->calculateCodeFrequency();
        $this->calculateDistribution();
        
        $this->translateIDsToLanguage(); // up until this point all the calculations are done only with the databaseIDs of the documents, codes and researchQuestions

        return $this->calculatedDocuments;
    } 

    private function setupDocuments() {
        $this->calculatedDocuments = array();
        foreach ($this->documents as $document) {
            $this->calculatedDocuments[] = new CalculatedDocument($document["title"], $document["totalNumberCodes"]);
        }
    }

    private function setupDistributionTemplate() {
        $this->distributionTemplateArray = array();
        foreach ($this->researchQuestions as $rq) {
            $this->distributionTemplateArray[$rq["id"]] = 0;
        }   
    }

    private function setRqIDToArrayKey() { // turns indexed array to associative array by settings the rq id as array kez for faster access later
        $result = array();
        foreach ($this->researchQuestions as $rq) {
            $result[$rq["id"]] = $rq;
        }
        $this->researchQuestions = $result;
    }

    private function calculateDistribution() {
        foreach ($this->documents as $i=>$document) {
            $distribution = $this->distributionTemplateArray;
            foreach ($document["codes"]["codeIDs"] as $codeID) {
                if (isset($this->codes[$codeID]["researchQuestions"])) {
                    $rqs = $this->codes[$codeID]["researchQuestions"];
                    foreach ($rqs as $rqID) {
                        $distribution[$rqID]++;
                    }
                }
            }
            $this->calculatedDocuments[$i]->setDistribution($distribution);
        } 
    }

    private function calculateCodeFrequency() {
        foreach ($this->documents as $i=>$document) {
            $codeFrequencies = array();
            foreach ($document["codes"]["codeIDs"] as $j=>$code) {
                $codeFrequencies[$code] = $document["codes"]["frequency"][$j]; 
            }
            $this->calculatedDocuments[$i]->setcodeFrequency($codeFrequencies);
        }
    }

    private function calculateNewCodes() {
        $usedCodes = array();
        foreach ($this->documents as $i=>$document) {
            $newCodes = array();
            foreach ($document["codes"]["codeIDs"] as $codeID) {
                if (!in_array($codeID, $usedCodes)) {
                    $newCodes[] = $codeID;
                    $usedCodes[] = $codeID;
                }
            }
            $this->calculatedDocuments[$i]->setNewCodes($newCodes);
        }
    }

    private function translateIDsToLanguage() {
        foreach ($this->calculatedDocuments as $document) {
            $this->translateDistribution($document);
            $this->translateNewCodes($document);
            $this->translateCodeFrequency($document);
        }
    }

    private function translateDistribution(CalculatedDocument $document) {
        $result = array();
        foreach (array_keys($document->distribution) as $rqID) {
            $result[$this->researchQuestions[$rqID]["question"]] = $document->distribution[$rqID];
        }
        $document->distribution = $result;
    }

    private function translateNewCodes(CalculatedDocument $document) {
        foreach ($document->newCodes as $i=>$codeID) {
            $document->newCodes[$i] = $this->getFullCodeName($codeID, $this->codes[$codeID]["name"]);
        }
    }

    private function translateCodeFrequency(CalculatedDocument $document) {
        $result = array();
        foreach (array_keys($document->codeFrequency) as $codeID) {
            $result[$this->getFullCodeName($codeID, $this->codes[$codeID]["name"])] = $document->codeFrequency[$codeID];
        }
        $document->codeFrequency = $result;
    }

    private function getFullCodeName(int $codeID, string $codeName) {
        if ($this->codes[$codeID]["parentID"]) {
            $parentName = $this->codes[$this->codes[$codeID]["parentID"]]["name"];
            $codeName = $parentName . "\\" . $codeName;
            $codeName = $this->getFullCodeName($this->codes[$codeID]["parentID"], $codeName);
        }        
        return $codeName;
    }
}
?>