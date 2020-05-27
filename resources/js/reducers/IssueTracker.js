import { ISSUE_TRACKER_CHANGE_LABEL, ISSUE_TRACKER_CLEAR_PAGE, ISSUE_TRACKER_CHANGE_CONTENT, ISSUE_TRACKER_ISSUES_LOADED, ISSUE_TRACKER_CHANGE_TYPE, ISSUE_TRACKER_CHANGE_PAGE } from "../constants/actionTypes";

const initialState = {
    label:"1",
    resetKey:1,
    content:"",
    issues:[],
    state:"opened",
    page:1
}

export default (state=initialState,{type,payload})=>{
    switch (type) {
    case ISSUE_TRACKER_CHANGE_LABEL:
        return {
            ...state,
            label:payload.label
        };
    case ISSUE_TRACKER_CLEAR_PAGE:
        return {
            ...state,
            label:"1",
            resetKey:state.resetKey+1
        }
    case ISSUE_TRACKER_CHANGE_CONTENT:
        return {
            ...state,
            content:payload.content
        }
    case ISSUE_TRACKER_CHANGE_TYPE:
        return {
            ...state,
            state:payload.state
        }
    case ISSUE_TRACKER_CHANGE_PAGE:
        return {
            ...state,
            page:payload.page
        }
    case ISSUE_TRACKER_ISSUES_LOADED:
        const appendIssues = payload.append? state.issues:[];
        return {
            ...state,
            issues:[
                ...appendIssues,
                ...payload.issues
            ]
        }
    default:
        return state;
    }
}