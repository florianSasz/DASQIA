"use strict";

function enableGraphSettings(parent) {
    parent.innerHTML = document.getElementById("graphSettingsHTML").value;
    const vGridCheckbox = document.getElementById("vGridDivisionAuto");
    const vGridInput = document.getElementById("gridDivision");

    vGridCheckbox.addEventListener("change", function() {
        if (vGridCheckbox.checked) {
            vGridInput.disabled = true;
        } else {
            vGridInput.disabled = false;
        }
    });

    if (vGridCheckbox.checked) {
        vGridInput.disabled = true;
    }
}

function disableGraphSettings(parent) {
    while (parent.firstChild) {
        parent.removeChild(parent.lastChild);
    }
}

function toggleGraphSettings() {
    const parent = document.getElementById("graphSettings");
    parent.classList.toggle("graphSettings");
    if (parent.children.length == 0) {
        enableGraphSettings(parent);
    } else {
        disableGraphSettings(parent);
    }
}

function setupDownload() {
    // add id to each graph
    let numberGraphs = 0;
    const graphs = document.querySelectorAll("svg");
    for (let i = 0; i < graphs.length; i++) {
        graphs[i].id = "svg_" + i;
        numberGraphs++;
    }
    // add eventlisteners to each download button
    const downloadButtons = document.getElementsByClassName("downloadButton");
    for (let i = 0; i < downloadButtons.length; i++) {
        if (downloadButtons[i].id.startsWith("downloadSVG_")) {
            downloadButtons[i].addEventListener('click', function(){ downloadSVGAsText(downloadButtons[i].id.split("_")[1], false); });

        } else if (downloadButtons[i].id.startsWith("downloadPNG_")) {      
            downloadButtons[i].addEventListener('click', function(){ downloadSVGAsPNG(downloadButtons[i].id.split("_")[1]); });
            
        } else if (downloadButtons[i].id.startsWith("downloadCSV_")) {
            downloadButtons[i].addEventListener('click', function(){ downloadSingleCSV(downloadButtons[i].id.split("_")[1]); });
        }
    }
    return numberGraphs;
}

function resetLabelsToDefault() {
    document.getElementById("xLabel").value = "document";
    document.getElementById("yLabel").value = "number of codes";
}

function resetResolutionToDefault() {
    document.getElementById("xResolution").value = "1000";
    document.getElementById("yResolution").value = "800";
}

function resetFontSizeToDefault() {
    document.getElementById("axisFont").value = "13";
    document.getElementById("labelFont").value = "13";
}

function resetVerticalGridDivision() {
    document.getElementById("gridDivision").value = "0";
}

function resetGraphColor() {
    document.getElementById("graphColor").value = "#5773FF";
}

const numberGraphs = setupDownload();
const xResolution = document.getElementById("initialXResolution").value;
const yResolution = document.getElementById("initialYResolution").value;