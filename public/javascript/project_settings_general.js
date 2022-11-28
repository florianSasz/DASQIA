"use strict";

export class Member {
    name;
    id;
    type;
    aliases;

    constructor(name, id, type, aliases) {
        this.name = name; 
        this.id = id;
        this.type = type;
        this.aliases = aliases;
    }
}

export class CollectData {
    static collectData(dataCollection) {
        // data comes from previouse attempt
        if (document.getElementById("newRegistered")) { // edit/new project with previouse attempt
            this.collectPreviouseAttemptData(dataCollection);

        } else if (document.getElementById("existingRegisteredMembers")){ // edit project 
            // data comes from database
            this.collectDatabaseData(dataCollection); 

        } else { // new project
            dataCollection.leader = document.getElementById("projectLeader").innerHTML.trim();
        }
    }

    static filterMemberData(value) {
        console.log(value);
        let split = value.split("%%");
        console.log(split);
        let result = [];
        result["name"] = split[0];
        split.shift();  
        console.log(split);
        (split[0] === "") ? split.splice(0, 1) : null;
        console.log(split);
        result["aliases"] = split;
        return result;oiuygf
    }

    static collectDatabaseData(dataCollection, editedAliases=null) { // editedAliases from previouse attempt
        let registered = document.getElementById("existingRegisteredMembers").childNodes;
        const currentLeader = document.getElementById("projectLeader").innerHTML.trim();
        for (let i = 0; i < registered.length; i++) {
            let memberData = this.filterMemberData(registered[i].value);
            let member = dataCollection.addExistingRegisteredMember(memberData["name"], memberData["aliases"]);
            // look for projectLeader and set said as property
            if (memberData["name"] == currentLeader) {
                dataCollection.leader = member;
                dataCollection.initialLeader = member;
            } 
        }

        let shadow = document.getElementById("existingShadowMembers").childNodes;
        for (let j = 0; j < shadow.length; j++) {
            let memberData = this.filterMemberData(shadow[j].value);
            let newShadow = dataCollection.addExistingShadowMember(memberData["name"], memberData["aliases"]);
            if (editedAliases) {
                if (editedAliases.includes(memberData["name"])) {
                    dataCollection.shadowMembersAliasEdited.push(newShadow);
                }
            }
        }
    }

    static collectPreviouseAttemptData(dataCollection) {
        let editedAliases = document.getElementById("editedAliases");
        if (editedAliases) {
            editedAliases = editedAliases.value.split("%%");
        } else {
            editedAliases = [];
        }

        if (document.getElementById("existingRegisteredMembers")) {
            this.collectDatabaseData(dataCollection, editedAliases);
        } else {
            dataCollection.leader = document.getElementById("projectLeader").innerHTML.trim();
        }

        let newRegistered = document.getElementById("newRegistered").childNodes;
        for (let i = 0; i < newRegistered.length; i++) {
            dataCollection.addNewRegisteredMember(newRegistered[i].value);
        }

        let newShadows = document.getElementById("newShadow").childNodes;
        for (let j = 0; j < newShadows.length; j++) {
            let memberData = this.filterMemberData(newShadows[j].value)
            let newShadow = dataCollection.addNewShadowMember(memberData["name"], memberData["aliases"]);
            if (editedAliases.includes(memberData["name"])) {
                dataCollection.shadowMembersAliasEdited.push(newShadow);
            }
        } 
    }

    static collectRemoveAndNewLeader(dataCollection) {
        // initialization of DisplayShadowMembers and DisplayRegisteredMembers mist be called before
        let removeRegistered = document.getElementById("removeRegistered").childNodes;
        for (let k = 0; k < removeRegistered.length; k++) {
            let member = dataCollection.getExistingRegisteredByName(removeRegistered[k].value);
            if (member) {
                dataCollection.addMemberToRemove(member);
            }
        } 

        let removeShadow = document.getElementById("removeShadow").childNodes;
        for (let l = 0; l < removeShadow.length; l++) {
            let member = dataCollection.getExistingShadowByName(removeShadow[l].value);
            if (member) {
                dataCollection.addMemberToRemove(member);
            }
        } 

        if (document.getElementById("newLeader")) {
            let newLeader = dataCollection.getExistingRegisteredByName(document.getElementById("newLeader").value);
            if (newLeader) {
                dataCollection.setNewLeader(newLeader);
            }
        }
    }
}

export class DataCollection {
    existingRegisteredMembers;
    existingShadowMembers;
    
    shadowMembersToRemove;
    registeredMembersToRemove;

    newRegisteredMembers;
    newShadowMembers;

    shadowMembersAliasEdited;

    initialLeader;
    leader;

    localId;
    type;

    constructor(type) { // type = "edit" or "new"
        this.localId = 0;
        if (type == "edit") {
            this.existingRegisteredMembers = [];
            this.existingShadowMembers = [];
            this.shadowMembersToRemove = [];
            this.registeredMembersToRemove = [];
        }
        this.type = type;
        this.newRegisteredMembers = [];
        this.newShadowMembers = [];
        this.shadowMembersAliasEdited = [];
        CollectData.collectData(this);
    }
    
    getLocalId() {
        return this.localId++;
    }

    getExistingRegisteredByName(name) {
        for (let i = 0; i < this.existingRegisteredMembers.length; i++) {
            if (this.existingRegisteredMembers[i].name == name) {
                return this.existingRegisteredMembers[i];
            }
        }
        return false;
    }

    getExistingShadowByName(name) {
        for (let i = 0; i < this.existingShadowMembers.length; i++) {
            if (this.existingShadowMembers[i].name == name) {
                return this.existingShadowMembers[i];
            }
        }
        return false;
    }

    addExistingShadowMember(name ,aliases) {
        let member = new Member(name, this.getLocalId(), "shadow", aliases);
        this.existingShadowMembers.push(member);
        return member;
    }

    addNewShadowMember(name, aliases) {
        let member = new Member(name, this.getLocalId(), "shadow", aliases);
        this.newShadowMembers.push(member);
        return member;
    }

    addExistingRegisteredMember(email, aliases) {
        let member = new Member(email, this.getLocalId(), "registered", aliases);
        this.existingRegisteredMembers.push(member);
        return member;
    }

    addNewRegisteredMember(email) { 
        let member = new Member(email, this.getLocalId(), "registered");
        this.newRegisteredMembers.push(member);
        return member;
    }

    addMemberToRemove(member) {
        if (this.leader == member) {
            this.revokeNewLeader();
        }
        switch (member.type) {
            case "shadow":
                this.shadowMembersToRemove.push(member);
                break;
            case "registered":
                this.registeredMembersToRemove.push(member);
                break;
        }
        DisplayMembers.toggleRemoveDisplay(member);
    }

    revokeMemberToRemove(member) {
        switch (member.type) {
            case "shadow":
                this.shadowMembersToRemove.splice(this.shadowMembersToRemove.indexOf(member), 1);
                break;
            case "registered":
                this.registeredMembersToRemove.splice(this.registeredMembersToRemove.indexOf(member), 1);
                break;
        }
        DisplayMembers.toggleRemoveDisplay(member);
    }

    setNewLeader(member) {
        if (this.initialLeader != this.leader) {
            DisplayMembers.toggleLeaderDisplay(this.leader);
        }
        if (this.registeredMembersToRemove.includes(member)) {
            this.revokeMemberToRemove(member);
        }
        this.leader = member;
        DisplayMembers.toggleLeaderDisplay(this.leader);
        DisplayMembers.displayNewLeaderNote();
    }

    revokeNewLeader() {
        DisplayMembers.toggleLeaderDisplay(this.leader);
        DisplayMembers.removeDisplayNewLeaderNote();
        this.leader = this.initialLeader;
    }

    removeNewMember(member) {
        switch (member.type) {
            case "registered":
                this.newRegisteredMembers.splice(this.newRegisteredMembers.indexOf(member), 1);
                if (this.newRegisteredMembers.length == 0) {
                    document.getElementById("headline_registered").remove();
                }
                break;
                case "shadow":
                this.newShadowMembers.splice(this.newShadowMembers.indexOf(member), 1);
                if (this.newShadowMembers.length == 0) {
                    document.getElementById("headline_shadow").remove();
                }
                break;
        }
    }

    addShadowMemberAliasesEdit(shadowMember) {
        if (!this.shadowMembersAliasEdited.includes(shadowMember)) {
            this.shadowMembersAliasEdited.push(shadowMember);
        }
    }
}

export class DisplayMembers {

    static createVisualContainer(id) {
        const container = document.createElement("div");
        container.id = "visualContainer_" + id;
        container.classList.add("memberVisualContainer");
        return container;
    }

    static createIdentifier(identifier) {
        const p = document.createElement("p");
        p.innerHTML = identifier.name;
        p.classList.add("memberName");
        return p;
    }

    static createRemoveFromProjectButton(member, dataCollection) {
        const button = document.createElement("button");
        button.type = "button";
        button.id = "remove_" + member.id;
        button.classList.add("button", "memberButton");
        button.innerHTML = "remove from project";
        button.addEventListener("click", function() {
            if (button.innerHTML == "remove from project") {
                dataCollection.addMemberToRemove(member);
            } else {
                dataCollection.revokeMemberToRemove(member);
            }
        });
        return button;
    }

    static createRemoveImmedietlyButton(member, dataCollection) {
        const button = document.createElement("button");
        button.type = "button";
        button.id = "remove_" + member.id;
        button.classList.add("button", "memberButton");
        button.innerHTML = "remove";
        button.addEventListener("click", function() {
            let container = document.getElementById("container_" + member.id);
            container.remove();
            dataCollection.removeNewMember(member);
        });
        return button;
    }

    static createEditAliasButton(shadowMember, dataCollection) {
        const button = document.createElement("button");
        button.type = "button";
        button.id = "aliasButton_" + shadowMember.id;
        button.classList.add("button", "memberButton");
        button.innerHTML = "edit aliases";
        button.addEventListener("click", function() {
            let parent = document.getElementById("container_" + shadowMember.id);
            if (button.innerHTML === "edit aliases") {
                // add edit alias menu
                parent.appendChild(DisplayMembers.createEditAliasMenu(shadowMember, dataCollection));
                button.innerHTML = "finish editing";
            } else {
                // remove edit alias menu
                document.getElementById("editAliasMenu_" + shadowMember.id).remove();
                // replace alias display 
                document.getElementById("aliasDisplay_" + shadowMember.id).remove();
                document.getElementById("visualContainer_" + shadowMember.id).appendChild(DisplayMembers.createAliasDisplay(shadowMember.id, shadowMember.aliases));
                button.innerHTML = "edit aliases";
            }
        });
        return button;
    }

    static createEditAliasMenu(shadowMember, dataCollection) {

        function createVisualAlias(alias, aliasIndex) {
            const container = document.createElement("div");
            container.classList.add("memberVisualContainer", "EditAliasVisualContainer");
            container.id = "visualAliasContainer_" + aliasIndex;

            const aliasText = document.createElement("p");
            aliasText.innerHTML = alias;
            container.appendChild(aliasText);

            container.addEventListener("click", function() {
                removeAlias(container.id, alias, shadowMember);
            });
            
            return container;
        }
        
        function removeAlias(aliasVisualContainerID, alias, shadowMember) {
            document.getElementById(aliasVisualContainerID).remove();
            // remove alias from dataCollection
            shadowMember.aliases.splice(shadowMember.aliases.indexOf(alias), 1);
            // display no alias info, if there are no left
            if (shadowMember.aliases.length === 0) {
                createNoAliasInfo(shadowMember.id, document.getElementById("editAliasMenu_" + shadowMember.id));
            }
            dataCollection.addShadowMemberAliasesEdit(shadowMember);
        }

        function createCaption() {
            const caption = document.createElement("p");
            caption.innerHTML = "aliases:";
            caption.classList.add("blackSubHeadline", "noMargin");
            return caption;
        }

        function createNewAliasBar(shadowMember) {
            const container = document.createElement("div");

            const text = document.createElement("p");
            text.innerHTML = "new alias:";
            text.classList.add("sameLine", "aliasMenuElements");
            container.appendChild(text);
            
            const input = document.createElement("input");
            input.type = "text";
            input.id = "newAliasInput_" + shadowMember.id;
            input.classList.add("sameLine", "aliasMenuElements");
            container.appendChild(input);
            
            const button = document.createElement("button");
            button.type = "button";
            button.innerHTML = "add";
            button.classList.add("button", "sameLine", "aliasMenuElements");
            button.addEventListener("click", function() {
                let newAlias = input.value;
                newAlias = newAlias.replaceAll("%" , "");
                let onlyChar = newAlias.replaceAll(" ", "");
                if (!onlyChar) {
                    input.value = "";
                    return;
                }
                addNewAlias(newAlias, shadowMember);
                input.value = "";
            });
            container.appendChild(button);
            return container;
        }

        function addNewAlias(alias, shadowMember) {
            shadowMember.aliases.push(alias);
            document.getElementById("editAliasMenu_" + shadowMember.id).appendChild(createVisualAlias(alias, shadowMember.aliases.length));
            if (document.getElementById("noAliasInfo_" + shadowMember.id)) {
                document.getElementById("noAliasInfo_" + shadowMember.id).remove();
            }
            dataCollection.addShadowMemberAliasesEdit(shadowMember);
        }

        function createNoAliasInfo(shadowMemberID, container) {
            const info = document.createElement("p");
            info.innerHTML = "There are no alises for this shadow member.";
            info.classList.add("greyText");
            info.id = "noAliasInfo_" + shadowMemberID;
            container.appendChild(info);
        }

        const container = document.createElement("div");
        container.id = "editAliasMenu_" + shadowMember.id;
        container.classList.add("EditAliasContainer");

        container.appendChild(createNewAliasBar(shadowMember));
        container.appendChild(createCaption());
        
        if (shadowMember.aliases.length > 0) {
            shadowMember.aliases.forEach((alias, index) => {
                container.appendChild(createVisualAlias(alias, index));
            });
        } else {
            createNoAliasInfo(shadowMember.id, container);
        }
        
        return container;
    }

    static createAliasDisplay(id, aliases) {
        const container = document.createElement("div");
        container.id = "aliasDisplay_" + id;
        const key = document.createElement("p");
        key.innerHTML = "aliases:&nbsp";
        key.id = "alias_" + id;
        key.classList.add("memberName", "greyText", "sameLine");
        container.appendChild(key);
        if (aliases) {
            const value = document.createElement("p");
            value.innerHTML = aliases.join(", ");
            value.id = "aliasValues_" + id;
            value.classList.add("memberName", "greyText", "sameLine");
            container.appendChild(value);
        }
        return container;
    }

    static createMakeLeaderButton(member, dataCollection) {
        const button = document.createElement("button");
        button.type = "button";
        button.id = "makeLeader_" + member.id;
        button.classList.add("button", "memberButton");
        button.innerHTML = "make leader";
        button.addEventListener("click", function() {
            if (button.innerHTML == "make leader") {
                dataCollection.setNewLeader(member);
                } else {
                dataCollection.revokeNewLeader();
            }
        });
        return button;
    }

    static toggleLeaderDisplay(leader) {
        document.getElementById("visualContainer_" + leader.id).classList.toggle("newLeader");
        document.getElementById("alias_" + leader.id).classList.toggle("greyText");
        document.getElementById("aliasValues_" + leader.id).classList.toggle("greyText");

        let button = document.getElementById("makeLeader_" + leader.id);
        if (button.innerHTML == "make leader") {
            button.innerHTML = "don't make leader";
        } else {
            button.innerHTML = "make leader";
        }
    }

    static toggleRemoveDisplay(member) {
        document.getElementById("visualContainer_" + member.id).classList.toggle("removeMember");
        document.getElementById("alias_" + member.id).classList.toggle("greyText");
        document.getElementById("aliasValues_" + member.id).classList.toggle("greyText");

        let button = document.getElementById("remove_" + member.id);
        if (button.innerHTML == "remove from project") {
            button.innerHTML = "don't remove from project";
        } else {
            button.innerHTML = "remove from project";
        }
    }

    static createContainer(id) {
        const container = document.createElement("div");
        container.id = "container_" + id;
        container.classList.add("memberContainer");
        return container;
    }

    static displayMember(member, parent) {
        const container = DisplayMembers.createContainer(member.id);
        const visualContainer = DisplayMembers.createVisualContainer(member.id);

        visualContainer.appendChild(DisplayMembers.createIdentifier(member));
        visualContainer.appendChild(DisplayMembers.createAliasDisplay(member.id, member.aliases));
        container.appendChild(visualContainer);

        parent.appendChild(container);
        return container;
    }

    static displayNewLeaderNote() {
        const parent = document.getElementById("newLeaderNote");
        if (parent.firstChild) {
            return;
        }
        const text = document.createElement("p");
        const bold = document.createElement("b");

        text.innerHTML = "Note: If you make someone else a leader you will lose your status as project leader!<br>\
                          There can always only be one project leader.";
        bold.appendChild(text);
        parent.appendChild(bold);
    }

    static removeDisplayNewLeaderNote() {
        const parent = document.getElementById("newLeaderNote");
        while (parent.firstChild) {
            parent.lastChild.remove();
        }
    }

    static createHeadline(text, type=null) {
        const headline = document.createElement("p");
        headline.innerHTML = text;
        headline.classList.add("blackSubHeadline");
        if (type) {
            headline.id = "headline_" + type;
        }
        return headline;
    }
}

export class DisplayShadowMembers {
    parent;

    constructor(dataCollection) {
        this.parent = document.getElementById("shadowMembers");
        if (dataCollection.type == "edit") {
            if (dataCollection.existingShadowMembers.length > 0) {
                this.parent.appendChild(DisplayMembers.createHeadline("Existing"));
                for (const member of dataCollection.existingShadowMembers) {
                    this.displayExistingShadowMember(member, dataCollection);
                }
            }
        }
        if (dataCollection.newShadowMembers.length > 0) {
            for (const member of dataCollection.newShadowMembers) {
                this.displayNewShadowMember(member, dataCollection);
            }
        }
    }

    displayExistingShadowMember(shadowMember, dataCollection) {
        let container = DisplayMembers.displayMember(shadowMember, this.parent);
        container.appendChild(DisplayMembers.createRemoveFromProjectButton(shadowMember, dataCollection));
        container.appendChild(DisplayMembers.createEditAliasButton(shadowMember, dataCollection));
    }

    displayNewShadowMember(shadowMember, dataCollection) {
        if (!document.getElementById("headline_shadow")) {
            this.parent.appendChild(DisplayMembers.createHeadline("New", shadowMember.type));
        }
        let container = DisplayMembers.displayMember(shadowMember, this.parent);
        container.appendChild(DisplayMembers.createRemoveImmedietlyButton(shadowMember, dataCollection));
        container.appendChild(DisplayMembers.createEditAliasButton(shadowMember, dataCollection));
    }
}

export class DisplayRegisteredMembersBase {
    parent;

    constructor() {
        this.parent = document.getElementById("registeredMembers");
    }

    displayNewRegisteredMember(registeredMember, dataCollection) {
        if (!document.getElementById("headline_registered")) {
            this.parent.appendChild(DisplayMembers.createHeadline("New", registeredMember.type));
        }
        let container = DisplayMembers.displayMember(registeredMember, this.parent);
        container.appendChild(DisplayMembers.createRemoveImmedietlyButton(registeredMember, dataCollection));
    }
}

export class Submit {

    writeDataToDom(dataCollection) {

        function createHiddenInput(name, value) {
            let input = document.createElement("input");
            input.type = "hidden";
            input.name = name
            input.value = value;
            return input;
        }

        const parent = document.getElementById("submitData");
        if (dataCollection.type == "edit"){

            dataCollection.shadowMembersToRemove.forEach((element, index) => {
                parent.appendChild(createHiddenInput("remove_shadow_" + index, element.name));
            });
    
            dataCollection.registeredMembersToRemove.forEach((element, index) => {
                parent.appendChild(createHiddenInput("remove_registered_" + index, element.name));
            });
            
            if (document.getElementById("statusCheckbox")) {
                parent.appendChild(createHiddenInput("status", document.getElementById("statusCheckbox").checked));
            }
        }

        dataCollection.newShadowMembers.forEach((element, index) => {
            let value;
            if (element.aliases.length > 0) {
                value = element.name + "%%" + element.aliases.join("%%");
            } else {
                value = element.name;
            }
            parent.appendChild(createHiddenInput("new_shadow_" + index, value));
            dataCollection.shadowMembersAliasEdited.splice(dataCollection.shadowMembersAliasEdited.indexOf(element), 1);
        });

        dataCollection.newRegisteredMembers.forEach((element, index) => {
            parent.appendChild(createHiddenInput("new_registered_" + index, element.name));
        });

        dataCollection.shadowMembersAliasEdited.forEach((element, index) => {
            let value;
            if (element.aliases.length > 0) {
                value = element.name + "%%" + element.aliases.join("%%");
            } else {
                value = element.name;
            }
            parent.appendChild(createHiddenInput("edit_alias_" + index, value))
        });

        parent.appendChild(createHiddenInput("leader", dataCollection.leader.name));
    }

    saveProject(dataCollection) {
        this.writeDataToDom(dataCollection);
        this.submitForm("save");
    }

    cancelProject() {
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

export class SetupEvents {
    
    constructor(dataCollection, displayShadowMembers, displayRegistered, submit) {
        this.setupAddShadowMember(dataCollection, displayShadowMembers);
        this.setupAddRegisteredMember(dataCollection, displayRegistered);
        this.setupNavigationButtons(dataCollection, submit);
    }

    setupAddShadowMember(dataCollection, displayShadowMembers) {
        const button = document.getElementById("addShadowMember");
        button.addEventListener("click", function() {
            
            let name = document.getElementById("shadowMemberToAdd").value;
            let onlyChar = name.replaceAll(" ", "");
            if (!onlyChar) {
                return;
            }
            let newMember = dataCollection.addNewShadowMember(name, []);
            displayShadowMembers.displayNewShadowMember(newMember, dataCollection);
            document.getElementById("shadowMemberToAdd").value = "";
        });
    }

    setupAddRegisteredMember(dataCollection, displayRegisteredMembers) {
        const button = document.getElementById("addRegisteredMember");
        button.addEventListener("click", function() {
            
            let name = document.getElementById("emailToAdd").value;
            let onlyChar = name.replaceAll(" ", "");
            if (!onlyChar) {
                return;
            }
            let newMember = dataCollection.addNewRegisteredMember(name);
            displayRegisteredMembers.displayNewRegisteredMember(newMember, dataCollection);
            document.getElementById("emailToAdd").value = "";
        });
    }

    setupNavigationButtons(dataCollection, submit) {
        document.getElementById("cancel").addEventListener("click", function() {
            submit.cancelProject();
        });

        document.getElementById("save").addEventListener("click", function() {
            submit.saveProject(dataCollection);
        });
    }
}