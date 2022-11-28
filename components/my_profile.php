<?php
/**
 * component to display the "my profile" content on home.php and edit_aliases.php
 */
function writeMyProfile(string $currentPage, DatabaseAccess $database) {
    $buttonFunctionality = ($currentPage === "home") ? "" : "disabled"; // disables buttons when user is on edit_aliases.php
    $output =
    "<div class='bubble homeBubble myProfileBubble'>
        <p class='bubbleHeadline homeBubbleHeadlines'> my profile </p>
        <table>
            <tr>
                <td>
                    <p class='greyText profileInformationHeadlines'> name </p>
                    <p class='info'>" . $_SESSION["user"]["name"] . "</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class='greyText profileInformationHeadlines'> email </p>
                    <p class='info'>" . $_SESSION["user"]["email"] . "</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class='greyText profileInformationHeadlines'> aliases </p>";
                    if (!$_SESSION["user"]["aliases"]) {
                        $output .= "<p class='info'>No aliases added</p>";
                    } else {
                        $output .= "<p class='info lineBreak'>" . implode(", ", $_SESSION["user"]["aliases"]) . "</p>";
                    }

    $output .=  "</td>
                <td class='alignRight'>";

                        $hintText = "When importing data from MAXQDA the name written into the documents is, on default, your Windows account name.
                                    By adding your Windows account name as an alias this name will automaticly be replaced by your DASQIA account name.";
                        $output .= popUpHint($hintText, "sameLine");

    $output .=  "</td>    
            </tr>
            <tr>
                <td>
                    <form action='./edit_alias.php' class='profileInformationHeadlines'>
                        <input type='submit' class='button' value='edit aliases' " . $buttonFunctionality . ">
                    </form>
                </td>
            </tr>
            <tr>
                <td>
                    <p class='greyText profileInformationHeadlines'> member since </p>
                    <p class='info'>" . convertDateFormat($_SESSION['user']['registrationDate']) . "</p>
                </td>
                
                <td>
                    <p class='greyText profileInformationHeadlines'> number of projects </p>
                    <p class='info'>" . $database->getUserProjectsNumber($_SESSION["user"]["id"]) . "</p>
                </td>
                
            </tr>
            <tr>
                <td>
                    <form action='./change_name.php' method='post' class='profileInformationHeadlines'>
                        <input type='submit' class='button' value='change name' " . $buttonFunctionality . ">
                    </form>
                </td>
                <td>
                    <form action='./change_password.php' class='profileInformationHeadlines'>
                        <input type='submit' class='button' value='change password' " . $buttonFunctionality . ">
                    </form>
                </td>
            </tr>
        </table>
    </div>";
    
    return $output;
}
?>