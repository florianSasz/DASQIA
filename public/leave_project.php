<?php
/**
 * page to leave a project
 */
require_once "../components/html.php";
require_once "../components/database.php";
require_once "../components/projectData.php";
require_once "../components/access_user.php";
require_once "../components/access_project.php";

function writeDeleteProjectPage(ProjectData $projectData) {
    $output = 
    "<div class='workspace'>
        <div class='bubble delteProjectBubble'>
            <p class='bubbleHeadline headline'> leave project </p>
            <br><br>
            <form action='../components/leave_project_processing.php' method='post'>
                <p class='center'> Are you sure that you want to leave the project <b> ". $projectData->title ." </b>?</>
                <p class='redText center'><b> The only way to get back into this project is by being invited again. </b></p>
                <div class='center'>
                    <input type='submit' class='button' name='navigation' value='cancel'>
                    <input type='submit' class='button redButton' name='navigation' value='leave'>
                </div>
            </form>
        </div>
    </div>";

    echo $output;
}

session_start();
UserAccess::userIsLoggedIn();
UserAccess::userHasProjectSelected();
$database = new DatabaseAccess();
ProjectAccess::checkUserIsNotLeader($database);
$projectData = new ProjectData($_SESSION["projectID"], "", $database);

$cssFiles = array("style_main.css", "style_delete_project.css");
writeHeaderAndHeadline($cssFiles);
writeTopBanner();
writeDeleteProjectPage($projectData);
writeBottomBanner();
closeHtmlBody();
?>