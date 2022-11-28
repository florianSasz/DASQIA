<?php
/**
 * page to edit the research questions inside a project
 */
require_once "../components/html.php";
require_once "../components/project_side_bar.php";
require_once "../components/database.php";
require_once "../components/projectData.php";
require_once "../components/access_user.php";
require_once "../components/access_project.php";

function writeRQSpace(projectData $projectData) {
    $output ="
    <div class='workspace'>
        <table>
            <tr>
                <td valign='top'>"

                    . writeSideBar($projectData->teamMembers, "back to project") . 
                    
                "</td>
                <td valign='top'>
                    <div class='bubble rqBubble pageBubbles'>
                        <form action='../components/research_questions_processing.php' method='post' class='sameLine'>
                            <p class='projectHeadline Headlines'> " . $projectData->title . " </p>
                            <p class='bubbleHeadline Headlines'> research questions </p>";

                            if (isset($_SESSION["error"])) {
                                $output .= "<p class='redText'>" . $_SESSION["error"] . "</p>";
                                unset($_SESSION["error"]);
                            }

$output .=                 "<table id='researchQuestions'>
                            </table>
                            <div class='center'>
                                <button type='button' id='addResearchQuestions' onclick='addRQ()' class='button addRqButton'> add research question </button>
                            </div>
                            <div class='navigationButtons center'>
                                <input type='submit' class='button' value='save' name='navigation'>
                                <input type='submit' class='button' value='cancel' name='navigation'>
                            </div>
                        </form>
                    </div>
                </td>
            </tr>
        </table>
    </div>";

    echo $output;
}

function writeRqData(projectData $projectData) {
    $output = "<div id='researchQuestionsData'>";
    foreach ($projectData->researchQuestions as $rq) {
        $data = "{$rq['question']}%%{$rq['description']}%%{$rq['id']}";
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
$projectData = new projectData($_SESSION["projectID"], "researchQuestions", $database);

$cssFiles = array("style_main.css", "style_all_project_pages.css", "style_research_questions.css");
$jsFiles = array("research_questions.js");
writeHeaderAndHeadline($cssFiles, $jsFiles);
writeRqData($projectData);
writeTopBanner();
writeRQSpace($projectData);
writeBottomBanner();
closeHtmlBody();
?>