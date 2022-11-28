<?php
/**
 * page to create a new project
 */
require_once "../components/project_settings.php";
require_once "../components/html.php";
require_once "../components/access_user.php";

class Project {
    public string $title;
    public string $description;
    public array $newRegisteredMembers;
    public array $newShadowMembers;

    function __construct(string $type) { // type = new or previouseAttempt
        switch ($type) {
            case "new":
                $this->title = "";
                $this->description = "";
                $this->newRegisteredMembers = array();
                $this->newShadowMembers = array();
                break;
            case "previouseAttempt":
                $this->title = $_SESSION["previouseAttempt"]["title"];
                $this->description = $_SESSION["previouseAttempt"]["description"];
                $this->newRegisteredMembers = $_SESSION["previouseAttempt"]["newMembers"]["registered"];
                $this->newShadowMembers = $_SESSION["previouseAttempt"]["newMembers"]["shadow"];
        }
    }

    private function getArrayToReplaceShadowMemberAliases() {
        // array to replace exsiting aliases with newly set in previouse attempt
        if ($_SESSION["previouseAttempt"]["editShadowAlias"]) {
            $this->editAliasShadowMembers = array();
            foreach ($_SESSION["previouseAttempt"]["editShadowAlias"] as $shadowUser) {
                $this->editAliasShadowMembers[$shadowUser["name"]] = $shadowUser["aliases"];
            } 
        }   
    }
}

function writeSideBar() {
    return 
    "<div class='leftSideBar'>
        <form action='./home.php' class='center'>
            <input type='submit' class='button' value='back home'>
        </form>
    </div>";
}

function writeWorkspace(Project $project) {
    $output =
    "<div class='workspace'>
        <table>
            <tr>
                <td valign='top'>"
                    . writeSideBar() .
                "</td>
                <td>"
                    . openProjectSettings("new");

                    if (isset($_SESSION["error"])) {
                        $output .= displayErrors($_SESSION["error"], $_SESSION["errorInformation"]);
                    }

                    $output .=
                      projectTitle($project->title) 
                    . projectDescription($project->description)
                    . projectMembers($_SESSION["user"]["email"])
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

if (isset($_SESSION["previouseAttempt"])) {
    $project = new Project("previouseAttempt");
} else {
    $project = new Project("new");
}

$cssFiles = array("style_main.css", "style_project_settings.css");
$jsFiles = array("popup.js");
$jsModules = array("project_settings_new.js");
writeHeaderAndHeadline($cssFiles, $jsFiles, $jsModules);
writeTopBanner();
writeWorkspace($project);
writeBottomBanner();
writeDataPreviouseAttemptForNewMembers($project);
closeHtmlBody();

unset($_SESSION["previouseAttempt"]);
unset($_SESSION["error"]);
unset($_SESSION["errorInformation"]);
?>