<?php
/**
 * page to assign the codes to research questions
 */

require_once "../components/html.php";
require_once "../components/project_side_bar.php";
require_once "../components/database.php";
require_once "../components/projectData.php";
require_once "../components/access_user.php";
require_once "../components/access_project.php";

function writeCodeAssignmentSpace(ProjectData $projectData) {
    $output =
    "<div class='workspace'>
        <table>
            <tr>
                <td valign='top'>"

                    . writeSideBar($projectData->teamMembers, "back to project") . 
            
                "</td>
                <td valign='top'>
                    <div class='bubble pageBubbles assignmentBubble' id='mainBubble'>
                        <p class='projectHeadline Headlines '> " . $projectData->title . " </p>
                        <p class='bubbleHeadline Headlines'> code assignment </p>
                        <br>
                        <p> assign your codes to your research questions: </p>
                        <div id='codeAssignmentSpace'></div>
                        <form action='../components/code_assignment_processing.php' method='post' id='submitForm'>
                            <div id='assignments'>
                            </div>
                        </form>
                        <div class='center navigationButtons' id='navigation'>
                        <button class='button' onclick='submit.saveAssignment()'> save </button>
                            <button class='button' onclick='submit.cancelAssignment()'> cancel </button> 
                        </div>
                    </div>
                </td>
                <td valign='top'>"

                    . writeCodeFilterSection()
                    . writeResearchQuestionFilterSection() .
                    
                "</td>
            </tr>
        </table>
    </div>";
    echo $output;
}


function writeCodeFilterSection() {

    function showAll() {
        $output = 
        "<div class='filterMethodBubble'>
            <input type='radio' id='showAll' name='filterMethod' checked>
            <label for='showAll'> show all </label>
        </div>";
        return $output;
    }

    function filterByKeyword() {
        $output = 
        "<div class='filterMethodBubble'>
            <input type='radio' id='filterByKeyword' name='filterMethod'>
            <label for='filterByKeyword'> filter by keyword </label>
            <div class='center addKeyword'>
                <label for='keywordInput' class='sameLine'> keyword: </label>
                <input type='text' id='keywordInput' class='sameLine'>
                <div class='sameLine'>
                    <button class='button' id='keywordAddButton'> add </button> 
                </div>
            </div>
            <div id='keyworsToFilter'>
                <table id='keywordTable'>
                </table>
            </div>
        </div>";
        return $output;
    }

    function alphabetical() {
        $output = 
        "<div class='filterMethodBubble'>
            <input type='radio' id='filterAlphabetical' name='filterMethod'>
            <label for='filterAlphabetical'> alphabetical </label>
        </div>";
        return $output;
    }

    function filterByGroup() {
        $output = 
        " <div class='filterMethodBubble'>
            <input type='radio' id='filterByGroup' name='filterMethod'>
            <label for='filterByGroup'> filter by group </label>
            <br><br>
            <div id='groupSelection'>
            </div>
        </div>";
        return $output;
    }

    $output = 
    "<div id='filter' class='bubble filterBubble'>
        <p class='blueSubHeadline center FilterPanelHeadline'> filter codes </p><hr>
        <div class='center notAssignedCheckbox'>
            <input type='checkbox' id='codesNotAssigned' class='checkbox'>
            <label for='codesNotAssigned'> not assigned </label>
        </div>"; 

        $output .= "<hr>";
        $output .= showAll();
        $output .= "<hr>";
        $output .= filterByKeyword();
        $output .= "<hr>";
        $output .= filterByGroup();
        $output .= "<hr>";
        $output .= alphabetical();

    $output .= 
    "</div>";

    return $output;
}

function writeResearchQuestionFilterSection() {
    $output =
    "<div class='bubble filterBubble'>
        <p class='blueSubHeadline center FilterPanelHeadline'> filter research questions </p><hr>
        <div id='researchQuestionsSection' class='filterMethodBubble'>
        </div>
        <hr>
        <div class='center notAssignedScope'>
            <input type='checkbox' id='notAssignedScopeToggle' class='checkbox'>
            <label for='notAssignedScopeToggle'> apply 'not assigned' filter only <br> on selected research questions </label>
        </div>
    </div>";

    return $output;
}

function writeHiddenData(ProjectData $projectData) {    
    $output = 
    "<div id='hiddenData'>";
        $rqIndex = 0;
        foreach ($projectData->researchQuestions as $rq) {
            $data = "{$rq['question']}%%{$rq['id']}";
            $output .= "<input type='hidden' name='researchQuestion_". $rqIndex . "' value='" . $data . "'>";
            $rqIndex++;
        }
        $codeIndex = 0;
        // code syntax = codename, codeID, (optional)parentID, (optional)assigned researchQuestion IDs
        foreach ($projectData->codes as $code) {
            if (isset($code["researchQuestions"])) {
                $researchQuestions = implode("%%", $code['researchQuestions']);
                $data = "{$code['name']}%%{$code['id']}%%{$code['parentID']}%%{$researchQuestions}";
            } else {
                $data = "{$code['name']}%%{$code['id']}%%{$code['parentID']}";
            }
            $output .= "<input type='hidden' name='code_". $codeIndex . "' value='" . $data . "'>";
            $codeIndex++;
        }
    $output .=
    "</div>";
    echo $output;
}

session_start();
UserAccess::userIsLoggedIn();
UserAccess::userHasProjectSelected();
$database = new DatabaseAccess();
ProjectAccess::checkProjectIsNotFinished($database);
ProjectAccess::checkCodeAssignmentDataExist($database);
$projectData = new projectData($_SESSION["projectID"], "codeAssignment", $database);

$cssFiles = array("style_main.css", "style_all_project_pages.css", "style_code_assignment.css");
$jsFiles = array("code_assignment.js");
writeHeaderAndHeadline($cssFiles, $jsFiles);
writeTopBanner();
writeCodeAssignmentSpace($projectData);
writeHiddenData($projectData);
writeBottomBanner();
closeHtmlBody();
?>