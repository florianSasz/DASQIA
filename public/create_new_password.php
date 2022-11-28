<?php
/**
 * page to send an email to reset a users password 
 */
require_once "../components/html.php";
require_once "../components/redirect_user.php";

function writeworkspace(string $selector, string $validator) {
    $output = 
    "<div class='workspace' id='outer'>
        <div class='centerVer'>
            <div class='bubble entranceBubble'>
                <p class='blueSubHeadline noMargin entranceHeadline'>create new password</p>
                <form action='../components/create_new_password_processing.php' method='post'>
                    <input type='hidden' name='selector' value='" . $selector . "'>
                    <input type='hidden' name='validator' value='" . $validator . "'>
                    <div class='inputContainer'>
                        <label for='new_password'>new password</label><br>
                        <input name='new_password' type='password' class='input'>
                    </div>
                    <div class='inputContainer'>
                        <label for='new_password_re'>repeat  new password</label><br>
                        <input name='new_password_re' type='password' class='input'>
                    </div>";
                        if (isset($_GET["newPassword"])) {
                            if ($_GET["newPassword"] === "unequal") {
                                $output .= "<p class='redText center'>Passwords are not equal.</p>";

                            } else if ($_GET["newPassword"] === "toshort") {
                                $output .= "<p class='redText center'>Password is to short. At least 7 characters are required.</p>";
                            }

                        } else if (isset($_GET["token"])) {
                            if ($_GET["token"] === "wrong") {
                                $output .= "<p class='redText center'>Something went wrong. Try to submit a new request for a password reset email.</p>";
                            }
                        }
    $output .=      "<div class='submitButton center'>
                        <input type='submit' class='button loginElements textElements' name='navigation' value='reset'>
                        <input type='submit' class='button loginElements textElements' name='navigation' value='cancel'>
                    </div>
                </form>
            </div>
        </div>
    </div>";

    echo $output;
}

$selector = $_GET["selector"];
$validator = $_GET["validator"];

if (empty($selector) || empty($validator)) {
    RedirectUser::returnToLoginPage();
} else {
    // check for valid hexadecimal value
    if (ctype_xdigit($selector) === false && ctype_xdigit($validator) === false) { 
        RedirectUser::returnToLoginPage();
    }
}

session_start();
$cssFiles = array("style_main.css", "style_entrance.css", "style_login.css");
writeHeaderAndHeadline($cssFiles);
writeTopBanner();
writeworkspace($selector, $validator);
writeBottomBanner();
closeHtmlBody();
session_destroy();
?>