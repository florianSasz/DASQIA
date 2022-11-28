<?php
/**
 * page to create an account
 */
require_once "../components/html.php";

function writeWorkspace() {
    if (isset($_SESSION["error"])) {
        $email = $_SESSION["email"];
        $name = $_SESSION["name"];
        $errorMessage = 
        "<div class='center'>
            <p class='redText center'>" . $_SESSION["error"] . "</p>
        </div>";
    } else {
        $email = "";
        $name = "";
        $errorMessage = "";
    }

    $output =
    "<div class='workspace'>
        <div class='centerVer'>
            <div class='bubble entranceBubble'>
                <p class='blueSubHeadline entranceHeadline noMargin'>register</p>

                <form action='../components/register_processing.php' method='post'>
                        <div class='inputContainer'>
                            <label for='email' >e-mail</label><br>
                            <input type='text' name='email' class='input' value='" . $email . "'>
                        </div>
                        <div class='inputContainer'>
                            <label for='name'>full name</label><br>
                            <input type='text' name='name' class='input' value='" . $name . "'>
                            <p>This name will be displayed in the documents. It can be changed later on.</p>
                        </div>
                        <div class='inputContainer'>
                            <label fpr='password'>password</label><br>
                            <input type='password' name='password' class='input'>
                        </div>
                        <div class='inputContainer'>
                            <label for='re_password'>repeat password</label><br>
                            <input type='password' name='re_password' class='input'>
                        </div>"
                        .
                        $errorMessage
                        .
                        "<div class='center submitButton'>
                            <input type='submit' name='navigation' value='register' class='button sameLine'>    
                            <input type='submit' name='navigation' value='cancel' class='button sameLine'>
                        </div>
                </form>
                
            </div>
        </div>
    </div>";
    
    echo $output;
}

session_start();
$cssFiles = array("style_main.css", "style_entrance.css");
writeHeaderAndHeadline($cssFiles);
writeTopBanner();
writeWorkspace();
writeBottomBanner();
closeHtmlBody();
session_destroy();
?>