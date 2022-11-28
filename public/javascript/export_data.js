"use strict";
// setup for downloads in graph.js

function createFileName(fileType, index=null, name=null) {
    if (index || index === 0) {
        return "[" + new Date().toLocaleString() + "] " + document.getElementById("graphTitle_" + index).innerHTML + fileType;
    } else if (name) {
        return "[" + new Date().toLocaleString() + "] " + name + fileType;
    }
}

function downloadCSV(csvData, index=null, name=null) {
    let blob = new Blob(["\ufeff", csvData],{ type: "text/csv;charset=utf-8" });
    let fileName = createFileName(".csv", index, name);
    saveAs(blob, fileName);
}

function downloadCodeAssignment() {
    let csvData = document.getElementById('csv_codeAssignment_' + 0).value;
    downloadCSV(csvData, null, "code_assignment")
}

function downloadDocuments() {
    let csvData = document.getElementById('csv_documents_' + 0).value;
    downloadCSV(csvData, null, "documents")
}

function getCSVDiagramData(index) {
    return document.getElementById('csv_diagram_' + index).value;
}

function downloadAllCSV() {
    let csvString = "";
    for (let i = 0; i < numberGraphs; i++) {
        csvString += getCSVDiagramData(i);
    }
    const projectTitle = document.getElementById("projectTitle").innerHTML;
    downloadCSV(csvString, null, projectTitle);
}

function downloadSingleCSV(index) {
    downloadCSV(getCSVDiagramData(index), index);
}

// https://codepen.io/Alexander9111/pen/VwLaaPe?editors=1010
function downloadSVGAsText(index, zip) {
    const svg = document.getElementById('svg_' + index);
    const base64doc = btoa(unescape(encodeURIComponent(svg.outerHTML)));
    const fileName = createFileName(".svg", index);
    if (zip) {
        return {
            "fileName": fileName,
            "data": base64doc
        };

    } else {
        const a = document.createElement('a');
        const e = new MouseEvent('click');
        a.download = fileName;
        a.href = 'data:image/svg+xml;base64,' + base64doc;
        a.dispatchEvent(e);
    }
}

async function downloadSVGAsPNG(index, zip){

    return new Promise(function(resolve){ // ugly
        const canvas = document.createElement("canvas");
        const svg = document.getElementById('svg_' + index);
        const base64doc = btoa(unescape(encodeURIComponent(svg.outerHTML)));
        const w = parseInt(xResolution);
        const h = parseInt(yResolution); 
        const img_to_download = document.createElement('img');
        img_to_download.src = 'data:image/svg+xml;base64,' + base64doc;
        const fileName = createFileName(".png", index);
        let result = [];
        img_to_download.onload = function(){
            canvas.setAttribute('width', w);
            canvas.setAttribute('height', h);
            const context = canvas.getContext("2d");
            context.drawImage(img_to_download,0,0,w,h);
            const dataURL = canvas.toDataURL('image/png');
            if (zip) {
                result =  {
                    "fileName": fileName,
                    "data": dataURL.substring(22)
                };

            } else {
                const a = document.createElement('a');
                const my_evt = new MouseEvent('click');
                a.download =  fileName;
                a.href = dataURL;
                a.dispatchEvent(my_evt);
                return;
            }
            resolve(result);
        }
    });
}

function downloadAllSVG() {
    downloadAll("svg");
}

function downloadAllPNG() {
    downloadAll("png");
}

async function downloadAll(filetype) {
    // https://stuk.github.io/jszip/
    let zip = new JSZip();

    if (filetype == "svg") {
        packZipSVG(zip);

    } else if (filetype == "png") {
        await packZipPNG(zip);
    }

    // Generate the zip file asynchronously
    zip.generateAsync({type:"blob"})
    .then(function(content) {
        // Force down of the Zip file
        const projectTitle = document.getElementById("projectTitle").innerHTML;
        saveAs(content, createFileName(".zip", null, projectTitle));
    }); 
}

function packZipSVG(zip) {
    for (let i = 0; i < numberGraphs; i++) {
        let image = downloadSVGAsText(i, true);
        zip.file(image["fileName"], image["data"], {base64: true});
    }
}

async function packZipPNG(zip) {
    for (let i = 0; i < numberGraphs; i++) {
        let image = await downloadSVGAsPNG(i, true);
        zip.file(image["fileName"], image["data"], {base64: true});
    }
}