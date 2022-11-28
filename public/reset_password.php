<?php
/**
 * page to send an email to reset a users password 
 */
require_once "../components/html.php";

function writeworkspace() {
    if (isset($_SESSION["email"])) {
        $email = $_SESSION["email"];
        $errorMessage = 
        "<p class='redText center'>Please enter a valid e-mail.</p>";
    } else {
        $email = "";
        $errorMessage = "";
    }

    $output = 
    "<div class='workspace' id='outer'>
        <div class='centerVer'>
            <div class='bubble entranceBubble'>
                <p class='blueSubHeadline noMargin entranceHeadline'>reset password</p>
                <form action='../components/reset_password_processing.php' method='post'>
                    <div class='inputContainer'>
                        <label for='email'>e-mail</label><br>
                        <input name='email' type='text' class='input' value='" . $email . "'>
                    </div>"
                    .
                    $errorMessage
                    .
                    "<div class='submitButton center'>
                        <input type='submit' class='button loginElements textElements' name='navigation' value='reset'>
                        <input type='submit' class='button loginElements textElements' name='navigation' value='cancel'>
                    </div>
                </form>
            </div>
        </div>
    </div>";

    echo $output;
}

session_start();
$cssFiles = array("style_main.css", "style_entrance.css", "style_login.css");
writeHeaderAndHeadline($cssFiles);
writeTopBanner();
writeworkspace();
writeBottomBanner();
closeHtmlBody();
session_destroy();
?>