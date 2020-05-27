import { ISSUE_TRACKER_CHANGE_LABEL, ISSUE_TRACKER_CLEAR_PAGE, ISSUE_TRACKER_CHANGE_CONTENT, ISSUE_TRACKER_CHANGE_TYPE, ISSUE_TRACKER_CHANGE_PAGE, ISSUE_TRACKER_ISSUES_LOADED } from "../constants/actionTypes";
import agent from "../agent";
import { alertDialog } from "./Dialogs";

export const changeLabel = label=>({
    type:ISSUE_TRACKER_CHANGE_LABEL,
    payload:{label}
});

export const clearPage = ()=>({
    type:ISSUE_TRACKER_CLEAR_PAGE
})

export const changeContent = content=>({
    type:ISSUE_TRACKER_CHANGE_CONTENT,
    payload:{content}
})

export const submitIssue = (content,label)=>dispatch=>{
    agent.IssueTracker.create(content,label).then(({success,payload,message})=>{
        if(success){
            dispatch(clearPage());
            dispatch(alertDialog(message,"success"))
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const changeType =state=>({
    type:ISSUE_TRACKER_CHANGE_TYPE,
    payload:{state}
});

export const changePage = page=>({
    type:ISSUE_TRACKER_CHANGE_PAGE,
    payload:{page}
});

export const loadedIssues = (issues,append=true)=>({
    type:ISSUE_TRACKER_ISSUES_LOADED,
    payload:{issues,append}
})

export const fetchIssues = (state,page)=>dispatch=>{
    agent.IssueTracker.search(state,page).then(({issues})=>{
        dispatch(loadedIssues(issues,page!=1))
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'))
    })
}