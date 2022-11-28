<?php
/**
 * component to display a clickable popup hint
 */
$popupIndex = 0;
function popUpHint(string $popupText, string $cssClass="") { // 'popup.js' file required for this function
    global $popupIndex;
    return
    "<div class='popup " . $cssClass ."'>
        <img src='./images/info.svg' alt='info' class='infoButton' onclick='togglePopUp({$popupIndex})'>
        <span class='popuptext' id='popup_" . $popupIndex++ . "'>". $popupText ."</span>
    </div>";
}
?>