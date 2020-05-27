import { USER_CLONE_CHANGE_USER, USER_CLONE_LOAD_SECTIONS, USER_CLONE_CHANGE_SELECTED_TYPES, USER_CLONE_CHANGE_VALUES, USER_CLONE_RESET } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeUser = user =>({
    type:USER_CLONE_CHANGE_USER,
    payload:{user}
});

export const loadSections = sections=>({
    type:USER_CLONE_LOAD_SECTIONS,
    payload:{sections}
});

export const fetchSections = ()=>dispatch=>{
    agent.UserClone.fetchSections().then(({sections})=>{
        dispatch(loadSections(sections));
    });
}

export const selectType= id=>({
    type:USER_CLONE_CHANGE_SELECTED_TYPES,
    payload:{id}
});

export const changeValues = (name,value)=>({
    type:USER_CLONE_CHANGE_VALUES,
    payload:{name,value}
});

export const reset = ()=>({
    type:USER_CLONE_RESET
});

export const cloneUser = (values,sectionIds,id) =>dispatch=>{
    if(!id){
        dispatch(alertDialog("Please select a user to clone.",'error'));
    }
    agent.UserClone.save(values,sectionIds,id).then(({message})=>{
        dispatch(alertDialog(message,'success'));
        dispatch(reset());
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}