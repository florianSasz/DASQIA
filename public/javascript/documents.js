"use strict";

class CollectData {

    static getExistingDocuments() {
        let documentObjects = [];
        const HTMLdocuments = document.getElementById("documents").children;
        for (let i = 0; i < HTMLdocuments.length; i++) {
            let documentData = HTMLdocuments[i].value.split("%%");

            let document = new Document();
            document.type = documentData[0];
            document.title = documentData[1];
            document.interviewer = documentData[2];
            document.originalInterviewer = documentData[3];
            document.interviewDate = documentData[4];
            document.evaluator = documentData[5];
            document.originalEvaluator = documentData[6];
            document.evaluationDate = documentData[7];
            document.codes = documentData[8];
            document.dbID = documentData[9];

            documentObjects.push(document);
        }
        return documentObjects;
    }

    static getProjectMembers() {
        let memberObjects = [];
        const members = document.getElementById("members").children;
        for (let i = 0; i < members.length; i++) {
            let memberData = members[i].value.split("%%");
            let name = memberData[0];
            memberData.shift();
            if (memberData.length === 1 && memberData[0] === "") {
                memberData = [];
            }
            memberObjects.push(new Member(name, memberData));
        }
        return memberObjects;
    }
}

class Document {
    type;
    title;
    interviewer;
    originalInterviewer;
    interviewDate;
    evaluator;
    originalEvaluator;
    evaluationDate;
    codes;
    dbID;
}

class Member {
    name;
    aliases;

    constructor(name, aliases) {
        this.name = name;
        this.aliases = aliases;  
    }
}

class DataCollection {
    existingDocuments;
    members;
    aliases;

    constructor(existingDocuments, members) {
        this.existingDocuments = existingDocuments;
        this.members = members;
        this.setupAliases();
    }  
    
    removeDocument(documentTitle) {
        for (let i = 0; i < this.existingDocuments.length; i++) {
            if (this.existingDocuments[i].title == documentTitle) {
                this.existingDocuments.splice(i, 1);
                break;
            }
        }
    }   

    setupAliases() {
        this.aliases = {};
        this.members.forEach((member) => {
            member.aliases.forEach((alias) => {
                if (this.aliases[alias]) {
                    this.aliases[alias].push(member);
                } else {
                    this.aliases[alias] = [member];
                }
            });
        });       
    }
}

class BuildTable {
    existingTableContainer;
    newTableContainer;
    members;
    aliases;
    tableIndex;
    aliasStatusInfoDisplayed;
    displayAliasStatusInfo;
    newNameInputWidth;

    constructor(members, aliases, existingDocuments) {
        this.existingTableContainer = document.getElementById("existingTable");
        this.newTableContainer = document.getElementById("newTable");
        this.tableIndex = 0;
        this.aliasStatusInfoDisplayed = false;
        this.displayAliasStatusInfo = false;
        this.newNameInputWidth = false;

        this.aliases = aliases;
        this.members = members;

        this.buildTableExistingDocuments(existingDocuments);
    }


    buildTableExistingDocuments(existingDocuments) {
        this.resetTable("existing");
        this.buildTableCaption("existing");
        if (existingDocuments.length === 0) {
            this.showNoExistingDocuments();
            return;
        }
        const table = this.openTable("existing");
        this.buildTableHeader(table);
        this.buildTableContent(table, existingDocuments, "existing");
    }
    
    buildTableNewDocuments(newDocuments) {
        this.resetTable("new");
        if (!document.getElementById("newDocumentsCaption")) {
            this.buildTableCaption("new");
        }
        if (newDocuments.length === 0) {
            this.showNoNewDocuments();
            return;
        }
        const table = this.openTable("new");
        this.buildTableHeader(table);
        this.buildTableContent(table, newDocuments, "new");
    }

    buildTableCaption(type) {
        let caption = document.createElement("p");
        caption.innerHTML = "<b>" + type + " documents" + "</b>";
        (type === "new") ? caption.id = "newDocumentsCaption" : null;
        caption.classList.add("blackSubHeadline");
        document.getElementById(type + "Caption").appendChild(caption);
    }

    buildTableHeader(table) {
        const tableHeadContent = ["", "id", "type", "title", "interviewer", "interview date", "evaluator", "evaluation date", "codes"];
        const tr = document.createElement("tr");
        for (let i = 0; i < tableHeadContent.length; i++) {
            let td = document.createElement("th");
            td.classList.add("documentsTD");
            td.innerHTML = tableHeadContent[i];
            tr.appendChild(td);
        }
        table.appendChild(tr);
    }

    openTable(type) {
        const table = document.createElement("table");
        table.classList.add("documentsTable");
        switch (type) {
            case "new":
                this.newTableContainer.appendChild(table);
                break;
            case "existing":
                this.existingTableContainer.appendChild(table);
        }
        return table;
    }

    resetTable(type) { // type = "new" or "existing"
        const documents = document.getElementById(type + "Table")
        let child = documents.lastElementChild;
        while (child) {
            documents.removeChild(child);
            child = documents.lastElementChild;
        }
    }

    buildTableContent(table, documents, type) {

        function createID(self) {
            let HTMLid = document.createElement("p");
            HTMLid.innerHTML = self.tableIndex + 1;
            HTMLid.classList.add("center", "noMargin");
            return createTD(HTMLid);
        }

        function createRemoveButton(self) {
            let removeButton = document.createElement("button");
            removeButton.type = "button";
            removeButton.classList.add("removeButton", "removeButtonPos");
            removeButton.id = self.tableIndex;
            removeButton.onclick = function deleteDocument() {
                let row = document.getElementById("row_" + removeButton.id);
                let documentTitle = document.getElementById("title_" + removeButton.id).value;
                collection.removeDocument(documentTitle);
                row.remove();
            }
            return removeButton;
        }

        function createType(type, self) {
            let HTMLtype = document.createElement("input");
            HTMLtype.type = "text";
            HTMLtype.name = "type_" + self.tableIndex;
            HTMLtype.value = type;
            return createTD(HTMLtype);
        }

        function createTitle(title, self) {
            let HTMLtitle = document.createElement("input");
            HTMLtitle.type = "text";
            HTMLtitle.name = "title_" + self.tableIndex;
            HTMLtitle.id = "title_" + self.tableIndex;
            HTMLtitle.readOnly = true;
            HTMLtitle.value = title;
            HTMLtitle.classList.add("readOnly");
            if (self.tableIndex % 2 === 1) {
                HTMLtitle.classList.add("tableRowDark");
            }
            return createTD(HTMLtitle);
        }

        function createMemberSelector(currentEditor, originalEditor, members, type, self) { // type = interviewer or evaluator
            const container = document.createElement("div"); 

            let selector = document.createElement("select");
            selector.name = type + "_" + self.tableIndex;
            selector.classList.add("sameLine");
            const selectorIndex = self.tableIndex;

            let temp = getAliasStatus(self, currentEditor);
            let aliasDot = createDot(temp["aliasStatus"]);
            currentEditor = temp["currentEditor"];
            // temp also used at the end of this funciton
        

            // currently selected member
            let setSelected = document.createElement("option");
            setSelected.innerHTML = currentEditor; 
            setSelected.value = currentEditor; 
            selector.appendChild(setSelected);

            // original name from MAXQDA
            if (currentEditor !== originalEditor) {   
                let originalEditorOption = document.createElement("option");
                originalEditorOption.value = originalEditor; 
                originalEditorOption.innerHTML = originalEditor;
                selector.appendChild(originalEditorOption);
            }

            // rest of team members
            for (let i = 0; i < members.length; i++) {
                if (members[i].name !== currentEditor) {
                    let memberOption = document.createElement("option");
                    memberOption.value = members[i].name;
                    memberOption.innerHTML = members[i].name;
                    selector.appendChild(memberOption);
                }
            }
            // option to enter a new name
            let newName = document.createElement("option");
            newName.value = "enter new name";
            newName.innerHTML = "enter new name:";
            selector.appendChild(newName);

            // entering a new name
            selector.addEventListener("change", function() {
                if (selector.value === "enter new name") {
                    container.appendChild(createNewNameInput(type, selector.offsetWidth, aliasDot.offsetWidth, selectorIndex, self));
                } else {
                    if (document.getElementById("newName_" + type + "_" + selectorIndex)) {
                        document.getElementById("newName_" + type + "_" + selectorIndex).remove();
                    }
                }
            });           
            container.appendChild(aliasDot);
            container.appendChild(selector);

            /* find a better way to dispaly the users with duplicate aliases

            if (temp["aliasStatus"] === "alias duplicate") {
                let duplicateUserAliasNames = document.createElement("p");
                duplicateUserAliasNames.classList.add("orangeText", "noMargin");
                duplicateUserAliasNames.innerHTML = temp["duplicateUsers"].join(", ");
                container.appendChild(duplicateUserAliasNames);
            }
            */
            return createTD(container);
        }

        function createInterviewer(currentInterviewer, originalInterviewer, members, self) {
           return createMemberSelector(currentInterviewer, originalInterviewer, members, "interviewer", self)
        }

        function createNewNameInput(editorType, selectorWidth, aliasDotWidth, selectorIndex, self) { // type = interviewer or evaluator
            const input = document.createElement("input");
            input.type = "text";
            input.id = "newName_" + editorType + "_" +  selectorIndex;
            input.name = "newName_" + editorType + "_" + selectorIndex;

            if (!self.newNameInputWidth) {
                let style = document.createElement("style");
                style.innerHTML =
                `.newNameInputWidth {
                    width:` + selectorWidth + `px; 
                    margin-left: ` + aliasDotWidth + `px;
                }`;
                document.head.appendChild(style);
                self.newNameInputWidth = true;
            }

            input.classList.add("newNameInput", "newNameInputWidth", "sameLine");
            return input;
        }

        function createInterviewDate(date, type, self) {
            let HTMLDate = document.createElement("input");
            HTMLDate.name = "interviewDate_" + self.tableIndex;
            HTMLDate.type = "date";
    
            if (type === "new") {
                HTMLDate.value = self.convertDateFormat(date);
            } else {
                HTMLDate.value = date;
            }

            return createTD(HTMLDate);
        }

        function createEvaluator(currentEvaluator, originalEvaluator, members, self) {
            return createMemberSelector(currentEvaluator, originalEvaluator, members, "evaluator", self)
        }

        function createEvaluationDate(date, type, self) {
            let HTMLDate = document.createElement("input");
            HTMLDate.name = "evaluationDate_" + self.tableIndex;
            HTMLDate.type = "date";

            if (type === "new") {
                HTMLDate.value = self.convertDateFormat(date);
            } else {
                HTMLDate.value = date;
            }

            return createTD(HTMLDate);
        }

        function createCodes(codes, self) {
            let HTMLcodes = document.createElement("p");
            HTMLcodes.name = "numberCodes_" + self.tableIndex;
            HTMLcodes.innerHTML = codes;
            HTMLcodes.classList.add("center", "noMargin");
            return createTD(HTMLcodes);
        }

        function createDBindex(dbID, self) {
            let dbIndex = document.createElement("input");
            dbIndex.type = "hidden";
            dbIndex.name = "dbIndex_" + self.tableIndex;
            dbIndex.value = dbID;
            return dbIndex;
        }

        function createOriginalInterviwer(originalInterviewer, self) {
            let OgInterviewer = document.createElement("input");
            OgInterviewer.type = "hidden";
            OgInterviewer.name = "originalInterviewer_" + self.tableIndex;
            OgInterviewer.value = originalInterviewer;
            return OgInterviewer;
        }

        function createOriginalEvaluator(originalEvaluator, self) {
            let OgEvaluator = document.createElement("input");
            OgEvaluator.type = "hidden";
            OgEvaluator.name = "originalEvaluator_" + self.tableIndex;
            OgEvaluator.value = originalEvaluator;
            return OgEvaluator;
        }

        function createRow(self) {
            let row = document.createElement("tr");
            if (self.tableIndex % 2 === 1) {
                row.classList.add("tableRowDark");
            }
            row.id = "row_" + self.tableIndex;
            return row;
        }

        function createDot(aliasStatus) {
            const container = document.createElement("div");
            if (aliasStatus === "alias found") {
                container.classList.add("filledDotBlue");
            } else if (aliasStatus === "alias duplicate") {
                container.classList.add("filledDotOrange");
            }
            container.classList.add("dot", "sameLine");
            return container;
        }

        function getAliasStatus(self, currentEditor) {
            let aliasStatus = "no alias";
            let duplicateUsers = [];
            if (Object.keys(self.aliases).includes(currentEditor)) {
                if (self.aliases[currentEditor].length > 1) {
                    aliasStatus = "alias duplicate"
                    self.aliases[currentEditor].forEach((user) => {
                        duplicateUsers.push(user.name);
                    });
                } else {
                    aliasStatus = "alias found"
                    currentEditor = self.aliases[currentEditor][0].name;
                }
                self.displayAliasStatusInfo = true;
            }
            return {
                "aliasStatus": aliasStatus,
                "currentEditor": currentEditor,
                "duplicateUsers": duplicateUsers
            };
        }

        function createTD(childElement) {
            let td = document.createElement("td");
            td.classList.add("documentsTD");
            td.appendChild(childElement);
            return td;
        }

        function createSpace() {
            return document.createElement("br");
        }   

        function createAliaStatusInformation(type) {
            document.getElementById("aliasStatusInfo_" + type).classList.toggle("hide");
            
        }

        documents.forEach(document=> {
            const tr = createRow(this);
            tr.appendChild(createRemoveButton(this));    
            tr.appendChild(createID(this));    
            tr.appendChild(createType(document.type, this));
            tr.appendChild(createTitle(document.title, this));
            tr.appendChild(createInterviewer(document.interviewer, document.originalInterviewer, this.members, this));
            tr.appendChild(createInterviewDate(document.interviewDate, type, this));
            tr.appendChild(createEvaluator(document.evaluator, document.originalEvaluator, this.members, this));
            tr.appendChild(createEvaluationDate(document.evaluationDate, type, this));
            tr.appendChild(createCodes(document.codes, this));
            tr.appendChild(createSpace());
            tr.appendChild(createSpace());
            if (type === "existing") {
                tr.appendChild(createDBindex(document.dbID, this));
            } else {
                tr.appendChild(createOriginalInterviwer(document.originalInterviewer, this));
                tr.appendChild(createOriginalEvaluator(document.originalEvaluator, this));
            }
            table.appendChild(tr);
            this.tableIndex++;
            
        })
        if (this.displayAliasStatusInfo) {
            createAliaStatusInformation(type);
        }
    }

    showNoExistingDocuments() {
        let message = document.createElement("p");
        message.classList.add("greyText");
        message.innerHTML = "There are no documents in the project.";
        this.existingTableContainer.appendChild(message);
    }

    showNoNewDocuments() {
        let message = document.createElement("p");
        message.classList.add("greyText");
        message.innerHTML = "There are no new documents in this variables file.";
        this.newTableContainer.appendChild(message);
    }

    convertDateFormat(date) {
        let correctFormat = date.split(".");
        correctFormat = correctFormat[2] + "-" + correctFormat[1] + "-" + correctFormat[0];
        return correctFormat;
    }
}

class ReadFile {
    fileInput;

    constructor() {
        this.fileInput = document.getElementById("varFileInput");

        let extractDataButton = document.getElementById("extractDataButton");
        extractDataButton.addEventListener("click", function() {
            readFile.extractData();
        });
    }

    extractData() {
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
            filterFile.findInterviews(fileContent);
        });
    }
}

class FilterFile {
    dataCollection;
    tableBuilder;

    constructor(dataCollection, tableBuilder) {
        this.dataCollection = dataCollection;
        this.tableBuilder = tableBuilder;
    }

    findInterviews(fileContent) {
        let newDocuments = [];
        for (let j = 0; j < fileContent.length; j++) {
            if (fileContent[j] == '<td align=\'left\' class=\"Style1 BHI\"  white-space: nowrap>Interviews</td>' || fileContent[j] == '<td align=\'left\' class=\"Style1 BHB\"  white-space: nowrap>Interviews</td>'
            || fileContent[j] == '<td align=\'left\' class=\"Style1 BHI\"  white-space: nowrap>Survey</td>' || fileContent[j] == '<td align=\'left\' class=\"Style1 BHB\"  white-space: nowrap>Survey</td>') {
                let document = new Document();
                
                document.type =           (fileContent[j    ].substring(fileContent[j    ].indexOf(">") + 1, fileContent[j    ].lastIndexOf("<")));
                document.title =          (fileContent[j + 1].substring(fileContent[j + 1].indexOf(">") + 1, fileContent[j + 1].lastIndexOf("<")));
                document.interviewer =    (fileContent[j + 2].substring(fileContent[j + 2].indexOf(">") + 1, fileContent[j + 2].lastIndexOf("<")));
                document.interviewDate =  (fileContent[j + 3].substring(fileContent[j + 3].indexOf(">") + 1, fileContent[j + 3].lastIndexOf("<")).slice(0, 10));
                document.evaluator =      (fileContent[j + 4].substring(fileContent[j + 4].indexOf(">") + 1, fileContent[j + 4].lastIndexOf("<"))); 
                document.evaluationDate = (fileContent[j + 5].substring(fileContent[j + 5].indexOf(">") + 1, fileContent[j + 5].lastIndexOf("<")).slice(0, 10)); 
                document.codes = "-";
                document.originalInterviewer = document.interviewer;
                document.originalEvaluator = document.evaluator;

                newDocuments.push(document);
            }
        }
        this.compareExistingWithNewDocuments(newDocuments);
    }

    compareExistingWithNewDocuments(newDocuments) {
        const existingDocuments =  this.dataCollection.existingDocuments;
        for (let i = 0; i < newDocuments.length; i++) {
            for (let j = 0; j < existingDocuments.length; j++) {
                if (newDocuments[i].title == existingDocuments[j].title) {
                    newDocuments.splice(i, 1);
                }
            }
        }
        this.tableBuilder.buildTableNewDocuments(newDocuments);
    }
}   

const collection = new DataCollection(CollectData.getExistingDocuments(), CollectData.getProjectMembers());
const tableBuilder = new BuildTable(collection.members, collection.aliases, collection.existingDocuments);
const readFile = new ReadFile();
const filterFile = new FilterFile(collection, tableBuilder);