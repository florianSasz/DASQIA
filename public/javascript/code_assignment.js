"use strict";

class CollectData {

    static collectData(DOMParent) {
        let data = {
            "researchQuestions": [],
            "codes": []
        };
        for (let i = 0; i < DOMParent.children.length; i++) {
            if (DOMParent.children[i].name.startsWith("researchQuestion_")) {
                data["researchQuestions"].push(DOMParent.children[i].value);
                
            } else if (DOMParent.children[i].name.startsWith("code_")) {
                data["codes"].push(DOMParent.children[i].value);
            }
        }
        return data;
    }
}

class Code {
    title;
    id;
    parentID;
    researchQuestions;

    constructor(title, id, parentID=null, researchQuestions=null) {
        this.title = title;
        this.id = id;
        this.parentID = parentID;
        this.researchQuestions = researchQuestions;
    }
}

class ResearchQuestion {
    question;
    id;

    constructor(question, id) {
        this.question = question;
        this.id = id;
    }
}

class InterpretData {

    static interpretRQs(collectedRQs) {
        let rqs = [];
        for (let i = 0; i < collectedRQs.length; i++) {
            let rq = collectedRQs[i].split("%%");
            rqs.push(new ResearchQuestion(rq[0], rq[1]));
        }
        return rqs;
    }

    static interpretCodes(collectedCodes) {
        let codes = [];
        for (let i = 0; i < collectedCodes.length; i++) {
            let codeSplitted = collectedCodes[i].split("%%");
            // assigned researchQuestion id's to this code
            let assignedRQs = [];
            if (codeSplitted.length > 3) {
                for (let j = 3; j < codeSplitted.length; j++) {
                    assignedRQs.push(codeSplitted[j]);
                }
            }   
            codes.push(new Code(codeSplitted[0], codeSplitted[1], codeSplitted[2], assignedRQs));
        }
        return codes;
    }
}

class Group {
    parentCode;
    childrenCodes;

    constructor(self, children) {
        this.parentCode = self;
        this.childrenCodes = children;
    }
}

class DataCollection {
    researchQuestions;
    codes; 
    codeTree; // codes in tree structure -> children = [code, code,...]
    groups;

    constructor(researchQuestions, codes) {
        this.researchQuestions = researchQuestions;
        this.codes = codes;
        this.groups =  this.setGroups();
        console.log(this.groups);
    }

    getCodeList() {
        return JSON.parse(JSON.stringify(this.codes));
    }

    createTreeStructure(nodes) {
        // https://www.naept.com/en/blog/a-simple-tree-building-algorithm/ in the comments
        for (let j = 0; j < nodes.length; j++){
            nodes[j].children = [];
        } 
        const myMap = new Map(nodes.map((item) => [item.id, item]));
        const tree = [];
        for (let i = 0; i < nodes.length; i += 1) {
            const item = nodes[i];
            if (item.parentID) {
                const parentItem = myMap.get(item.parentID);
                
                if (parentItem) {
                    parentItem.children.push(item);
                } else {
                    tree.push(item);
                }
            } else {
                tree.push(item);
            }
        }
        return tree;
    }

    setGroups() {
        let codes = this.createTreeStructure(this.getCodeList());
        let groups = [];
        for (let i = 0; i < codes.length; i++) {
            if (codes[i].children.length > 0) {
                groups.push(new Group(codes[i], codes[i].children));
                this.setSubGroups(codes[i], groups);
            }
        }
        return groups;
    }

    setSubGroups(parentCode, groupList) {
        for (let j = 0; j < parentCode.children.length; j++) {
            if (parentCode.children[j].children.length > 0) {
                groupList.push(new Group(parentCode.children[j], parentCode.children[j].children));
                this.setSubGroups(parentCode.children[j], groupList);
            }
        }
    }

    getResearchQuestionsByID(researchQuestions) {
        let result = [];
        for (const rq of researchQuestions) {
            for (const x of this.researchQuestions) {
                if (rq == x.id) {
                    result.push(x);
                }
            }
        }
        return result;
    }
}

class FilterPanel {
    researchQuestions;
    rqCheckboxes;
    
    groups;
    groupCheckboxes;

    keywords;
    keywordDisplay;

    notAssigned;
    notAssignedLocal;

    selectedFilterMethod;

    constructor(researchQuestions, groups) {
        this.researchQuestions = researchQuestions;
        this.rqCheckboxes = [];

        this.groups = groups;
        this.groupCheckboxes = [];

        this.keywords = [];
        this.keywordDisplay = new KeywordDisplay();

        this.setupFilterMethods();
        this.setupNotAssigned();
        this.setupResearchQuestions();
        this.setupGroups();
        this.setupKeyword();
    }

    setupFilterMethods() {
        const methods = document.getElementsByName("filterMethod");
        for (let i = 0; i < methods.length; i++) {
            methods[i].addEventListener("change", function(){
                filterPanel.changeSelectedFilterMethod(methods[i].id);
            });
            // initial setup:
            if (methods[i].checked == true) {
                this.selectedFilterMethod = methods[i].id;
            }
        }
    }

    changeSelectedFilterMethod(filtermethod) {
        filterPanel.selectedFilterMethod = filtermethod;
        document.getElementById(filtermethod).checked = true;
        buildTable.builTable();
    }

    setupNotAssigned() {
        const notAssignedCheckbox = document.getElementById("codesNotAssigned");
        notAssignedCheckbox.addEventListener("change", function(){
            filterPanel.notAssigned = notAssignedCheckbox.checked;
            buildTable.builTable();
        });
        // initial setup:
        this.notAssigned = notAssignedCheckbox.checked;

        const notAssignedScopeCheckbox = document.getElementById("notAssignedScopeToggle");
        notAssignedScopeCheckbox.addEventListener("change", function(){
            filterPanel.notAssignedLocal = notAssignedScopeCheckbox.checked;
            if (filterPanel.notAssigned) {
                buildTable.builTable();
            }
        });
        // initial setup:
        this.notAssignedLocal = notAssignedScopeCheckbox.checked;
    }

    setupResearchQuestions() {
        const parent = document.getElementById("researchQuestionsSection");
        for (let i = 0; i < this.researchQuestions.length; i++) {
            const checkboxContainer = Checkbox.createCheckbox("rq_" + i, this.researchQuestions[i].question, this.researchQuestions[i].id, true, true, "FilterPanelCheckbox");
            
            const checkbox = checkboxContainer.childNodes[0];
            checkbox.addEventListener("change", function() {
                buildTable.builTable();
            });

            this.rqCheckboxes.push(checkbox);
            parent.appendChild(checkboxContainer);
        }
    }

    setupGroups() {
        const parent = document.getElementById("groupSelection");
        for (let i = 0; i < this.groups.length; i++) {
            const checkboxContainer = Checkbox.createCheckbox("group_" + i, this.groups[i].parentCode.title, null, true, false, "FilterPanelCheckbox");
            
            const checkbox = checkboxContainer.childNodes[0];
            checkbox.addEventListener("change", function(){
                if (filterPanel.selectedFilterMethod == "filterByGroup") {
                    buildTable.builTable();
                } else {
                    if (checkbox.checked) {
                        filterPanel.changeSelectedFilterMethod("filterByGroup");
                    }
                }
            });
            this.groupCheckboxes.push(checkbox);
            parent.appendChild(checkboxContainer);
        }
    }

    setupKeyword() {
        const addButton = document.getElementById("keywordAddButton");
        addButton.addEventListener("click", function() {
            const input = document.getElementById("keywordInput");
            if (input.value) {
                filterPanel.addKeyword(input.value);
                if (filterPanel.selectedFilterMethod == "filterByKeyword") {
                    buildTable.builTable();
                } else {
                    filterPanel.changeSelectedFilterMethod("filterByKeyword");
                }
            }
            input.value = "";
        });
    }

    addKeyword(keyword) {
        this.keywords.push(keyword);
        this.keywordDisplay.createKeyword(keyword);
    }

    removeKeyword(keyword) {
        this.keywords.splice(this.keywords.indexOf(keyword.split("%%")[0]), 1);
        this.keywordDisplay.removeKeyword(keyword);    
    }

    getSelectedGroups() {
        let selected = [];
        for (let i = 0; i < this.groupCheckboxes.length; i++) {
            if (this.groupCheckboxes[i].checked) {
                selected.push(this.groups[i]);
            }
        }
        return selected;
    }

    getSelectedResearchQuestions() {
        let selected = [];
        for (let i = 0; i < this.rqCheckboxes.length; i++) {
            if (this.rqCheckboxes[i].checked) {
                selected.push(this.rqCheckboxes[i].id);
            }
        }
        return selected;
    }

    getSelectedFilter() {
        let filter = {
            "method": this.selectedFilterMethod,
            "notAssigned": this.notAssigned,
            "notAssignedLocal": this.notAssignedLocal,
            "researchQuestions": this.getSelectedResearchQuestions()
        };

        switch (this.selectedFilterMethod) {
            case "filterByKeyword":
                filter.keywords = this.keywords;
                break;
            case "filterByGroup":
                filter.groups = this.getSelectedGroups();
        }
        return filter;
    }
}

class KeywordDisplay {
    index;
    parent;

    constructor() {
        this.index = 0; // used to keep each keyword unique
        this.parent = document.getElementById("keyworsToFilter");
    }

    createKeyword(keyword) {
        const container = document.createElement("div");
        container.classList.add("keywordContainer");
        container.id = keyword + "%%" + this.index;
        const text = document.createElement("p");
        text.innerHTML = keyword;
        text.classList.add("keywordContent", "center");

        container.addEventListener("click", function() {
            filterPanel.removeKeyword(container.id);
            buildTable.builTable();
        });

        container.appendChild(text);
        this.parent.appendChild(container); 
        this.index++;     
    }

    removeKeyword(keyword) {
        document.getElementById(keyword).remove();
    }
}

class Checkbox {
    static createCheckbox(name, value, id=null, label=false, checked=false, css=null, assignmentCheckbox=false) {
        // assignment Checkbox: name=codeID, value=rqID
        const container = document.createElement("div");
        const checkbox = this.makeCheckbox(name, value, checked);
        if (id) {
            checkbox.id = id;
        }
        if (assignmentCheckbox) {
            checkbox.addEventListener("change", function() {
                if (checkbox.checked) {
                    assignment.addAssignment(checkbox.name, checkbox.value);
                } else {
                    assignment.removeAssignment(checkbox.name, checkbox.value);
                }
            });
        }
        container.appendChild(checkbox);
        if (label) {
            const label = this.makeLabel(value, name);
            container.appendChild(label);
        }
        if (css) {
            container.classList.add(css);
        }
        return container;
    }

    static makeCheckbox(name, value, checked) {
        const input = document.createElement("input");
        input.type = "checkbox";
        input.name = name;
        input.value = value;
        input.checked = checked;
        input.classList.add("checkbox");
        return input;
    }

    static makeLabel(value, checkboxName) {
        const label = document.createElement("label");
        label.innerHTML = value;
        label.htmlFor = checkboxName;
        label.classList.add("checkboxLabel");
        return label;
    }
}

class Filter {
    filterPanel;
    dataCollection;
    assignment;

    constructor(filterPanel, dataCollection, assignment) {
        this.filterPanel = filterPanel;
        this.dataCollection = dataCollection;
        this.assignment = assignment;
    }

    getTableResources() {
        const filterSettings = this.filterPanel.getSelectedFilter();
        
        let filteredCodes = this.dataCollection.getCodeList();
        let buildMethod;
        
        if (filterSettings["notAssigned"]) {
            filteredCodes = this.#filterNotAssigned(filteredCodes, filterSettings["notAssignedLocal"], filterSettings["researchQuestions"]);
        }

        switch (filterSettings["method"]) {
            case "showAll":
                filteredCodes = this.#filterShowAll(filteredCodes);
                buildMethod = "nested";
                break;

            case "filterByKeyword":
                filteredCodes = this.#filterKeywords(filteredCodes, filterSettings["keywords"]);
                buildMethod = "listed";
                break;

            case "filterByGroup":
                filteredCodes = this.#filterGroups(filteredCodes, filterSettings["groups"]);
                buildMethod = "nested";
                break;

            case "filterAlphabetical":
                filteredCodes = this.#filterAlphabetical(filteredCodes, filteredCodes);
                buildMethod = "listed";
                break;
        }  

        return {
            "codes": filteredCodes,
            "buildMethod": buildMethod,
            "researchQuestions": this.dataCollection.getResearchQuestionsByID(filterSettings["researchQuestions"])
        };
    }

    #filterShowAll(codes) {
        return this.dataCollection.createTreeStructure(codes);
    }

    #filterKeywords(codes, keywords) {
        let result = [];

        for (let i = 0; i < codes.length; i++) {
            for (let j = 0; j < keywords.length; j++) {
                let regex = new RegExp(keywords[j], "i");
                if (codes[i].title.match(regex)) {
                    result.push(codes[i]);
                }   
            }
        }
        return result;
    }

    #filterAlphabetical(codes) {
        codes.sort((a, b) => a.title.localeCompare(b.title)) // ????
        return codes;
    }

    #filterGroups(codes, groups) {

        function getChildIds(childCodes, result) {
            for (let i = 0; i < childCodes.length; i++) {
                result.push(childCodes[i].id);
                if (childCodes[i].children) {
                    getChildIds(childCodes[i].children, result);
                }
            }
        }

        // get all code id's of the selected groups, including all children id's
        let groupIDs = [];
        for (let i = 0; i < groups.length; i++) {
            groupIDs.push(groups[i].parentCode.id);
            getChildIds(groups[i].childrenCodes, groupIDs);
        }

        // compare the received code id's with the groups id's; if a code id exists as a group id -> add code to the result
        let result = [];
        for (let j = 0; j < codes.length; j++) {
            if (groupIDs.includes(codes[j].id)) {
                result.push(codes[j]);
            }
        }

        // create a tree structre of the result 
        return this.dataCollection.createTreeStructure(result);
    }

    #filterNotAssigned(codes, notAssignedLocal, selectedRQs) {

        function filternotAssignedGlobal(codes){
            let result = [];
            for (let i = 0; i < codes.length; i++) {
                if (codes[i].researchQuestions.length == 0) {
                    result.push(codes[i]);
                }
            }
            return result;
        }

        function filterNotAssignedLocal(codes, selectedRQs) {
            let result = [];
            for (let i = 0; i < codes.length; i++) {
                let intersection = codes[i].researchQuestions.filter(x => selectedRQs.includes(x));
                if (intersection.length == 0) {
                    result.push(codes[i]);
                }
            }        
            return result;
        }
        if (notAssignedLocal) {
            return filterNotAssignedLocal(codes, selectedRQs);
        } else {
            return filternotAssignedGlobal(codes);
        }
    }
}

class Assignment {
    dataCollection;

    constructor(dataCollection) {
        this.dataCollection = dataCollection;
    }

    addAssignment(codeID, rqID) {
        for (let i = 0; i <  this.dataCollection.codes.length; i++) {
            if (this.dataCollection.codes[i].id == codeID) {
                this.dataCollection.codes[i].researchQuestions.push(rqID);
                break;
            }
        }
    }

    removeAssignment(codeID, rqID) {
        for (let i = 0; i <  this.dataCollection.codes.length; i++) {
            if (this.dataCollection.codes[i].id == codeID) {
                this.dataCollection.codes[i].researchQuestions.splice(this.dataCollection.codes[i].researchQuestions.indexOf(rqID), 1);
                break;
            }
        }
    }

    getAllAssignments() {
        const codes = this.dataCollection.getCodeList();
        let assignments = {};
        for (const code of codes) {
            if (code.researchQuestions.length > 0) {
                assignments[code.id] = code.researchQuestions.join();
            }   
        }
        return assignments;
    }
}

class BuildTable {
    filter;
    table;
    indentationPixels;
    intendationCssClasses; // used to determine wheter an intendation class already exists

    constructor(filter) {
        this.filter = filter;
        this.table = document.createElement("table");
        this.table.classList.add("codeAssignmentTable");
        document.getElementById("codeAssignmentSpace").appendChild(this.table);
        this.indentationPixels = 35;
        this.intendationCssClasses= [];

        this.builTable();
    }

    builTable() {
        this.resetTable();

        const tableResources = this.filter.getTableResources();
        this.buildTableHeader(tableResources["researchQuestions"]);    

        if (tableResources["codes"].length == 0) {
            this.showNoResult();
            return;
        }
        switch (tableResources["buildMethod"]) {
            case "listed":
                this.buildListed(tableResources["codes"], tableResources["researchQuestions"]);
                break;
            
            case "nested":
                this.buildNested(tableResources["codes"], tableResources["researchQuestions"]);
                break;
        }
    }

    buildTableHeader(researchQuestions) {

        function getGreatestDimension(searchList, dimension) {
            let greatestValue = 0;
            for (let i = 0; i < searchList.length; i++) {
                let dimensions = searchList[i].getBoundingClientRect();
                if (dimensions[dimension] > greatestValue) {
                    greatestValue = dimensions[dimension];
                }
            }
            return greatestValue;
        }

        // top row with angled research questions
        const trRQs = document.createElement("tr");
        let RQs = [];

        const tdEmpty = document.createElement("td");
        trRQs.appendChild(tdEmpty);

        for (let i = 0; i < researchQuestions.length; i++) {
            let td = document.createElement("td");
            
            let question = document.createElement("p");
            question.innerHTML = researchQuestions[i].question;
            question.classList.add("rotate", "test");
            RQs.push(question);

            td.appendChild(question);
            trRQs.appendChild(td);
        }

        this.table.appendChild(trRQs);

        // offset div of the table to make room for the research questions
        let greatestRqHeight = getGreatestDimension(RQs, "height");
        let style = document.createElement("style");
        style.innerHTML =
            `#codeAssignmentSpace {
                margin-top:` + (greatestRqHeight) + `px; 
            }`;
        document.head.appendChild(style);

        // bottom row with checkboxes to assign all
        const trAll = document.createElement("tr");
        trAll.classList.add("tableHeaderRow");

        const tdAll = document.createElement("td");
        tdAll.innerHTML = "all";
        trAll.appendChild(tdAll);

        for (let j = 0; j < researchQuestions.length; j++) {
            let td = document.createElement("td");
            let checkboxAll = Checkbox.createCheckbox("all", researchQuestions[j].id,  null, false, false, "checkboxTD");

            let checkbox = checkboxAll.childNodes[0];
            checkbox.addEventListener("change", function() {
                if (checkbox.checked) {
                    buildTable.assignAllCodesByRQ(checkbox.value);
                }
            });

            td.appendChild(checkboxAll);
            trAll.appendChild(td);
        }

        if (researchQuestions.length > 1) {
            const tdAll = document.createElement("td");
            tdAll.innerHTML = "all";
            tdAll.classList.add("tableColoumnAll", "center");

            trAll.appendChild(tdAll);
        }

        this.table.appendChild(trAll);
    }

    buildListed(codes, researchQuestions) {
        for (const code of codes) {
            this.createRowListed(code, researchQuestions);
        }
    }

    createRowListed(code, researchQuestions) {
        let tr = document.createElement("tr");
        tr.classList.add("tableRow");

        this.createCodeTitle(code.title, tr);
        this.createRQCheckboxes(code.id, code.researchQuestions, researchQuestions, tr);
        this.createCheckboxToAssingAllRQs(code.id, researchQuestions, tr);

        this.table.appendChild(tr);
    }
    
    createCodeTitle(codeTitle, tr, bold=false, intendationDepth=0) {
        let tdCode = document.createElement("td");
        tdCode.innerHTML = codeTitle;
        if (bold) {
            tdCode.classList.add("codeParent");
        }
        if (intendationDepth) {
            tdCode.classList.add("child_" + intendationDepth);
        }
        tr.appendChild(tdCode);
    }

    createRQCheckboxes(codeID, codeAssingments, researchQuestions, tr) {
        for (let i = 0; i < researchQuestions.length; i++) {
            let tdRQ = document.createElement("td");
            let checked = codeAssingments.includes(researchQuestions[i].id);
            tdRQ.appendChild(Checkbox.createCheckbox(codeID, researchQuestions[i].id, null, false, checked, "checkboxTD", true));
            tr.appendChild(tdRQ);
        }
    }

    createCheckboxToAssingAllRQs(codeID, researchQuestions, tr) {
        if (researchQuestions.length > 1) {
            let tdAll = document.createElement("td");
            tdAll.classList.add("tableColoumnAll");
            let checkboxAll = Checkbox.createCheckbox(codeID, "all", null, false, false, "checkboxTD");

            let checkbox = checkboxAll.childNodes[0];
            checkbox.addEventListener("change", function() {
                if (checkbox.checked) {
                    buildTable.assignAllRQsByCode(checkbox.name);
                }
            });

            tdAll.appendChild(checkboxAll);
            tr.appendChild(tdAll);            
        }
    }

    buildNested(codes, researchQuestions) {
        for (const code of codes) {
            this.createRowNested(code, researchQuestions, 0);
        }
    }

    createRowNested(code, researchQuestions, depth) {
        let tr = document.createElement("tr");
        tr.classList.add("tableRow");
        
        if (depth > 0) {
            if (!this.intendationCssClasses[depth - 1]) {
                this.createIntendationClass(depth);
            }
        }
        
        this.createCodeTitle(code.title, tr, code["children"].length > 0, depth);
        this.createRQCheckboxes(code.id, code.researchQuestions, researchQuestions, tr);
        this.createCheckboxToAssingAllRQs(code.id, researchQuestions, tr);

        this.table.appendChild(tr);

        if (code["children"].length > 0) {
            depth++;
            for (const childCode of code["children"]) {
                this.createRowNested(childCode, researchQuestions, depth);
            }
        }
        depth--;
    }

    createIntendationClass(depth) {
        let style = document.createElement("style");
        style.innerHTML =
            `.child_` + depth + ` {
                padding-left: ` + (depth * this.indentationPixels) + `px;
            }`;
        document.head.appendChild(style);
        this.intendationCssClasses.push("child_" + depth);
    }

    resetTable() {
        while (this.table.firstChild) {
            this.table.removeChild(this.table.lastChild);
        }
    }

    showNoResult() {
        let tr = document.createElement("tr");
        tr.classList.add("tableRow");
        let noResult = document.createElement("td");
        noResult.innerHTML = "There are no results for your filter settings.";
        noResult.classList.add("redText");
        tr.appendChild(noResult);
        this.table.appendChild(tr);
    }

    assignAllCodesByRQ(rqID) {
        let codeCheckboxes = document.querySelectorAll("input[value='" + rqID + "']");
        for (let i = 0; i < codeCheckboxes.length; i++) {
            codeCheckboxes[i].checked = true;
            assignment.addAssignment(codeCheckboxes[i].name, codeCheckboxes[i].value)
        }
    }
    
    assignAllRQsByCode(codeID) {
        let codeCheckboxes = document.querySelectorAll("input[name='" + codeID + "']");
        for (let i = 0; i < codeCheckboxes.length; i++) {
            codeCheckboxes[i].checked = true;
            assignment.addAssignment(codeCheckboxes[i].name, codeCheckboxes[i].value)
        }
    }
} 

class Submit {
    assignment;

    constructor(assignment) {
        this.assignment = assignment;
    }

    writeAssignmentsToDom() {

        function createHiddenInput(codeID, assignedRQs) {
            // POST: codeID => "rqID,rqID,rqID" 
            let input = document.createElement("input");
            input.type = "hidden";
            input.name = codeID;
            input.value = assignedRQs;
            return input;
        }

        const parent = document.getElementById("assignments");
        const assignments = this.assignment.getAllAssignments();

        for (const code of Object.keys(assignments)) {
            parent.appendChild(createHiddenInput(code, assignments[code]));
        }
    }

    saveAssignment() {
        this.writeAssignmentsToDom();
        this.submitForm("save");
    }

    cancelAssignment() {
        this.submitForm("cancel");
    }

    submitForm(navigationValue) {
        const form = document.getElementById("submitForm");

        let navigation = document.createElement("input");
        navigation.type = "hidden";
        navigation.name = "navigation";
        navigation.value = navigationValue;
        form.appendChild(navigation);

        form.submit();
    }
}

const DOMParent = document.getElementById("hiddenData");
const rawData = CollectData.collectData(DOMParent);
const collection = new DataCollection(InterpretData.interpretRQs(rawData["researchQuestions"]), InterpretData.interpretCodes(rawData["codes"]));
const assignment = new Assignment(collection);
const filterPanel = new FilterPanel(collection.researchQuestions, collection.groups);
const filter = new Filter(filterPanel, collection, assignment);
const submit = new Submit(assignment);
const buildTable = new BuildTable(filter);