<?php
/**
 * components to create HTML header and body as well as top and bottom banner
 */
function writeHeaderAndHeadline(array $cssFiles=null, array $jsFiles=null, array $jsModules=null, array $externalLibs=null) {
    $header =
         "<!DOCTYPE html>
          <html lang=\"de\">
          <head>
          <meta charset=\"utf-8\">
          <title>DASQIA</title>
          <link rel='icon' type='image/x-icon' href='./images/favi/favicon.ico'>";
    if ($cssFiles) {
        foreach ($cssFiles as $file) {
            $header .= 
                "<link rel=\"stylesheet\" type= \"text/css\" href=./css/" . $file . ">";
        }
    }
    if ($jsFiles) {
        foreach ($jsFiles as $file) {
            $header .= 
                "<script type='text/javascript' src='./javascript/" . $file . "'defer></script>";
        }
    }
    if ($jsModules) {
        foreach ($jsModules as $module) {
            $header .=
                "<script type='module' src='./javascript/" . $module . "'defer></script>";
        }
    }
    if ($externalLibs) {
        foreach ($externalLibs as $lib) {
            $header .= 
                "<script src='" . $lib . "'defer></script>";
        }
    }
    $header .= 
         "</head>
          <body>";
    echo $header;
}

function writeTopBanner() {
    $output = 
    "<div class='topBanner'>
        <img src='./images/logo.svg' alt='logo' width='180px'>";
    if (isset($_SESSION["user"])) {
        $output .=
        "<div class='logoutButton'>
            <p class='sameLine'>" . $_SESSION["user"]["email"] . "</p>
            <form action='./index.php' class='sameLine'>
                <input type='submit' class='button' value='logout'>
            </form>
        </div>"; 
    }
    $output .=    
    "</div>";
    echo $output;
}

function writeBottomBanner() {
    echo 
    "<div class='bottomBanner'>
        <p>Hier kann auch noch etwas stehen.</p>
    </div>";
}

function closeHtmlBody() {
    echo "</body></html>";
}
?>