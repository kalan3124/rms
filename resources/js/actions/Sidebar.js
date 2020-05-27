import {  SIDEBAR_CLOSE, SIDEBAR_SECTION_COLLAPSE, SIDEBAR_SECTION_EXPAND, SIDEBAR_CHANGE } from "../constants/actionTypes";

export const closeSidebar = ()=>({
    type:SIDEBAR_CLOSE
})

export const collapseSection = (id)=>({
    type:SIDEBAR_SECTION_COLLAPSE,
    payload:{id}
})

export const changeSidebar = (type,id)=>({
    type:SIDEBAR_CHANGE,
    payload:{type,id}
})

export const expandSection = (id)=>({
    type:SIDEBAR_SECTION_EXPAND,
    payload:{id}
})