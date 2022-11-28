<?php
/**
 * side bar which shows the team members of a project and the back button
 */

function writeTeamMembers($teamMembers) {
    $output = "";
    foreach ($teamMembers as $member) {
        $output .= "<p class='teamMemberNames'>" . $member["name"] . "</p>";
        if (array_key_exists("email", $member)) {
            $output .= "<p class='greyText teamMemberEmails'>" . $member["email"] . "</p>";
        }
    }
    return $output;
}

function writeBackHomeButton() {
    $output = 
    "<form action='./home.php' class='center'>
        <input type='submit' value='back home' class='button pageBubbles'>
    </form>";
    return $output;
}

function writeBackToProjectButton() {
    $output = 
    "<form action='./project.php' class='center'>
        <input type='submit' value='back to project' class='button pageBubbles'>
    </form>";
    return $output;
}

function writeSideBar(array $teamMembers, string $backType) {
    $output = 
        "<div>";
            if ($backType === "back home") {
                $output .= writeBackHomeButton();
            } else if ($backType === "back to project"){
                $output .= writeBackToProjectButton();
            }
            
    $output .=     
            "<div class='bubble teamMembersBubble pageBubbles'>
                <p class='bubbleHeadline center teamMembersHeadline'> team members </p>";
                $output .= writeTeamMembers($teamMembers);
    $output .=     
            "</div>
        </div>";
    
    return $output;
}
?>