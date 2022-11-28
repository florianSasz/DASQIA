<?php
/**
 * components to build up the new_project.php and edit_project.php page
 */
require_once "../components/popup.php";

function openProjectSettings(string $type) {
    return
    "<div class='bubble projectSettingsBubble'>
        <p class='bubbleHeadline noMargin'>" . $type . " project</p>
        <br>
        <form action='../components/" . $type . "_project_processing.php' method='post' id='submitForm'>";
}

function closeProjectSettings() {
    return
        "</form>
    </div>";
}

function displayErrors(string $errorMessage, array $errors) {
    $output = 
    "<div>
        <p class='redText'>" . $errorMessage . "</p>
        <ul class='redText'>";

            foreach ($errors as $error) {
                $output .= "<li>" . $error . "</li>";
            }

    $output .= 
        "</ul>
    </div>";
    return $output;
}

function projectTitle(string $title) {    
    return 
    "<div>
        <p class='sameLine'>title:</p>
        <input type='text' class='sameLine' name='title' size='90' value='" . $title . "'>
    </div>";
}

function projectDescription(string $description) {
    return 
    "<div>
        <p>additional information:</p>
        <textarea name='description' rows='6' cols='90'>" . $description . "</textarea>
    </div>";
}

function projectMembers(string $projectLeader) {
    return 
    "<div>  
        <p class='blueSubHeadline'>members</p>
        <div>
            <p class='sameLine'>email:</p>
            <input type='text' id='emailToAdd' class='sameLine' size='40'>
            <button type='button' id='addRegisteredMember' class='sameLine button'> add member </button>
            <div id='registeredMembers' class='projectMembersContainer'>
                <div>
                    <p class='sameLine'>project leader</p>
                    <p class='sameLine leaderEmail' id='projectLeader'>" . $projectLeader . "
                </div>
            </div>
        </div>
    </div>";
}

function projectShadowMembers() {
    $hintText = 
    "Shadow members are pseudo user that have participed in the project but do not have an account on DASQIA.
    By adding their name here you are able to assign their name to documents.";

    return 
    "<div>
        <p class='sameLine blackSubHeadline'> shadow members</p>"

        . popUpHint($hintText, "sameLine hintPosition") .

        "<div>
            <p class='sameLine'>name:</p>
            <input type='text' id='shadowMemberToAdd' class='sameLine' size='40'>
            <button type='button' id='addShadowMember' class='sameLine button'> add shadow member </button>
        </div>
        <div id='shadowMembers' class='projectMembersContainer'></div>
    </div>";
}

function navigationButtons() {
    return 
    "<br><br>
    <div id='newLeaderNote' class='center'></div>
    <div id='submitData'></div>
    <div class='center'>
        <button type='button' class='button' id='save'>save</button>
        <button type='button' class='button' id='cancel'>cancel</button>
    </div>";
}

function writeDataPreviouseAttemptForNewMembers(Project $project) {
    
    $newRegisteredContainer = "<div id='newRegistered'>";
    for ($i = 0; $i < count($project->newRegisteredMembers); $i++) {
        if (gettype($project->newRegisteredMembers[$i]) === "array") {
            $project->newRegisteredMembers[$i] = $project->newRegisteredMembers[$i]["email"];
        }
        $newRegisteredContainer .= "<input type='hidden' name='newRegisteredMember' value='" . $project->newRegisteredMembers[$i] . "'>";
    }
    $newRegisteredContainer .= "</div>";

    $newShadowContainer = "<div id='newShadow'>";
    foreach($project->newShadowMembers as $member) {
        $newShadowContainer .= "<input type='hidden' name='newShadowMember' value='" . $member["name"] . "%%" . implode("%%", $member["aliases"]) . "'>";
    }
    $newShadowContainer .= "</div>";

    echo $newRegisteredContainer;
    echo $newShadowContainer;
}
?>