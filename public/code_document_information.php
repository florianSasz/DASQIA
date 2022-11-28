<?php
/**
 * page to see detailed information about the codes in documents
 */

require_once "../components/html.php";
require_once "../components/database.php";
require_once "../components/projectData.php";
require_once "../components/project_side_bar.php";
require_once "../components/access_user.php";
require_once "../components/access_project.php";
require_once "../components/calculate_detailed_document.php";

function writeCodeDocumentPage(ProjectData $projectData, array $documents) {
    $output =
    "<div class='workspace'>
        <table>
            <tr>
                <td valign='top'>"

                    . writeSideBar($projectData->teamMembers, "back to project") .

                "</td>
                <td valign='top'>
                    <div class='bubble pageBubbles code-documentBubble'>
                        <p class='projectHeadline Headlines '>" . $projectData->title . "</p>
                        <p class='bubbleHeadline Headlines'>detailed code-document information</p>
                        <br>"   
                        . writeDocumentsInformation($documents) .
                        "<div class='center backButton'>"
                        . backToProjectButton() . 
                        "</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>";

    echo $output;
}

function writeDocumentsInformation(array $documents) {
    $output = "";
    foreach ($documents as $document) {
        $output .=
          openDocument($document->title, $document->totalCodeFrequency, count($document->codeFrequency))
          . writeNewCodes($document->newCodes)
          . writeDistribution($document->distribution)
          . writeCodeFrequencies($document->codeFrequency)
          . closeDocument();
    }
    return $output;
}

function openDocument(string $documentTitle, int $totalCodeFrequency, int $numberCodes) {
    return 
    "<details class='document'>
        <summary class='blackSubHeadline'><b>" . $documentTitle . "</b></summary>
        <div class='documentContent'>
            <p>total number of codes: " . $numberCodes . "</p>
            <p>total code frequency:  " . $totalCodeFrequency . "</p>
        </div>";
}

function writeDistribution(array $distribution) {
    if (count($distribution) === 0) {
        return;
    }
    $output = 
    "<details class='documentContent' open>
        <summary class='blueSubHeadline'> code distribution </summary>";
    
        foreach (array_keys($distribution) as $rq) {
            $output .= "<p class='documentContent'>" . $distribution[$rq] . "x " . $rq . "</p>";
        }

    $output .= 
    "</details>";
    
    return $output;
}

function writeNewCodes(array $newCodes) {
    $output = 
    "<details class='documentContent' open>
        <summary class='blueSubHeadline'> new codes </summary>";

        if (count($newCodes) === 0) {
            $output .= "<p class='documentContent'>There are no new codes in this document.</p>";
        } else {
            foreach ($newCodes as $code) {
                $output .= "<p class='documentContent'>" . $code . "</p>";
            }
        }

    $output .= 
    "</details>";
    
    return $output;
}

function writeCodeFrequencies(array $codeFrequencies) {
    $output = 
    "<details class='documentContent' open>
        <summary class='blueSubHeadline'> code frequencies </summary>";

        if (count($codeFrequencies) === 0) {
            $output .= "<p class='documentContent'>There are no codes in this document.</p>";
        } else {
            foreach (array_keys($codeFrequencies) as $code) {
                $output .= "<p class='documentContent'>" . $codeFrequencies[$code] . "x " . $code . "</p>";
            }
        }   

    $output .= 
    "</details>";
    
    return $output;
}

function closeDocument() {
    return 
    "</details>";
}

function backToProjectButton() {
    $output =
    "<form action='./project.php' method='post'>
        <input type='submit' class='button' value='back to project'>
    </form>";
    return $output;
}

session_start();
UserAccess::userIsLoggedIn();
UserAccess::userHasProjectSelected();
$database = new DatabaseAccess();
ProjectAccess::checkAnalysisDataExist($database);
$projectData = new ProjectData($_SESSION["projectID"], "all", $database);
$calculator = new CalculateDetailedDocumentInformation($projectData->documents, $projectData->researchQuestions, $projectData->codes, $database);
$data = $calculator->calculate();

$cssFiles = array("style_main.css", "style_all_project_pages.css", "style_code_document_information.css");
writeHeaderAndHeadline($cssFiles);
writeTopBanner();
writeCodeDocumentPage($projectData, $data);
writeBottomBanner();
closeHtmlBody();
?>