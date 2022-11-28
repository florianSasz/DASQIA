"use strict";
import {   
    DataCollection,
    DisplayShadowMembers,
    SetupEvents,
    DisplayRegisteredMembersBase,
    Submit
    } from "./project_settings_general.js";

class DisplayRegisteredMembers extends DisplayRegisteredMembersBase {
    
    constructor(dataCollection) {
        super();
        if (dataCollection.newRegisteredMembers.length > 0) {
            for (const member of dataCollection.newRegisteredMembers) {
                this.displayNewRegisteredMember(member, dataCollection);
            }
        }
    }
}

const collection = new DataCollection("new");
const displayShadow = new DisplayShadowMembers(collection);
const displayRegistered = new DisplayRegisteredMembers(collection);
const submit = new Submit();
const setupEvents = new SetupEvents(collection, displayShadow, displayRegistered, submit);