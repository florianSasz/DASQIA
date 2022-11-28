<?php
/**
 * login page
 */
require_once "../components/html.php";

function writeLoginSpace() {
    $output = 
    "<div class='workspace' id='outer'>
        <div class='centerVer'>
            <div class='bubble entranceBubble'>
                <p class='blueSubHeadline noMargin entranceHeadline'>login</p>
                <form action='../components/login_processing.php' method='post'>
                    <div class='inputContainer'>
                        <label for='email'>e-mail</label><br>
                        <input name='email' type='text' class='input'>
                    </div>
                    <div class='inputContainer'>
                        <label for='password'>password</label><br>
                        <input name='password' type='password' class='input'>
                    </div>
                    <div class='center submitButton'>
                        <input type='submit' class='button' value='login'>
                    </div>
                </form>
            </div>
            <br>";

            if (isset($_SESSION["sendResetEmail"])) {
                $output .= 
                "<div class='bubble entranceBubble center infoText'>
                    <p class='noMargin'>We sent an e-mail to reset your password if the email was correct.</p>
                </div><br>";
            }

            if (isset($_SESSION["registration"])) {
                $output .= 
                "<div class='bubble entranceBubble center infoText'>
                    <p class='greenText noMargin'>Registration succesful</p>
                </div><br>";
            }

            if (isset($_SESSION["failedLogin"])) {
                $output .= 
                "<div class='bubble entranceBubble center infoText'>
                    <p class='redText noMargin'>e-mail or password incorrect</p>
                </div><br>";
            }

            if (isset($_SESSION["newPassword"])) {
                $output .= 
                "<div class='bubble entranceBubble center infoText'>
                    <p class='greenText noMargin'>The new password was set.</p>
                </div><br>";
            }

    $output .=
            "<div class='bubble entranceBubble twoColumngrid center entranceSubBubble'>
                <p>no account?</p>
                <form action='./register.php' method='post'>
                    <input type='submit' class='button registerButton' value='register'>
                </form>
            </div>
            <br>    
            <div class='bubble entranceBubble twoColumngrid center entranceSubBubble'>
                <p>forgot your password?</p>
                <form action='./reset_password.php' method='post'>
                    <input type='submit' class='button resetPasswordButton' value='reset password'>
                </form>
            </div>
        </div>
    </div>";

    echo $output;
}

session_start();
unset($_SESSION["user"]);

$cssFiles = array("style_main.css", "style_entrance.css", "style_login.css");
writeHeaderAndHeadline($cssFiles);
writeTopBanner();
writeLoginSpace();
writeBottomBanner();
closeHtmlBody();
session_destroy();
?>