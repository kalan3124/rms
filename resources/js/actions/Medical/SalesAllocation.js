import {
    SALES_ALLOCATION_CHANGE_TEAM,
    SALES_ALLOCATION_CHANGE_UPDATING_MODE,
    SALES_ALLOCATION_CHANGE_MODE,
    SALES_ALLOCATION_LOAD_DATA,
    SALES_ALLOCATION_LOAD_SECTION_DATA,
    SALES_ALLOCATION_SELECT_ROW,
    SALES_ALLOCATION_CHANGE_SEARCH_TERM,
    SALES_ALLOCATION_CHANGE_PAGE,
    SALES_ALLOCATION_CHANGE_PER_PAGE,
    SALES_ALLOCATION_CHANGE_MEMBER_PERCENTAGE
} from "../../constants/actionTypes";
import agent from "../../agent";
import { SEARCHING_RECORDS } from "../../constants/debounceTypes";
import { alertDialog } from "../Dialogs";

export const changeTeam = team => ({
    type: SALES_ALLOCATION_CHANGE_TEAM,
    team
});

export const changeUpdatingMode = updatingMode => ({
    type: SALES_ALLOCATION_CHANGE_UPDATING_MODE,
    updatingMode
});

export const changeMode = mode => ({
    type: SALES_ALLOCATION_CHANGE_MODE,
    mode
});

export const loadData = (modes, results,members) => ({
    type: SALES_ALLOCATION_LOAD_DATA,
    modes,
    results,
    members
});

export const loadSectionData = (results,count) => ({
    type: SALES_ALLOCATION_LOAD_SECTION_DATA,
    results,
    count
});

export const selectRow = row => ({
    type: SALES_ALLOCATION_SELECT_ROW,
    row
});

export const changeSearchTerm = value=>({
    type: SALES_ALLOCATION_CHANGE_SEARCH_TERM,
    value
})

export const fetchSearchResults = (mode,searchTerm="",page=1,perPage=10,additional={})=>{
    let thunk = dispatch=>{
        agent.SalesAllocation.search(mode,searchTerm,page,perPage,additional).then(({results,count})=>{
            dispatch(loadSectionData(results,count));
        })
    }

    thunk.meta = {
        debounce: {
            time: 300,
            key:SEARCHING_RECORDS
        }
    }

    return thunk;
}

export const changePage = page=>({
    type:SALES_ALLOCATION_CHANGE_PAGE,
    page
})

export const changePerPage = perPage =>({
    type: SALES_ALLOCATION_CHANGE_PER_PAGE,
    perPage
})

export const changeMemberPercentage = (id,value)=>({
    type:SALES_ALLOCATION_CHANGE_MEMBER_PERCENTAGE,
    id,
    value
})

export const fetchData = (team)=>dispatch=>{
    agent.SalesAllocation.load(team).then(({modes,results,members})=>{
        dispatch(loadData(modes,results,members));
        dispatch(fetchSearchResults("towns"));
    })
}

export const submit = (team,modes,selected,members)=>dispatch=>{
    agent.SalesAllocation.save(team,modes,selected,members).then(({success,message})=>{
        dispatch(fetchData(team));
        if(success){
            dispatch(alertDialog(message,"success"));
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}