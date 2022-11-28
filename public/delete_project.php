<?php
/**
 * page to delete a project 
 */
require_once "../components/html.php";
require_once "../components/database.php";
require_once "../components/access_user.php";
require_once "../components/access_project.php";
require_once "../components/projectData.php";

function writeDeleteProjectPage(ProjectData $projectData) {
    $output = 
    "<div class='workspace'>
        <div class='bubble delteProjectBubble'>
            <p class='bubbleHeadline headline'> delete project </p>
            <br><br>
            <form action='../components/delete_project_processing.php' method='post'>
                <p class='center'> 
                    Are you sure that you want to delete the project <b> ". $projectData->title ." </b> for all members?
                    <br>
                    If you just want to leave this project, you just need to assign someone else as the project leader first.
                </p>
                <p class='redText center'><b> This process cannot be undone. </b></p>
                <div class='center'>    
                    <input type='submit' class='button' name='navigation' value='cancel'>
                    <input type='submit' class='button redButton' name='navigation' value='delete'>
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
ProjectAccess::checkUserIsLeader($database);
$projectData = new ProjectData($_SESSION["projectID"], "", $database);

$cssFiles = array("style_main.css", "style_delete_project.css");
writeHeaderAndHeadline($cssFiles);
writeTopBanner();
writeDeleteProjectPage($projectData);
writeBottomBanner();
closeHtmlBody();
?>