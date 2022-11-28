<?php
/**
 * page to edit the aliases of a registered user
 */
require_once "../components/my_profile.php";
require_once "../components/html.php";
require_once "../components/access_user.php";
require_once "../components/popup.php";
require_once "../components/string_utilities.php";
require_once "../components/database.php";

function writeEditAlias() {
    $output =
    "<div class='workspace'>
        <table>
            <tr>
                <td valign='top'>"
                    . writeMyProfile("", new DatabaseAccess()) .
                "</td>
                <td valign='top'>
                    <div class='bubble homeBubble'>
                        <p class='bubbleHeadline homeBubbleHeadline'>my aliases</p>
                        <div>
                            <label for='newAliasInput'>new alias:</label>
                            <input type='text' id='newAliasInput' name='newAliasInput'>
                            <button type='button' id='addNewAliasButton' class='button'> add new alias</button>
                        </div>";
                            if (isset($_SESSION["error"])) {
                                $output .= "<p class='redText center'>" . $_SESSION["error"] . "</p>";
                                unset($_SESSION["error"]);
                            }
    $output .=          "<br>
                        <div>
                            <div id='currentAliasesSpace'>
                                <p class='blackSubHeadline'>current aliases</p>
                            </div>
                            <br>
                            <div id='newAliasesSpace'></div>
                        </div>
                        <div class='center navigation'>
                            <form action='../components/edit_alias_processing.php' id='submitForm' method='post'></form>
                                <button type='button' id='saveSubmit' class='button'>save</button>
                                <button type='button' id='cancelSubmit' class='button'>cancel</button>
                        </div>
                    <div>
                </td>
            <tr>
        </table>
    </div>";
    echo $output;
}

function writeAliasData() {
    $output = "<div id='aliases'>";
    foreach ($_SESSION["user"]["aliases"] as $i=>$alias) {
        $output .= "<input type='hidden' id='alias_" . $i . "' value='" . $alias . "'>";
    }
    if (isset($_SESSION["errorInformation"])) {
        foreach ($_SESSION["errorInformation"]["newAliases"] as $i=>$information) {
            $output .= "<input type='hidden' id='newAlias_" . $i . "' value='" . $information . "'>";
        }
        foreach ($_SESSION["errorInformation"]["aliasesToRemove"] as $i=>$information) {
            $output .= "<input type='hidden' id='removeAlias_" . $i . "' value='" . $information . "'>";
        }
        unset($_SESSION["errorInformation"]);
    } 
    $output .= "</div>";
    echo $output;
}

session_start();
UserAccess::userIsLoggedIn();

$cssFiles = array("style_main.css", "style_home.css", "style_edit_alias.css");
$jsFiles = array("popup.js", "edit_alias.js");
writeHeaderAndHeadline($cssFiles, $jsFiles);
writeTopBanner();
writeEditAlias();
writeAliasData();   
writeBottomBanner();
closeHtmlBody();
?>