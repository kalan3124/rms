import {  SIDEBAR_CLOSE,SIDEBAR_SECTION_COLLAPSE, SIDEBAR_SECTION_EXPAND, SIDEBAR_CHANGE, SIDEBAR_MOBILE_TOGGLE } from "../constants/actionTypes";

const initialState = {
    expandedSections:[],
    sidebarMenu:undefined,
    systemType:undefined,
    hiddenMobile:true
}

export default (state=initialState,action)=>{
    switch (action.type) {
        case SIDEBAR_CLOSE:
            return {
                ...state,
                sidebarMenu:undefined
            };
        case SIDEBAR_MOBILE_TOGGLE:
            return {
                ...state,
                hiddenMobile: !state.hiddenMobile
            };
        case SIDEBAR_SECTION_COLLAPSE:
            let collapse = action.payload.id;
            let expandedSections = state.expandedSections.filter(id=>id!=collapse)
            return {
                ...state,
                expandedSections
            }
        case SIDEBAR_SECTION_EXPAND:
            return {
                ...state,
                expandedSections:[...state.expandedSections,action.payload.id]
            }
        case SIDEBAR_CHANGE:
            return {
                ...state,
                sidebarMenu:action.payload.id,
                systemType:action.payload.type
            }
        default:
            return state;
    }
}