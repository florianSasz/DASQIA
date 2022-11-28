"use strict";

class CollectData {
    static collectData() {
        let aliasesElements = document.getElementById("aliases").childNodes
        
        let aliasKeys = [];
        aliasesElements.forEach((element) => {
            aliasKeys.push(element.id);
        });
        
        let existingAliases = [];
        let newAliases = [];
        let aliasesToRemove = [];
        aliasKeys.forEach((element, index) => {
            let prefix = element.split("_")[0];
            switch (prefix) {
                case "alias":
                    existingAliases.push(aliasesElements[index].value);
                    break;
                case "newAlias":
                    newAliases.push(aliasesElements[index].value);
                    break;
                case "removeAlias":
                    aliasesToRemove.push(aliasesElements[index].value);
            }
        });

        return {
            "existing": existingAliases,
            "new": newAliases,
            "remove": aliasesToRemove
        };
    }
}

class DataCollection {
    existingAliases;
    newAliases;
    aliasesToRemove;

    constructor(data) {
        this.existingAliases = data["existing"];
        this.newAliases = data["new"];
        this.aliasesToRemove = data["remove"];
    }

    toggleRemoveAlias(alias) {
        alias = alias.replace(" - delete", "");
        if (this.aliasesToRemove.includes(alias)) {
            this.aliasesToRemove.splice(this.aliasesToRemove.indexOf(alias), 1);
        } else {
            this.aliasesToRemove.push(alias);
        }
    }

    addNewAlias(alias) {
        this.newAliases.push(alias);
    }

    removeNewAlias(alias) {
        this.newAliases.splice(this.newAliases.indexOf(alias), 1);
    }
}

class DisplayAlias {
    index;
    parentExisting;
    dataCollection;
    displayingNewAliasCaption;

    constructor(dataCollection) {
        this.index = 0;
        this.parentExisting = document.getElementById("currentAliasesSpace");
        this.parentNew = document.getElementById("newAliasesSpace");
        this.dataCollection = dataCollection;
        this.displayingNewAliasCaption = false;

        if (this.dataCollection.existingAliases.length > 0) {
            this.displayAliasRemoveInfo();
            this.dataCollection.existingAliases.forEach((alias) => {
                this.displayExistingAlias(alias);
            });
        } else {
            this.displayNoExistingAliases();
        }
        if (this.dataCollection.newAliases.length > 0) {
            this.dataCollection.newAliases.forEach((alias) => {
                this.displayNewAlias(alias);
            });
        }
    }

    createVisualAlias(alias) {
        const container = document.createElement("div");
        container.classList.add("aliasContainer");
        container.id = "alias_" + this.index;
        const text = document.createElement("p");
        text.innerHTML = alias;
        container.appendChild(text);
        return container;
    }

    displayExistingAlias(alias) {
        const container = this.createVisualAlias(alias);

        container.addEventListener("click", function() {
            colleciton.toggleRemoveAlias(container.childNodes[0].innerHTML);
            display.toggleDisplayToRemove(container.id);
        });
        this.parentExisting.appendChild(container); 
        this.index++;  
        if (this.dataCollection.aliasesToRemove.includes(alias)) {
            this.toggleDisplayToRemove(container.id);
        }
    }

    displayNewAlias(alias) {
        if (!this.displayingNewAliasCaption) {
            this.addNewAliasCaption();
            this.displayingNewAliasCaption = true;
        }
        const container = this.createVisualAlias(alias);

        container.addEventListener("click", function() {
            colleciton.removeNewAlias(container.childNodes[0].innerHTML);
            display.removeNewAlias(container.id);
        });

        this.parentNew.appendChild(container); 
        this.index++;  
    }

    addNewAliasCaption() {
        let caption = document.createElement("p");
        caption.classList.add("blackSubHeadline");
        caption.innerHTML = "new aliases";
        caption.id = "caption_new";

        let userInfo = document.createElement("p");
        userInfo.classList.add("greyText", "userInfo");
        userInfo.innerHTML = "Click an alias to remove it.";
        userInfo.id = "userInfo_new";

        this.parentNew.appendChild(caption);
        this.parentNew.appendChild(userInfo);
    }

    removeNewAliasCaption() {
        document.getElementById("caption_new").remove();
        document.getElementById("userInfo_new").remove();
        this.displayingNewAliasCaption = false;
    }

    toggleDisplayToRemove(aliasContainerID) {
        let container = document.getElementById(aliasContainerID);
        if (container.classList.contains("aliasContainerRemove")) {
            container.childNodes[0].innerHTML = container.childNodes[0].innerHTML.replace(" - delete", "");
        } else {
            container.childNodes[0].innerHTML += " - delete";
        }
        container.classList.toggle("aliasContainerRemove")
    }

    removeNewAlias(containerID) {
        if (this.dataCollection.newAliases.length === 0) {
            this.removeNewAliasCaption();
        }
        document.getElementById(containerID).remove();
    }

    displayNoExistingAliases() {
        let text = document.createElement("p");
        text.innerHTML = "There are no existing aliases.";
        text.classList.add("greyText", "userInfo");
        this.parentExisting.appendChild(text);
    }

    displayAliasRemoveInfo() {
        let text = document.createElement("p");
        text.classList.add("greyText", "userInfo");
        text.innerHTML = "Click an alias to toggle a remove order.";
        this.parentExisting.appendChild(text);
    }
}

class Events {
    
    static setupAddNewAlias() {
        let addNewButton = document.getElementById("addNewAliasButton");
        let newAliasInput = document.getElementById("newAliasInput");
        addNewButton.addEventListener("click", function() {
            let newAlias = newAliasInput.value;
            let onlyChar = newAlias.replaceAll(" ", "");
            if (!onlyChar) {
                newAliasInput.value = "";
                return;
            }
            colleciton.addNewAlias(newAlias);
            display.displayNewAlias(newAlias);
            newAliasInput.value = "";
        });
    }

    static setupNavigationButtons() {
        let saveButton = document.getElementById("saveSubmit");
        saveButton.addEventListener("click", function() {   
            submit.save();
        });

        let cancelButton = document.getElementById("cancelSubmit");
        cancelButton.addEventListener("click", function() {
            submit.cancel();
        });
    }
}

class Submit {
    dataCollection;

    constructor(dataCollection) {
        this.dataCollection = dataCollection;
        this.form = document.getElementById("submitForm");
    }

    createInput(value, name) {
        let input = document.createElement("input");
        input.type = "hidden";
        input.name = name;
        input.value = value;
        this.form.appendChild(input);
    }

    writeDataToDom() {
        this.dataCollection.newAliases.forEach((newAlias, index) => {
            this.createInput(newAlias, "newAlias_" + index);
        });

        this.dataCollection.aliasesToRemove.forEach((aliasToRemove, index) => {
            this.createInput(aliasToRemove, "aliasToRemove_" + index);
        });
    }

    save() {
        this.writeDataToDom();
        this.submitForm("save");
    }

    cancel() {
        this.submitForm("cancel");
    }

    submitForm(navigationValue) {
        let navigation = document.createElement("input");
        navigation.type = "hidden";
        navigation.name = "navigation";
        navigation.value = navigationValue;
        this.form.appendChild(navigation);

        this.form.submit();
    }
}

const colleciton = new DataCollection(CollectData.collectData());
const display = new DisplayAlias(colleciton);
const submit = new Submit(colleciton);
Events.setupAddNewAlias();
Events.setupNavigationButtons();