<?php
/**
 * home page of a user
 */
require_once "../components/html.php";
require_once "../components/database.php";
require_once "../components/string_utilities.php";
require_once "../components/access_user.php";
require_once "../components/popup.php";
require_once "../components/my_profile.php";

function writeProjects(array $projects) {
    if (count($projects) > 0) {
        $output = "";
        foreach ($projects as $project) {
            $members = implode(", ", $project["members"]);
            $output .= 
                "<form action='./project.php' method='post'>
                    <button name='projectID' value='" . $project["projectID"] . "' type='submit' class='projectButton'> 
                        <p class='projectButtonText projectButtonTitle'>" . $project["title"] . "</p>
                        <p class='greyText projectButtonText'>" . $members . "</p>
                    </button>
                </form>";
        }
    } else {
        $output = "There are no projects.";
    }
    return $output;
}

function writeHomescreen(array $projectsOngoing, array $projectsFinished, DatabaseAccess $database) {
    $output =
    "<div class='workspace'>
        <table>
            <tr>
                <td valign='top'>"
                    . writeMyProfile("home", $database) .
                "</td>
                <td valign='top'>
                    <div class='bubble homeBubble myProjectBubble'>
                        <div>
                            <p class='bubbleHeadline homeBubbleHeadlines sameLine'>my projects</p>
                            <form action='new_project.php' class='sameLine'>
                                <input type='submit' class='button newProjectButton' value='new project'>
                            </form>
                        </div>
                        <div>
                            <p class='blueSubHeadline'> ongoing </p>"
                             . writeProjects($projectsOngoing) .
                        "</div>
                        <div>
                            <p class='blueSubHeadline'> finished </p>" 
                            . writeProjects($projectsFinished) .
                        "</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>";
    
    echo $output;
}

function getAllProjectMembers(array &$userProjects, DatabaseAccess $database) {
    // get all project members and add them to the $userProjects array
    foreach($userProjects as $i=>$project) {
        $members = array_merge($database->getProjectMembers($project["projectID"]), $database->getProjectShadowMembers($project["projectID"]));
        foreach ($members as $member) {
            $userProjects[$i]["members"][] = $member["name"];
        }
    }
}

function sortProjects(array $userProjects) {
    $projects = array("finished" => array(), "ongoing" => array());
    foreach ($userProjects as $project) {
        if ($project["finished"]) {
            $projects["finished"][] = $project;
        } else {
            $projects["ongoing"][] = $project;
        }
    }
    return $projects;
}

session_start();
UserAccess::userIsLoggedIn();

unset($_SESSION["projectID"]); // when coming from a project 
unset($_SESSION["passwordVerfied"]); // from change name/password
unset($_SESSION["nameToShort"]); // from change name
unset($_SESSION["passwordError"]); // from change password

$database = new DatabaseAccess();
$userProjects = $database->getUserProjects($_SESSION["user"]["id"]);
getAllProjectMembers($userProjects, $database);
$projects = sortProjects($userProjects);

$cssFiles = array("style_main.css", "style_home.css");
$jsFiles = array("popup.js");
writeHeaderAndHeadline($cssFiles, $jsFiles);
writeTopBanner();
writeHomescreen($projects["ongoing"], $projects["finished"], $database);
writeBottomBanner();
closeHtmlBody();
?>