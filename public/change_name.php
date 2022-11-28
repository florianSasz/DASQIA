<?php
/**
 * page to change a users name 
 */

require_once "../components/html.php";
require_once "../components/string_utilities.php";
require_once "../components/access_user.php";
require_once "../components/verify_password.php";

function setNewName() {
    if (isset($_SESSION["nameToShort"])) {
        $errorMessage = "<p class='redText center'>The new name is to short.</p>";
    } else {
        $errorMessage = "";
    }

    return
    "<div class='bubble changeNameBubble'>
        <p class='blueSubHeadline changeNameHeadline'>change name</p>
        <form action='../components/change_name_processing.php' method='post'>
            <div>
                <p class='sameLine'>enter a new name:</p>
                <input type='text' class='sameLine' name='newName'>
            </div>
            <div class='center'>
                <p><b> Note: This will not automatically change<br>your name in existing documents. </b></p>
                <input type='submit' class='button' name='navigation' value='change name'>
                <input type='submit' class='button' name='navigation' value='cancel'>
            </div>"
            .
            $errorMessage
            .
        "</form>
    </div>";
}

function writeWorkspace(verifyPassword $verify) {
    $output =
    "<div class='workspace'>";
            
        if (!$verify->isVerified()) {
            $output .= $verify->verifyPassword();
        } else {
            $output .= setNewName();
        }

    $output .=
    "</div>";

    echo $output;
}

session_start();
UserAccess::userIsLoggedIn();
$verify = new verifyPassword(verifyPassword::getCurrentURL());

$cssFiles = array("style_main.css", "style_change_name_pw.css", "style_verify_password.css");
writeHeaderAndHeadline($cssFiles);
writeTopBanner();
writeWorkspace($verify);
writeBottomBanner();
closeHtmlBody();
?>