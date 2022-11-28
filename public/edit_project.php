<?php
/**
 * page to edit a project
 */
require_once "../components/project_settings.php";
require_once "../components/html.php";
require_once "../components/access_user.php";
require_once "../components/database.php";
require_once "../components/projectData.php";

class Project { //  TODO: make it a dervied class from Base class in new_project.php
    public string $type;
    public string $title;
    public string $description;
    public bool $status;
    public string $leader;
    public bool $currentUserIsLeader;

    function __construct(string $type, DatabaseAccess $database) {
        $this->type = $type;
        $projectData = new projectData($_SESSION["projectID"], "", $database);
        $this->leader = $database->getProjectLeader($_SESSION["projectID"])["email"];
        $this->currentUserIsLeader = ($this->leader == $_SESSION["user"]["email"]) ? true : false;
        $this->registeredMembers = array();
        $this->shadowMembers = array();
        switch ($type) {
            case "database":
                $this->title = $projectData->title;
                $this->description = $projectData->description;
                $this->status = $projectData->finished;
                $this->defineDatabaseTeamMembers($projectData);
                break;

            case "previouseAttempt":
                $this->title = $_SESSION["previouseAttempt"]["title"];
                $this->description = $_SESSION["previouseAttempt"]["description"];
                $this->status = filter_var($_SESSION["previouseAttempt"]["status"], FILTER_VALIDATE_BOOLEAN);
                $this->definePreviouseAttemptTeamMembers($projectData);
                break;
        }
    }

    private function defineDatabaseTeamMembers(projectData $projectData) {
        $temp = $projectData->teamMembers;
        foreach ($temp as $member) {
            if (isset($member["email"])) {
                $this->registeredMembers[] = $member;
            } else {
                $this->shadowMembers[] = $member;
            }
        }
    }

    private function definePreviouseAttemptTeamMembers(projectData $projectData) {
        $this->defineDatabaseTeamMembers($projectData);
        
        $this->newRegisteredMembers = $_SESSION["previouseAttempt"]["newMembers"]["registered"];
        $this->newShadowMembers = $_SESSION["previouseAttempt"]["newMembers"]["shadow"];

        $this->removeRegisteredMembers = $_SESSION["previouseAttempt"]["removeMembers"]["registered"];
        $this->removeShadowMembers = $_SESSION["previouseAttempt"]["removeMembers"]["shadow"];
        
        $this->newLeader = ($_SESSION["previouseAttempt"]["leader"] != $this->leader) ? $_SESSION["previouseAttempt"]["leader"] : null;

        // array to replace exsiting aliases with newly set in previouse attempt
        if ($_SESSION["previouseAttempt"]["editShadowAlias"]) {
            $this->editAliasShadowMembers = array();
            foreach ($_SESSION["previouseAttempt"]["editShadowAlias"] as $shadowUser) {
                $this->editAliasShadowMembers[$shadowUser["name"]] = $shadowUser["aliases"];
            } 
        }
    }
}

function writeSideBar(bool $currentUserIsLeader) {
    $output = 
    "<div class='center leftSideBar'>
        <form action='./project.php'>
            <input type='submit' class='button' value='back to project'>
        </form>
        <br>";
        
        if ($currentUserIsLeader) {
            $output .= 
            "<form action='./delete_project.php' method='post'>
                <input type='submit' class='button redButton exitProject' value='delete project'>
            </form>";
        } else {
            $output .=
            "<form action='./leave_project.php' method='post'>
                <input type='submit' class='button redButton exitProject' value='leave project'>
            </form>";   
        }
     
    $output .=
    "</div>";
    
    return $output;
}

function writeStatus(bool $projectStatus) {
    $checked = ($projectStatus) ? "checked" : "";
    $hintText = "When a project is finished the data within a project cannot be edited anymore. But all the export methods and the analysis will still be available.";
    return  
    "<div>
        <p class='blueSubHeadline sameLine'>status</p>"
        . popUpHint($hintText, "sameLine hintPosition") .
    "</div>
    <input type='checkbox' name='finished' class='checkbox' id='statusCheckbox' " . $checked . ">
    <label for='finished'> mark project as finished </label>";
} 

function writeDataDatabase(Project $project, DatabaseAccess $database) {
    $registeredMemebersContainer = "<div id='existingRegisteredMembers'>";
    foreach ($project->registeredMembers as $member) {
        $aliases = $database->getAliases($member["id"], "user");
        $registeredMemebersContainer .=
        "<input type='hidden' name='registeredMember' value='" . $member["email"] . "%%" . implode("%%", $aliases) . "'>";
    }
    $registeredMemebersContainer .= "</div>";
    
    $shadowMemebersContainer = "<div id='existingShadowMembers'>";
    foreach ($project->shadowMembers as $member) {

        if (isset($project->editAliasShadowMembers[$member["name"]])) {
            $aliases = $project->editAliasShadowMembers[$member["name"]]; // previouse attempt
        } else {
            $aliases = $database->getAliases($member["id"], "shadowuser");
        }
        $shadowMemebersContainer .=
            "<input type='hidden' name='shadowMember' value='" . $member["name"] . "%%" . implode("%%", $aliases) . "'>";
    }
    $shadowMemebersContainer .= "</div>";

    echo $registeredMemebersContainer;
    echo $shadowMemebersContainer;
}

function writeDataPreviouseAttempt(Project $project, DatabaseAccess $database) {
    writeDataDatabase($project, $database);
    
    writeDataPreviouseAttemptForNewMembers($project);

    $removeRegisteredContainer = "<div id='removeRegistered'>";
    foreach($project->removeRegisteredMembers as $member) {
        $removeRegisteredContainer .= "<input type='hidden' name='removeRegisteredMember' value='" . $member . "'>";
    }
    $removeRegisteredContainer .= "</div>";

    $removeShadowContainer = "<div id='removeShadow'>";
    foreach($project->removeShadowMembers as $member) {
        $removeShadowContainer .= "<input type='hidden' name='removeShadowMembers' value='" . $member . "'>";
    }
    $removeShadowContainer .= "</div>";
    
    if ($project->newLeader) {
        echo "<input type='hidden' name='newLeader' id='newLeader' value='" . $project->newLeader . "'>";
    }

    if ($project->editAliasShadowMembers) {
        echo "<input type='hidden' name='editedAliases' id='editedAliases' value='" . implode("%%", array_keys($project->editAliasShadowMembers)) . "'>";
    }

    echo $removeRegisteredContainer;
    echo $removeShadowContainer;
}

function writeWorkspace(Project $project) {    
    $output =
    "<div class='workspace'>
        <table>
            <tr>
                <td valign='top'>"
                    . writeSideBar($project->currentUserIsLeader) .
                "</td>
                <td>"
                    . openProjectSettings("edit");

                    if (isset($_SESSION["error"])) {
                        $output .= displayErrors($_SESSION["error"], $_SESSION["errorInformation"]);
                    }
                    
                    $output .=
                      projectTitle($project->title)
                    . projectDescription($project->description);
                    
                    if ($project->currentUserIsLeader) {
                        $output .= writeStatus($project->status);                    
                    }

                    $output .=
                      projectMembers($project->leader)
                    . projectShadowMembers() 
                    . navigationButtons()
                    . closeProjectSettings() .
                "</td>
            </tr>
        </table>
    </div>";

    echo $output;
}

session_start();
UserAccess::userIsLoggedIn();
UserAccess::userHasProjectSelected();

$database = new DatabaseAccess();
if (isset($_SESSION["previouseAttempt"])) {
    $project = new Project("previouseAttempt", $database);
} else {
    $project = new Project("database", $database);
}

$cssFiles = array("style_main.css", "style_project_settings.css");
$jsFiles = array("popup.js");
if ($project->currentUserIsLeader) {
    $jsModules = array("project_settings_leader.js");
} else {
    $jsModules = array("project_settings_non_leader.js");
}

writeHeaderAndHeadline($cssFiles, $jsFiles, $jsModules);
writeTopBanner();
writeWorkspace($project);
writeBottomBanner();

if ($project->type == "database") {
    writeDataDatabase($project, $database);
} else {
    writeDataPreviouseAttempt($project, $database);
}
closeHtmlBody();

unset($_SESSION["previouseAttempt"]);
unset($_SESSION["error"]);
unset($_SESSION["errorInformation"]);
?>