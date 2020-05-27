import { ITINERARY_APPROVAL_CHANGE_TYPE, ITINERARY_APPROVAL_RESULT_LOADED, ITINERARY_APPROVAL_CHANGE_TEAM, ITINERARY_APPROVAL_OPEN_ITINERARY, ITINERARY_APPROVAL_CLOSE_ITINERARY,ITINERARY_APPROVAL_CHANGE_DIVISION } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeTeam = team=>({
    type:ITINERARY_APPROVAL_CHANGE_TEAM,
    payload:{team}
});

export const changeDivision = division=>({
    type:ITINERARY_APPROVAL_CHANGE_DIVISION,
    payload:{division}
});

export const changeType = type=>({
    type:ITINERARY_APPROVAL_CHANGE_TYPE,
    payload:{type}
});

export const loadedResults = (results,count)=>({
    type:ITINERARY_APPROVAL_RESULT_LOADED,
    payload:{results,count}
});

export const fetchResults = (division,team,type)=>dispatch=>{
    agent.ItineraryApproval.search(division,team,type).then(({results,count})=>{
        dispatch(loadedResults(results,count))
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const approve = id =>dispatch=>{
    agent.ItineraryApproval.approve(id).then(({success,message})=>{
        if(success)
            dispatch(alertDialog(message,"success"))
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const fetchItinerary = id =>dispatch=>{
    agent.ItineraryViewer.load(id).then(({message,success,dates})=>{
        if(success)
            dispatch(openItinerary(id,dates));
    })
}

export const openItinerary = (id,dates)=>({
    type:ITINERARY_APPROVAL_OPEN_ITINERARY,
    payload:{id,dates}
});

export const closeItinerary = ()=>({
    type:ITINERARY_APPROVAL_CLOSE_ITINERARY
})