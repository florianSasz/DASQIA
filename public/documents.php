<?php
/**
 * page to edit the documents in a project 
 */

require_once "../components/html.php";
require_once "../components/project_side_bar.php";
require_once "../components/database.php";
require_once "../components/projectData.php";
require_once "../components/access_user.php";
require_once "../components/access_project.php";
require_once "../components/string_utilities.php";
require_once "../components/popup.php";

function writeAliasStatusInfo(string $documnetType) {
    $hintText = "<div class='filledDotBlue dot sameLine'></div>: the name in the file was found as an alias and replaced with the corresponding account name.<br><br>
                <div class='filledDotOrange dot sameLine'></div>: the name in the file was found as an alias but this alias exists more than once among the 
                project members, meaning that it is not possible to assign the correct name.";
    return
    "<div id='aliasStatusInfo_" . $documnetType . "' class='hide sameLine aliasStatusInfo'><br>"                                
        . popUpHint($hintText, "sameLine") .
        "<div class='filledDotBlue dot sameLine'></div> / <div class='filledDotOrange dot sameLine'></div>
    </div>";
}

function writeDocumentsSpace(projectData $projectData){
    $output = "
    <div class='workspace'>
        <div class='circleBase circle1'></div>
        <table>
            <tr>
                <td valign='top'>"

                    . writeSideBar($projectData->teamMembers, "back to project") . 
            
                "</td>
                <td valign='top'>
                    <div class='bubble pageBubbles documentsBubble'>
                        <p class='projectHeadline Headlines'> " . $projectData->title . " </p>
                        <p class='bubbleHeadline Headlines'> documents </p>
                        <div class='center'>";

                            $hintText = "To import the documents from MAXQDA go to <b>Variables > Export Document Variables</b> and select the file type <b>Website (.html)</b>.";
                            $output .= popUpHint($hintText);

    $output .=             "<p class='sameLine'> variables file: </p>
                            <input type='file' id='varFileInput' accept='.html' class='sameLine'>
                            <button type='button' id='extractDataButton'  class='button sameLine'> extract data </button>
                        </div>
                        <div class='center'>";

                        if (isset($_SESSION["error"])) {
                            $output .= "<p class='redText'>" . $_SESSION["error"] . "</p>";
                            unset($_SESSION["error"]);
                        }

    $output .=         "</div>
                        <form action='../components/documents_processing.php' method='post'>
                            <div id='documentsSpace'>
                                <div id='existingDocumentsTable'>
                                    <div id='existingCaptionContainer'>
                                        <div id='existingCaption' class='sameLine'></div>"
                                        . writeAliasStatusInfo("existing") .   
                                    "</div>
                                    <div id='existingTable'></div>
                                </div>
                                <div id='newDocumentsTable'>
                                    <div id='newCaptionContainer'>
                                        <div id='newCaption' class='sameLine'></div>"
                                        . writeAliasStatusInfo("new") . 
                                    "</div>
                                    <div id='newTable'></div>
                                </div>
                            </div>
                            <br>
                            <div class='center'>
                                    <input type='submit' value='save' class='button' name='navigation'>
                                    <input type='submit' value='cancel' class='button'name='navigation'>
                            </div>
                        </form>
                    </div>
                </td>
            </tr>
        </table>
    </div>";

    echo $output;
}

function writeProjectMembersData(projectData $projectData, DatabaseAccess $database){
    $output = "<div id='members'>";
    foreach ($projectData->teamMembers as $member) {
        if (isset($member["email"])) {
            $aliases = $database->getAliases($member["id"], "user");
        } else {
            $aliases = $database->getAliases($member["id"], "shadowuser");
        }
        $output .= "<input type='hidden' value='" . $member["name"] . "%%" . implode("%%", $aliases) . "'>";
    }
    $output .= "</div>";
    echo $output;
}

function writeProjectDocumentsData(projectData $projectData) {
    $output = "<div id='documents'>";
    foreach ($projectData->documents as $document) {
        $data = "{$document['type']}%%{$document['title']}%%{$document['interviewer']}%%{$document['original_interviewer']}%%{$document['interview_date']}%%{$document['evaluator']}%%{$document['original_evaluator']}%%{$document['evaluation_date']}%%{$document['codes']}%%{$document['id']}";
        $output .= "<input type='hidden' value='" . $data . "'>";
    }
    $output .= "</div>";
    echo $output;
}

session_start();
UserAccess::userIsLoggedIn();
UserAccess::userHasProjectSelected();
$database = new DatabaseAccess();
ProjectAccess::checkProjectIsNotFinished($database);
$projectData = new projectData($_SESSION["projectID"], "documents", $database);

$cssFiles = array("style_main.css", "style_all_project_pages.css", "style_documents.css");
$jsFiles = array("documents.js", "popup.js");
writeHeaderAndHeadline($cssFiles, $jsFiles);
writeProjectMembersData($projectData, $database);
writeProjectDocumentsData($projectData);
writeTopBanner();
writeDocumentsSpace($projectData);
writeBottomBanner();
closeHtmlBody();
?>