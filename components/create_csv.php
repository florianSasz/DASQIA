<?php
/**
 * class to create .csv files
 */
class CreateCSV {
    private int $index;
    private string $dataKey;
    private string $dataValue;
    private string $headerKey;
    private string $headerValue;

    function __construct(string $dataKey, string $dataValue, array $headerValues) {
        $this->index = 0;
        $this->dataKey = $dataKey;
        $this->dataValue = $dataValue;
        $this->headerValues = $headerValues;
    }

    public function createCSVData(array $data, string $flag, array $additionalData=null) {
        switch ($flag) {
            case "diagram":
                /**
                 * headerValues = array("x"=>..., "y"=>...)
                 * additionalInformation = array("tableName"=>...)
                 */
                return $this->createHTMLInput($this->createCSVStringForDiagram($data, $additionalData["tableName"]), $flag);
                break;
            case "codeAssignment":
                /**
                 * headerValues = array(" ", rq1, rq2,...)
                 * additionalInformation = /
                 */
                return $this->createHTMLInput($this->createCSVStringForCodeAssignment($data), $flag);
                break;
            case "documents":
                /**
                 * 
                 */
                return $this->createHTMLInput($this->createCSVStringForCodeAssignment($data), $flag);
        }
    }
    
    private function createCSVStringForDiagram(array $data, string $tableName) {
        $csvString = "";
        $this->addLine2($tableName, "", $csvString);
        $this->addLine2($this->headerValues["x"], $this->headerValues["y"], $csvString);
        foreach ($data as $element) {
            $this->addLine2($element[$this->dataKey], $element[$this->dataValue], $csvString);
        }
        return $csvString;
    }

    private function createCSVStringForCodeAssignment(array $data) {
        $csvString = "";
        $this->addLineN($this->headerValues, $csvString);
        foreach ($data as $element) {
            $this->addLineN(array_merge(array($element[$this->dataKey]), $element[$this->dataValue]), $csvString);
        }
        return $csvString;
    }
    
    private function addLine2(string $value1, string $value2, string &$csvString) {
        $csvString .= $value1 . ";" . $value2 . "\r\n";
    }

    private function addLineN(array $values, string &$csvString) {
        $csvString .= implode(";", $values) . "\r\n";
    }

    private function createHTMLInput(string $csvString, string $flag) {
        return "<input type='hidden' id='csv_" . $flag . "_" . $this->index++ . "' value='" . $csvString . "'>";
    }
}
?>