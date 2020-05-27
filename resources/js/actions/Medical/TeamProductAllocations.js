import { TEAM_PROD_ALLOC_TEAM_CHANGE, TEAM_PROD_ALLOC_DATA_LOADED, TEAM_PROD_ALLOC_CHANGE_DATA } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeTeam = team=>({
    type:TEAM_PROD_ALLOC_TEAM_CHANGE,
    payload:{team}
})

export const dataLoaded = (allocated,members,unallocated)=>({
    type:TEAM_PROD_ALLOC_DATA_LOADED,
    payload:{allocated,members,unallocated}
})

export const fetchTeam = team=>dispatch=>{
    dispatch(changeTeam(team));

    agent.TeamProductAllocations.load(team).then(({allocated,members,unallocated})=>{
        dispatch(dataLoaded({...allocated},members,unallocated));
    })
}

export const changeData = (allocated,unallocated)=>({
    type:TEAM_PROD_ALLOC_CHANGE_DATA,
    payload:{allocated,unallocated}
})

export const submitData = (team,allocated)=>dispatch=>{
    if(!team) {
        dispatch(alertDialog("Please select a team to assign products.","warning"));
        return;
    }

    agent.TeamProductAllocations.save(team,allocated).then(data=>{
        dispatch(alertDialog(data.message,"success"));
        dispatch(fetchTeam(team));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'))
    })
}