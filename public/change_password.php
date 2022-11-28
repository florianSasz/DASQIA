<?php
/**
 * page to change the users password
 * @author: Florian Saß
 */

require_once "../components/html.php";
require_once "../components/string_utilities.php";
require_once "../components/access_user.php";
require_once "../components/verify_password.php";

function setNewPassword() {
    if (isset($_SESSION["passwordError"])) {
        $errorMessage = "<p class='redText center'>" . $_SESSION["passwordError"] . "</p>";
    } else {
        $errorMessage = "";
    }

    return
    "<div class='bubble changeNameBubble'>
        <p class='blueSubHeadline changeNameHeadline'>change password</p>
        <form action='../components/change_password_processing.php' method='post'>
            <div class='changeNameHeadline'>
                <p class='noMargin'>enter the new password: </p>
                <input type='password' class='' name='newPassword' size='30'>
            </div>
            <div class='changeNameHeadline'>
                <p class='noMargin'>repeat the new password:</p>
                <input type='password' class='' name='newPasswordRepeat' size='30'>
            </div>
            <div class='center'>
                <input type='submit' class='button' name='navigation' value='set new password'>
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
            $output .= setNewPassword();
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