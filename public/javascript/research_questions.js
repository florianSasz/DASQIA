"use strict";
let numberRQ = 0;
let uniqueIndex = 0;

function createRemoveButton() {
    let removeButton = document.createElement("button");
    removeButton.classList.add("removeButton");
    removeButton.id = uniqueIndex;

    removeButton.onclick = function deleteRQ() {
        let container = document.getElementById("container" + removeButton.id);
        container.remove();
        numberRQ--;
        if (numberRQ == 0) {
            addRQ();
        }
    }
    return removeButton;
}

function addRQ(dbQuestion=null, dbDescription=null, dbIndex=null) {
    let rqTable = document.getElementById("researchQuestions");
    let tableRow = document.createElement("tr");
    tableRow.id = "container" + uniqueIndex;

    let tableColumn0 = document.createElement("td");
    let tableColumn1 = document.createElement("td");
    let tableColumn2 = document.createElement("td");

    let container = document.createElement("div");

    tableColumn0.appendChild(createRemoveButton());

    let question = document.createElement("p");
    question.innerHTML = "question:";
    question.classList.add("question");
    tableColumn1.appendChild(question);

    let questionInput = document.createElement("input");
    questionInput.type = "text";
    if (dbQuestion) {
        questionInput.value = dbQuestion;
    }
    questionInput.name = "question_" + uniqueIndex;
    questionInput.size = 50;
    tableColumn2.appendChild(questionInput);
    
    tableColumn2.appendChild(document.createElement("br"));

    let description = document.createElement("p");
    description.innerHTML = "description:";
    description.classList.add("description");
    tableColumn1.appendChild(description);

    let descriptionInput = document.createElement("textarea");
    if (dbDescription) {
        descriptionInput.innerHTML = dbDescription;
    }
    descriptionInput.name = "description_" + uniqueIndex;
    descriptionInput.rows = "3";
    descriptionInput.cols = "50";
    descriptionInput.classList.add("description");
    tableColumn2.appendChild(descriptionInput);

    if (dbIndex) {
        let id = document.createElement("input");
        id.type = "hidden";
        id.name = "id_" + uniqueIndex;
        id.value = dbIndex;
        tableRow.appendChild(id);
    }
    
    container.appendChild(tableColumn0);
    container.appendChild(tableColumn1);
    container.appendChild(tableColumn2);

    tableRow.appendChild(container);
    rqTable.appendChild(tableRow);
    uniqueIndex++;
    numberRQ++;
}

function getResearchQuestions() {
    const rqs = document.getElementById("researchQuestionsData").children;
    if (rqs.length === 0) {
        addRQ();
    } else {
        for (let i = 0; i < rqs.length; i++) {
            let rqData = rqs[i].value.split("%%");
            addRQ(rqData[0], rqData[1], rqData[2]);
        }
    }
}

getResearchQuestions();