<?php
/**
 * main page of a project
 */
require_once "../components/html.php";
require_once "../components/database.php";
require_once "../components/project_side_bar.php";
require_once "../components/projectData.php";
require_once "../components/string_utilities.php";
require_once "../components/access_user.php";
require_once "../components/access_project_page.php";
require_once "../components/calculate_graph.php";
require_once "../components/draw_graph.php";
require_once "../components/graph_settings.php";
require_once "../components/redirect_user.php";
require_once "../components/create_csv.php";
require_once "../components/export_code_assignment.php";

function writeProjectHeadline(ProjectData $projectData) {
    if ($projectData->description) {
        $output = 
        "<div>
            <details>
                <summary class='projectHeadline projectHeadlineCollapse' id='projectTitle'>" . $projectData->title . "</summary>
                <p class='greyText'>" . $projectData->description . "</p>
            </details>
        </div>";
    } else {
        $output = 
        "<div>
            <p class='projectHeadline' id='projectTitle'> " . $projectData->title . " </p>
        </div>";
    }
    return $output;
}

function writeResearchQuestions(ProjectData $projectData, ProjectPageAccess $access) {
    $output = "
    <div>
        <div>
            <p class='bubbleHeadline sameLine'> research questions </p> 
            <form action='./research_questions.php' class='sameLine'>
                <input type='submit' value='edit' class='button projectButtons'" . $access->editResearchQuestionsDisable . ">
            </form>
        </div>
        <div class='projectContentOffset'>";
            if (count($projectData->researchQuestions) > 0) {
                foreach ($projectData->researchQuestions as $rq) {
                    if (strlen($rq["description"]) > 0) {
                        $output .=
                        "<details class='rqContainer'>
                            <summary class='blackSubHeadline question'>". $rq['question'] . " </summary>
                            <p class='greyText descriptionsOffset'>" . $rq["description"] . "</p>
                        </details>";
                    } else {
                        $output .=
                        "<div class='rqContainer'>
                            <p class='sameLine noMargin'> &#9679; </p>   
                            <p class='blackSubHeadline questionOffset sameLine noMargin'>". $rq['question'] . "</p>
                        </div>";
                    }
                }
            } else {
                $output .= "<p> There are no reseach questions in the project.</p>";
            }

    $output .= 
        "</div>
    </div>";

    return $output;
}

function writeDocuments(ProjectData $projectData, ProjectPageAccess $access) {
    
    function createCSV(ProjectData $projectData, array $headerValues) {
        $csvBuilder = new createCSV("id", "data", $headerValues);
        $csvData = array();
        foreach ($projectData->documents as $i=>$document) {
            unset($document["id"]); // remove not neeed data in .csv
            unset($document["projectID"]);
            unset($document["original_interviewer"]);
            unset($document["original_evaluator"]);
            $csvData[] = array("id"=>$i+1, "data"=>array_values($document));
        }
        return $csvBuilder->createCSVData($csvData, "documents");
    }

    $headline = ["id", "type", "title", "interviewer", "interview date", "evaluator", "evaluation date", "codes"];
    $documentsCSV = (!$access->exportDocumentsDisable) ? createCSV($projectData, $headline) : "";

    $output = 
    "<div>
        <div>
            <p class='bubbleHeadline sameLine'> documents </p> 
            <form action='./documents.php' class='sameLine'>
                <input type='submit' value='edit' class='button projectButtons'" . $access->editDocumentsDisable . ">
            </form>
            <button type='button' class='button projectButtons'" . $access->exportDocumentsDisable . " onclick='downloadDocuments()'>download documents as .csv</button>   
        </div>
        <div>" . $documentsCSV . "</div>";
        

        // TODO: auslagern in funtion
        if ($access->documentsExist) {
            $output .= 
            "<div class='projectContentOffset'>
                <table class='documentsTable'>
                    <tr>";
                    foreach ($headline as $th) {
                        $output .= "<th class='documentsTh'>" . $th . "</th>";
                    }
            $output .= 
                    "</tr>";
                    foreach ($projectData->documents as $i=>$document) {
                        $output .= "<tr class='documentsTd'>";
                        $documentKeys = array_keys($document);
                        foreach ($documentKeys as $key) {
                            if ($key != "projectID" && $key != "codeIDs" && $key != "codeIDsFrequency" && $key != "original_interviewer" && $key != "original_evaluator") {
                                if ($key == "id") {
                                    $output .= 
                                    "<td class='documentsTd'>" . $i + 1 . "</td>";
                                } else if ($key == "interview_date" || $key == "evaluation_date") {
                                    $output .= 
                                    "<td class='documentsTd'>" . convertDateFormat($document[$key]) . "</td>";
                                } else {
                                    $output .= 
                                    "<td class='documentsTd'>" . $document[$key] . "</td>";
                                }
                            }
                        }
                        $output .= "</tr>";
                    }
            $output .=
                "</table>
            </div>";
        } else {
            $output .= "<p class='projectContentOffset'>There are no documents in the project.</p>";
        }

    $output .= 
    "</div>";
    return $output;
}

function writeCodes(ProjectData $projectData, ProjectPageAccess $access) {
    $csvCodeAssignment = (!$access->assignCodesExportDisable) ? ExportCodeAssignment::buildCSV($projectData) : "";
    $output = 
    "<div>
        <div>
            <p class='bubbleHeadline sameLine'> codes </p> 
            <form action='./codes.php' class='sameLine'>
                <input type='submit' value='edit' class='button projectButtons'" . $access->editCodesDisable . ">
            </form>
            <form action='./code_assignment.php' class='sameLine'>
                <input type='submit' value='code assignment' class='button projectButtons'" . $access->assignCodesDisable . ">
            </form>
            <button type='button' class='button projectButtons' id='download_code_assignment' onclick='downloadCodeAssignment()' " . $access->assignCodesExportDisable . ">download code assignment as .csv</button>
            <div>" . $csvCodeAssignment . "</div>   
        </div>";

        if ($access->codesExist) {
            $output .= 
            "<div class='projectContentOffset'>
                <p> number of codes: <b>" . $projectData->numberOfCodes . "</b></p>
                <p> total code frequency in documents: <b>" . $projectData->numberOfCodesInDocuments . "</b></p>
            </div>";
        } else {
            $output .= "<p class='projectContentOffset'>There are no codes in the project.</p>";
        }
    $output .=
            "<div>
                <form action='./code_document_information.php' method='post'>
                    <input type='submit' class='button projectContentOffset' value='detailed code-document information'" . $access->analysisDataDisable . ">
                </form>
            <div>";

    $output .= 
    "</div>";

    return $output;
}

function writeAnalysis(ProjectData $projectData, DatabaseAccess $database, ProjectPageAccess $access) {
    $output = 
    "<div>
        <div id='analysisTab'>
            <p class='bubbleHeadline sameLine'> analysis </p>
            <button class='button projectButtons' onclick='toggleGraphSettings()'>graph settings</button>
            <button id='downloadSVG_all' class='button projectButtons'" . $access->analysisDataDisable . " onclick='downloadAllSVG()'>download all .svg</button>
            <button id='downloadPNG_all' class='button projectButtons'" . $access->analysisDataDisable . " onclick='downloadAllPNG()'>download all .png</button>
            <button id='downloadCSV_all' class='button projectButtons'" . $access->analysisDataDisable . " onclick='downloadAllCSV()'>download all .csv</button>
        </div>
        <input type='hidden' id='graphSettingsHTML' value='" . htmlspecialchars(writeGraphSettings($projectData)) . "'>
        <div id='graphSettings'>    
        </div>
        <div class='projectContentOffset'>";
        if ($access->analysisDataExist) {
            $graphData = new CalculateGraph($projectData->documents, $projectData->researchQuestions, $projectData->codes, $database);

            $csvBuilder = new CreateCSV("documentIndex", "numberCodes", array("x"=>$projectData->xLabel, "y"=>$projectData->yLabel));

            $graphs = array(
                array(
                    "title" => "new codes per document",
                    "data" => $graphData->newCodesPerDocument,
                    "csv" => $csvBuilder->createCSVData($graphData->newCodesPerDocument, "diagram", array("tableName"=>"new codes per document"))
                ),
                array (
                    "title" => "total code saturation",
                    "data" => $graphData->totalCodeSaturation,
                    "csv" => $csvBuilder->createCSVData($graphData->totalCodeSaturation, "diagram", array("tableName"=>"total code saturation"))
                )
            );
            foreach (array_keys($graphData->researchQuestionsSaturation) as $rqTitle) {
                $graphs[] = array(
                    "title" => $rqTitle,
                    "data" => $graphData->researchQuestionsSaturation[$rqTitle],
                    "csv" => $csvBuilder->createCSVData($graphData->researchQuestionsSaturation[$rqTitle], "diagram", array("tableName"=>$rqTitle))
                );
            }

            $drawGraph = new DrawGraph($projectData->xLabel, $projectData->yLabel, $projectData->axisFontSize, $projectData->labelFontSize, $projectData->vGridDivision, $projectData->graphColor);
            $output .= $drawGraph->drawAllGraphs($graphs);

            } else {
                $output .= "<p>An analysis is not possible yet. Add documents and codes to see the first results.</p>";
            }
            
    $output .=
        "</div>
    </div>";

    return $output;
}

function writeProjectSpace(ProjectData $projectData, DatabaseAccess $database, ProjectPageAccess $access) {
    $output = 
    "<div class='workspace'>
        <table>
            <tr>
                <td valign='top'>
                    <div>"
                        . writeSideBar($projectData->teamMembers, "back home")
                        . "<form action='./edit_project.php' class='center'>
                            <input type='submit' value='edit project' class='button pageBubbles'>
                        </form>
                        <br>
                        <form action='./project_log.php' class='center'>
                            <input type='submit' value='project log' class='button pageBubbles'>
                        </form>
                    </div>                   
                </td>
                <td>
                    <div class='bubble projectBubble pageBubbles'>
                        <div class='projectContent'>"
                            . writeProjectHeadline($projectData, $access)
                            . "<br><br>"
                            . writeResearchQuestions($projectData, $access)
                            . "<br><br>"
                            . writeDocuments($projectData, $access)
                            . "<br><br>"
                            . writeCodes($projectData, $access)
                            . "<br><br>"
                            . writeAnalysis($projectData, $database, $access) 
                            . "<br><br>
                        </div>
                    </div>
                </td>     
            </tr>
        </table>
    </div>";
    echo $output;
}

function writeGraphResolution(projectData $projectData) {
    $output = 
    "<div>
        <input type='hidden' id='initialXResolution' value='" . $projectData->xResolution . "'>
        <input type='hidden' id='initialYResolution' value='" . $projectData->yResolution . "'>
    </div>";

    echo $output;
}

function setProjectID(DatabaseAccess $database) {
    // if the user opens a project page for the first time in a session
    if (!isset($_SESSION["projectID"])) {
        if (isset($_POST["projectID"])) {
            // check, if user is really in the project
            if (!UserAccess::userIsInProject($_POST["projectID"], $database)) {
                RedirectUser::returnToHomePage();
            }
        } else {
            RedirectUser::returnToHomePage();
        }
        // save projectID for usage on other pages
        $_SESSION["projectID"] = $_POST["projectID"];
    }    
}

session_start();
UserAccess::userIsLoggedIn();
$database = new DatabaseAccess();
setProjectID($database);
$projectData = new projectData($_SESSION["projectID"], "all", $database);
$access = new ProjectPageAccess($projectData);

$cssFiles = array("style_main.css", "style_project_main_page.css", "style_all_project_pages.css");
$jsFiles = array("graph.js", "export_data.js");
$externalLibs = array("https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js", "https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.0/FileSaver.js");
writeHeaderAndHeadline($cssFiles, $jsFiles, null, $externalLibs);
writeTopBanner();
writeProjectSpace($projectData, $database, $access);
writeBottomBanner();
writeGraphResolution($projectData);
closeHtmlBody();
?>