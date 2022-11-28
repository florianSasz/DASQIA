<?php
/**
 * page to edit the codes in a project
 */

require_once "../components/html.php";
require_once "../components/project_side_bar.php";
require_once "../components/database.php";
require_once "../components/projectData.php";
require_once "../components/access_user.php";
require_once "../components/access_project.php";
require_once "../components/string_utilities.php";
require_once "../components/popup.php";

function writeCodeSpace(ProjectData $projectData) {
    $output =
    "<div class='workspace'>
        <table>
            <tr>
                <td valign='top'>"

                . writeSideBar($projectData->teamMembers, "back to project") . 
            
                "</td>
                <td valign='top'>
                    <div class='bubble pageBubbles codesBubble'>
                        <p class='projectHeadline Headlines'> " . $projectData->title . " </p>
                        <p class='bubbleHeadline Headlines'> codes </p>
                        <div class='center'>";

                            $hintText = "To import the codes from MAXQDA go to <b>Visual Tools > Code Matrix Browser > Export (ctrl + E)</b> and select the file type <b>Website (.html)</b>.";
                            $output .= popUpHint($hintText);   

    $output .=             "<p class='sameLine'> code-matrix file: </p>
                            <input type='file' id='codeMatrixInput' accept='.html' class='sameLine'>
                            <button type='button' id='extractCodesButton'  class='button sameLine'> extract data </button>
                        </div>";

                            if (isset($_SESSION["error"])) {
                                $output .= 
                                "<div>
                                    <p class='redText'>" . $_SESSION["error"] . "</p>
                                    <ul class='redText'>";
                                        foreach ($_SESSION["errorInformation"] as $document) {
                                            $output .= "<li>" . $document . "</li>";
                                        }
                                    $output .=
                                    "</ul>
                                </div>";
                                unset($_SESSION["error"]);
                                unset($_SESSION["errorInformation"]);
                            }

    $output .=         "<div id='codeSpaceExisting' class='codeLists'></div>
                        <div id='codeSpaceNew' class='codeLists'></div>
                        <form action='../components/codes_processing.php' id='formSubmit' method='post'>
                            <div id='submitData'></div>
                            <div class='center navigationButtons'>
                                <button type='button' class='button' id='save'>save</button>
                                <button type='button' class='button' id='cancel'>cancel</button>
                            </div>
                        </form>
                    </div>
                </td>
            </tr>
        </table>
    </div>";
    
    echo $output;
}

function getFullName(string $codeName, array $code, array $codeCollection) {
    if ($code["parentID"]) {
        $parentName = $codeCollection[$code["parentID"]]["name"];
        $codeName = $parentName . "\\" . $codeName;
        $codeName = getFullName($codeName, $codeCollection[$code["parentID"]], $codeCollection);
    }        
    return $codeName;
}

function writeHiddenData(projectData $projectData) {
    foreach ($projectData->codes as $i=>$code) { // sets array key as code ID for faster access later on
        $projectData->codes[$code["id"]] = $code;
        unset($projectData->codes[$i]);
    }

    $output = "<div id='codeData'>";
    foreach ($projectData->codes as $code) {
        $completeCodeName = getFullName($code["name"], $code, $projectData->codes); // = codeParents/../codeName
        $output .= "<input type='hidden' value='" . $completeCodeName . "%%" . $code["id"] . "%%" . $code["parentID"]  . "'>";
    }
    $output .= "</div>";
    echo $output;
}

session_start();
UserAccess::userIsLoggedIn();
UserAccess::userHasProjectSelected();
$database = new DatabaseAccess();
ProjectAccess::checkProjectIsNotFinished($database);
ProjectAccess::checkDocumentsExist($database);
$projectData = new projectData($_SESSION["projectID"], "codes", $database);

$cssFiles = array("style_main.css", "style_all_project_pages.css", "style_codes.css");
$jsFiles = array("codes.js", "popup.js");
writeHeaderAndHeadline($cssFiles, $jsFiles);
writeHiddenData($projectData);
writeTopBanner();
writeCodeSpace($projectData);
writeBottomBanner();
closeHtmlBody();
?>