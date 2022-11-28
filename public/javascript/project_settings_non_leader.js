"use strict";
import {   
    DataCollection,
    DisplayShadowMembers,
    SetupEvents,
    DisplayRegisteredMembersBase,
    DisplayMembers,
    Submit,
    CollectData
    } from "./project_settings_general.js";

class DisplayRegisteredMembers extends DisplayRegisteredMembersBase {
    
    constructor(dataCollection) {
        super();
        if (dataCollection.existingRegisteredMembers.length > 1) {
            this.parent.appendChild(DisplayMembers.createHeadline("Existing"));
            for (const member of dataCollection.existingRegisteredMembers) {
                this.displayExistingRegisteredMembers(member, dataCollection);
            }
        }
        if (dataCollection.newRegisteredMembers.length > 0) {
            for (const member of dataCollection.newRegisteredMembers) {
                this.displayNewRegisteredMember(member, dataCollection);
            }
        }
    }

    displayExistingRegisteredMembers(registeredMember, dataCollection) {
        if (registeredMember != dataCollection.leader) {
            DisplayMembers.displayMember(registeredMember, this.parent);
        }
    }

}

const collection = new DataCollection("edit");
const displayShadow = new DisplayShadowMembers(collection);
const displayRegistered = new DisplayRegisteredMembers(collection);
if (document.getElementById("removeRegistered")) {
    CollectData.collectRemoveAndNewLeader(collection);
}
const submit = new Submit();
const setupEvents = new SetupEvents(collection, displayShadow, displayRegistered, submit);
