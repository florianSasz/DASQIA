"use strict";

class DataCollection {
    existingCodes;
    existingCodesRelationTable;
    codesToRemove;
    childrenCodesToRemove;

    newCodes;
    newFrequencies;
    deselectedNewCodes;

    constructor(collectedData) {
        this.existingCodes = collectedData["codes"]; 
        this.existingCodesRelationTable = collectedData["relationTable"]; // shows all children of a code by their index; index (key and value) is corresponding to the index in this.existingCodes
        this.codesToRemove = [];
        this.childrenCodesToRemove = [];
    }

    addCodeToRemove(code) {
        this.codesToRemove.push(code);
    }

    addChildCodeToRemove(code) {
        this.childrenCodesToRemove.push(code);
    }

    revokeRemoveOrder(code) {
        this.codesToRemove.splice(this.codesToRemove.indexOf(code), 1);
    }
    
    revokeChildRemoveOrder(code) {
        this.childrenCodesToRemove.splice(this.childrenCodesToRemove.indexOf(code), 1);
    }

    setExtractedCodes(codes) {
        this.deselectedNewCodes = [];
        this.newCodes = codes;
    }

    setExtractedFrequencies(frequencies) {
        this.newFrequencies = frequencies;
    }

    addDeselectedCode(index) {
        this.deselectedNewCodes.push(index);
    }

    revokeDeselectedCode(index) {
        this.deselectedNewCodes.splice(this.deselectedNewCodes.indexOf(index), 1);
    }

    getSubmitData() {
        let submitData = {
            "codesToRemove": this.codesToRemove, // no need to send the children codes that will be deleted because the way the database is set up it will solve that on it's own
        }

        if (this.deselectedNewCodes) {
            // remove all deselected codes form newCodes -: this.newCodes will be the submit data
            this.deselectedNewCodes.sort(function(a, b){return a - b}); 
            //sorting to first remove codes that come first in newCodes -> indicies that come later can be subtracted by i to account for the index shift that occurs when deleting a previouse index
            for(let i = 0; i < this.deselectedNewCodes.length; i++) {
                this.newCodes.splice(this.deselectedNewCodes[i] - i, 1);
                
                for (let j = 0; j < this.newFrequencies.length; j++) {
                    this.newFrequencies[j]["frequency"].splice(this.deselectedNewCodes[i] - i, 1);
                }   
            }

            submitData["newCodes"] = this.newCodes,
            submitData["newFrequencies"] = this.newFrequencies;
        }

        return submitData;
    }
}

class CollectData {

    static collectCodes() {   
        const codesHTML = document.getElementById("codeData").children;
        let codes = [];
        let tempLookUp = []; // temp lookup table to know the local index by the received databaseID
        let relationTable = [];

        for (let i = 0; i < codesHTML.length; i++) {
            let codeData = this.#filterCodeEntry(codesHTML[i].value);
            codes.push(codeData["name"]);
            relationTable.push(null);
        
            tempLookUp.push(codeData["id"]);
            if (codeData["parentId"]) { // add child to parent in relationTable
                let localParentIndex = tempLookUp.indexOf(codeData["parentId"]);
                if (!relationTable[localParentIndex]) {
                    relationTable[localParentIndex] = [];
                } 
                relationTable[localParentIndex].push(i);
            }
        }
        return {
            "codes": codes,
            "relationTable": relationTable
        };
    }

    static #filterCodeEntry(codeEntry) {
        let codeComponents = codeEntry.split("%%");
        return {
            "name": codeComponents[0],
            "id": codeComponents[1],
            "parentId": codeComponents[2]
        };
    }
}

class DisplayExisting {
    parentContainer;

    constructor() {
        this.parentContainer = document.getElementById("codeSpaceExisting");
    }

    display(codes) {
        if (codes.length > 0) {
            this.displayExisting(codes);
        } else {
            this.displayNoCodes();
        }
    }

    displayExisting(codes) {
        const list = document.createElement("details");
        list.open = false;

        const headline = document.createElement("summary");
        headline.innerHTML = "<b>existing codes</b>";
        headline.classList.add("blackSubHeadline")
        list.appendChild(headline);

        const userInfo = document.createElement("p");
        userInfo.innerHTML = 
        "Click a code to toggle a remove order.<br>\
        If you delete a parent code, all the children codes will also be deleted.";
        list.appendChild(userInfo);

        for (let i = 0; i < codes.length; i++) {
            let container = document.createElement("div");
            container.id = "container_" + i;
            container.classList.add("existingCodeContainer");
            container.addEventListener("click", function() {
                events.toggleRemove(container, i)
            });

            let title = document.createElement("p");
            title.innerHTML = codes[i];
            title.classList.add("sameLine");
            container.appendChild(title);

            list.appendChild(container);
        }
        this.parentContainer.appendChild(list);
    }

    displayNoCodes() {
        const headline = document.createElement("p");
        headline.innerHTML = "<b>existing codes</b>";
        headline.classList.add("blackSubHeadline");
        this.parentContainer.appendChild(headline);

        const info = document.createElement("p");
        info.innerHTML = "There are no codes in this project.";
        info.classList.add("greyText")
        this.parentContainer.appendChild(info);
    }
}

class DisplayNew {
    parentContainer;

    constructor() {
        this.parentContainer = document.getElementById("codeSpaceNew");
    }

    display(codes) {
        DisplayNew.resetNewSpace();
        if (codes) {
            this.displayNew(codes);
        } else {
            this.displayNoNewCodes();
        }
    }

    displayNew(codes) {
        const list = document.createElement("details");
        list.open = true;

        const headline = document.createElement("summary");
        headline.innerHTML = "<b>new codes</b>";
        headline.classList.add("blackSubHeadline"); 
        list.appendChild(headline);
        
        const info = document.createElement("p");
        info.innerHTML = 
        "Deselect all codes yout don't want to process.<br>\
        If the code already existst in the project, the data will be updated if there is a change.<br>\
        <b>Only deselect codes you never want to include. If a code already exists but there is no change to it, inlcude it anyways!</b><br>";
        list.appendChild(info);

        for (let i = 0; i < codes.length; i++) {
            let container = document.createElement("div");
            container.classList.add("codeContainer");

            // code checkbox
            let checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.name = "checkbox_" + i;
            checkbox.id = "checkbox_" + i;
            checkbox.value = codes[i];
            checkbox.classList.add("sameLine", "checkbox");
            checkbox.checked = true;
            checkbox.addEventListener("change", function() {
                events.toggleCodeSelection(i, checkbox.checked);
            });
            container.appendChild(checkbox);

            // code name
            let codeName = document.createElement("p");
            codeName.innerHTML = codes[i];
            codeName.classList.add("sameLine", "codeName");
            container.appendChild(codeName);

            list.appendChild(container);
        }
        this.parentContainer.appendChild(list);
    }

    displayNoNewCodes() {
        const headline = document.createElement("p");
        headline.innerHTML = "<b>new codes</b>";
        headline.classList.add("blackSubHeadline");
        this.parentContainer.appendChild(headline);

        const info = document.createElement("p");
        info.innerHTML = "There are no new codes in this file.";
        info.classList.add("greyText")
        this.parentContainer.appendChild(info);
    }

    static resetNewSpace() {
        let parentContainer = document.getElementById("codeSpaceNew"); 
        while (parentContainer.firstChild) {
            parentContainer.removeChild(parentContainer.lastChild);
        }
    }
}

class DisplayError {
    parentContainer;

    constructor() {
        this.parentContainer = document.getElementById("codeSpaceNew");   
    }

    display(inputError) {
        DisplayNew.resetNewSpace();
        let errorText = document.createElement("p");
        errorText.innerHTML = 
        "It is not possible to add the code '" + inputError + "'. The character combination '%%' is not allowed.<br>\
        Please rename this code and all the other codes with this character combination before proceeding.";
        errorText.classList.add("redText");
        this.parentContainer.appendChild(errorText);
    }
}

class ReadFile {
    fileInput;

    constructor() {
        this.fileInput = document.getElementById("codeMatrixInput");
    }

    read() {
        if (this.fileInput.files.length == 0) {
            return;
        }
        let file = this.fileInput.files[0];

        if (file.type !== "text/html") {
            alert("Error: not a .html file");
            return;
        };
        
        let reader = new FileReader();
        reader.readAsText(file);

        // when file is finished reading:
        reader.addEventListener('load', function(e) {
            // split data into single lines
            let fileContent = e.target.result.split(/\r\n|\n/);
            events.finishedReadingFile(fileContent);
        });
    }
}

class FilterFile {

    extractData(fileContent) {
        //search for codes
        let index;
        let codesFound = []
        for (let i = 0; i < fileContent.length; i++) {
            if (fileContent[i].startsWith("<th")) {
                let content = fileContent[i].substring(fileContent[i].indexOf(">") + 1, fileContent[i].lastIndexOf("<"));
                if (content != "&nbsp;") {
                    if (this.stringIsValid(content)) {
                        codesFound.push(content);
                    } else {
                        events.inputError(content);
                        return;
                    }
                }
            }
            if (fileContent[i].startsWith("<td")) {
                index = i;
                break;
            }
        }
        // search for codes frequency
        let frequenciesFound = [];
        let lastElementWasTableData = false;
        let tempDocument;
        let tempFrquency = [];
        for (let j = index; j < fileContent.length; j++) {
            if (fileContent[j].startsWith("<td")) {
                if (!lastElementWasTableData) {
                    tempDocument = fileContent[j].substring(fileContent[j].indexOf(">") + 1, fileContent[j].lastIndexOf("<"));
                    lastElementWasTableData = true;
                } else {
                    tempFrquency.push(fileContent[j].substring(fileContent[j].indexOf(">") + 1, fileContent[j].lastIndexOf("<")));
                }
            } else {
                if (lastElementWasTableData) {
                    frequenciesFound.push({
                        "document": tempDocument,
                        "frequency": tempFrquency
                    });
                    tempFrquency = [];
                    lastElementWasTableData = false;
                }
            }
        }
        events.finishedExtractingData(codesFound, frequenciesFound);
    }

    stringIsValid(input) {
        return !input.includes("%%");
    }   
}

class Events {
    dataCollection;
    displayExisting;
    readFile;
    filterFile;
    displayNew;
    displayError;

    constructor(dataCollection, displayExisting) {
        this.dataCollection = dataCollection;
        this.displayExisting = displayExisting;

        this.setupExtractData();
        this.setupNavigationButtons();
        this.displayExistingCodes();
    }

    setupExtractData() {
        document.getElementById("extractCodesButton").addEventListener("click", function() {
            if (!events.readFile) {
                events.readFile = new ReadFile();
            }
            events.readFile.read();
        });
    }

    setupNavigationButtons() {
        document.getElementById("save").addEventListener("click", function () {
            let submit = new Submit();
            submit.save(events.dataCollection.getSubmitData());
        });

        document.getElementById("cancel").addEventListener("click", function () {
            let submit = new Submit();
            submit.cancel();
        });
    }

    displayExistingCodes() {
        this.displayExisting.display(this.dataCollection.existingCodes);
    }

    toggleRemove(codeContainer, codeIndex) {
        if (this.dataCollection.childrenCodesToRemove.includes(codeIndex)) {
            return; // prevents to toggle remove order for child deletes
        }
        codeContainer.classList.toggle("removeCode");
        if (this.dataCollection.codesToRemove.includes(this.dataCollection.existingCodes[codeIndex])) { // revoke remove order
            this.dataCollection.revokeRemoveOrder(this.dataCollection.existingCodes[codeIndex]);
            codeContainer.children[0].innerHTML = codeContainer.children[0].innerHTML.replace(" - delete", "");
        } else { // remove order
            this.dataCollection.addCodeToRemove(this.dataCollection.existingCodes[codeIndex]);
            codeContainer.children[0].innerHTML += " - delete"
        }
        this.toggleChildRemove(codeIndex);
    }

    toggleChildRemove(parentCodeIndex) {
        if (this.dataCollection.existingCodesRelationTable[parentCodeIndex]) {
            
            this.dataCollection.existingCodesRelationTable[parentCodeIndex].forEach(childIndex => {
                let childCodeContainer = document.getElementById("container_" + childIndex);
                childCodeContainer.classList.toggle("removeChildCode");

                if (this.dataCollection.childrenCodesToRemove.includes(childIndex)) { //revoke
                    this.dataCollection.revokeChildRemoveOrder(childIndex);
                    childCodeContainer.children[0].innerHTML = childCodeContainer.children[0].innerHTML.replace(" - child delete", "");

                } else { // remove
                    this.dataCollection.addChildCodeToRemove(childIndex);
                    childCodeContainer.children[0].innerHTML += " - child delete"
                }
                this.toggleChildRemove(childIndex);
            });
        }
    }

    finishedReadingFile(fileContent) {
        if (!this.filterFile) {
            this.filterFile = new FilterFile();
        }
        this.filterFile.extractData(fileContent);
    }

    finishedExtractingData(codesFound, frequenciesFound) {
        this.dataCollection.setExtractedCodes(codesFound);
        this.dataCollection.setExtractedFrequencies(frequenciesFound);
        if (!this.displayNew) {
            this.displayNew = new DisplayNew();
        }
        this.displayNew.display(codesFound);
    }
    
    inputError(inputError) {
        if (!this.displayError) {
            this.displayError = new DisplayError();
        }
        this.displayError.display(inputError);
    }   

    toggleCodeSelection(dataCollectionIndex, selctionBoolean) {
        if (selctionBoolean) {
            this.dataCollection.revokeDeselectedCode(dataCollectionIndex);
        } else {
            this.dataCollection.addDeselectedCode(dataCollectionIndex);
        }
    }
}

class Submit {

    writeDataToDom(submitData) {
        const parent = document.getElementById("submitData");

        for (let i = 0; i < submitData["codesToRemove"].length; i++) {
            parent.appendChild(this.createHiddenInput("remove_" + i, submitData["codesToRemove"][i]));
        }
        
        if (submitData["newCodes"]){
            for (let j = 0; j < submitData["newCodes"].length; j++) {
                parent.appendChild(this.createHiddenInput("newCode_" + j, submitData["newCodes"][j]));
            }
            for (let k = 0; k < submitData["newFrequencies"].length; k++) {
                parent.appendChild(this.createHiddenInput("documentName_" + k, submitData["newFrequencies"][k]["document"]));
                parent.appendChild(this.createHiddenInput("frequency_" + k, submitData["newFrequencies"][k]["frequency"]));
            }
        }
    }

    createHiddenInput(name, value) {
        let input = document.createElement("input");
        input.type = "hidden";
        input.name = name;
        input.value = value;
        return input;
    }

    save(submitData) {
        this.writeDataToDom(submitData);
        this.submitForm("save");
    }

    cancel() {
        this.submitForm("cancel");
    }

    submitForm(navigationValue) {
        const form = document.getElementById("formSubmit");
        form.appendChild(this.createHiddenInput("navigation", navigationValue));
        form.submit();
    }
}

let dataCollection = new DataCollection(CollectData.collectCodes());
let displayExisting = new DisplayExisting();

const events = new Events(dataCollection, displayExisting);