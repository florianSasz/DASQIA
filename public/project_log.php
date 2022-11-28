<?php
/**
 * page to see the project log 
 */
require_once "../components/html.php";
require_once "../components/database.php";
require_once "../components/access_user.php";
require_once "../components/project_side_bar.php";
require_once "../components/projectData.php";
require_once "../components/project_log_class.php";

function writeProjectLogPage(ReadProjectLog $readLog, projectData $projectData) {
    $output = 
    "<div class='workspace'>
        <table>
            <tr>
                <td valign='top'>"

                    . writeSideBar($projectData->teamMembers, "back to project") . 
            
                "</td>
                <td valign='top'>
                    <div class='bubble pageBubbles projectLogBubble'>
                        <p class='projectHeadline Headlines'> " . $projectData->title . " </p>
                        <p class='bubbleHeadline logHeadline headlines sameLine'> project log </p>
                        <a href='" . $readLog->getFilePath($_SESSION["projectID"]) . "' 
                          download='[" . date("d.m.y, H:i:s") . "]" . $projectData->title . "' class='button downloadButton'>download log file</a>
                        <div>"
                
                             . $readLog->getFileContent() .
                
                      "</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>";

    echo $output;
}

session_start();
UserAccess::userIsLoggedIn();
UserAccess::userHasProjectSelected();
$database = new DatabaseAccess();
$projectData = new ProjectData($_SESSION["projectID"], "", $database);
$readLog = new ReadProjectLog($_SESSION["projectID"]);

$cssFiles = array("style_main.css", "style_all_project_pages.css", "style_project_log.css");
writeHeaderAndHeadline($cssFiles);
writeTopBanner();
writeProjectLogPage($readLog, $projectData);
writeBottomBanner();
closeHtmlBody();
?>