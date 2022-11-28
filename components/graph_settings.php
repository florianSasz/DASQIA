<?php
/**
 * components to create the graph settings in the project page
 */
function writeGraphLabels(projectData $projectData) {
    return 
    "<div class='fitContent'>
        <p class='blackSubHeadline'><b>graph labels</b></p>
        <label for='xLabel'>x label:</label><br>
        <input type='text' name='xLabel' class='test' id='xLabel' value='" . $projectData->xLabel . "'
        <br><br><br>
        <label for='yLabel'>y label :</label><br>
        <input type='text' name='yLabel' id='yLabel' value='" . $projectData->yLabel . "'
        <br><br><br>
        <div class='center'>
            <button type='button' class='button' onclick='resetLabelsToDefault()'>reset to default</button>
        </div>
    </div>";
}

function writePngResolution(projectData  $projectData) {
    return 
    "<div class='fitContent'>
        <p class='blackSubHeadline'><b>.png resolution</b></p>
        <label for='xResolution'>width:</label><br>
        <input type='text' name='xResolution' id='xResolution' value='" . $projectData->xResolution . "'
        <br><br><br>
        <label for='yResolution'>height:</label><br>
        <input type='text' name='yResolution' id='yResolution' value='" . $projectData->yResolution . "'
        <br><br><br>
        <div class='center'>
            <button type='button' class='button' onclick='resetResolutionToDefault()'>reset to default</button>
        </div>
    </div>";
}

function writeFontSize(projectData $projectData) {
    return 
    "<div class='fitContent'>
        <p class='blackSubHeadline'><b>font size</b></p>
        <label for='axisFont'>axis font size:</label><br>
        <input type='text' name='axisFont' id='axisFont' value='" . $projectData->axisFontSize . "'
        <br><br><br>
        <label for='labelFont'>label font size:</label><br>
        <input type='text' name='labelFont' id='labelFont' value='" . $projectData->labelFontSize . "'
        <br><br><br>
        <div class='center'>
            <button type='button' class='button' onclick='resetFontSizeToDefault()'>reset to default</button>
        </div>
    </div>";
}

function writeVerticalGridDivision(projectData $projectData) {
    $checked = ($projectData->vGridDivision == 0) ? "checked" : "";
    return 
    "<div class='fitContent'>
        <p class='blackSubHeadline'><b>grid division</b></p>    
        <input type='checkbox' id='vGridDivisionAuto' name='vGridDivisionAuto' class='checkbox'" . $checked . ">
        <label for='vGridDivisionAuto'>auto division</label><br><br>    
        <label for='gridDivision'>vertical grid division:</label><br>
        <input type='text' name='gridDivision' id='gridDivision' value='" . $projectData->vGridDivision . "'
        <br><br><br>
        <div class='center'>
            <button type='button' class='button' onclick='resetVerticalGridDivision()'>reset to default</button>
        </div>
    </div>";
}

function writeGraphColor(projectData $projectData) {
    $checked = "";
    return 
    "<div class='fitContent'>
        <p class='blackSubHeadline'><b>graph color</b></p>    
        <label for='graphColor'>graph color:</label><br>
        <input type='color' name='graphColor' id='graphColor' value='" . $projectData->graphColor . "'
        <br><br><br>
        <div class='center'>
            <button type='button' class='button' onclick='resetGraphColor()'>reset to default</button>
        </div>
    </div>";
}

function writeGraphSettings(projectData $projectData) {
    return 
        "<form action='../components/graph_settings_processing.php' method='post'>
            <p class='blueSubHeadline noMargin'>graph settings</p>
            <table class='tableWidth'>
                <tr>
                    <td>"
                        . writeGraphLabels($projectData) .   
                    "</td>
                    <td>"
                        . writePngResolution($projectData) .
                    "</td>
                    <td>"
                        . writeFontSize($projectData) .
                    "</td>
                    <td valign='top'>"
                        . writeVerticalGridDivision($projectData) . 
                    "</td>
                    <td valign='top'>"
                        . writeGraphColor($projectData) .
                    "</td>
                </tr>
            </table>
            <br><br>
            <hr>    
            <div class='center'> 
                <input type='submit' value='save' name='navigationGraphSettings' class='button'>
            </div>
        </form>";
}
?>