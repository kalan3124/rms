import { STANDARD_ITINERARY_REP_CHANGE, STANDARD_ITINERARY_FORM_OPEN, STANDARD_ITINERARY_FORM_CLOSE,STANDARD_ITINERARY_NEW_DATE_CHANGED, STANDARD_ITINERARY_DATA_CHANGED,STANDARD_ITINERARY_DIVISION_CHANGE,STANDARD_ITINERARY_TEAM_CHANGE } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeRep = rep=>({
    type:STANDARD_ITINERARY_REP_CHANGE,
    payload:{rep}
});

export const changeDivision = division=>({
    type:STANDARD_ITINERARY_DIVISION_CHANGE,
    payload:{division}
});

export const changeTeam = team=>({
    type:STANDARD_ITINERARY_TEAM_CHANGE,
    payload:{team}
});

export const openForm = ()=>({
    type:STANDARD_ITINERARY_FORM_OPEN
});

export const closeForm = ()=>({
    type:STANDARD_ITINERARY_FORM_CLOSE
});

export const changeFormData = formData=>({
    type:STANDARD_ITINERARY_NEW_DATE_CHANGED,
    payload:{formData}
});

export const changeData = data=>({
    type:STANDARD_ITINERARY_DATA_CHANGED,
    payload:{data}
});

export const saveData = (rep,data)=>dispatch=>{
    agent.StandardItinerary.save(rep,data).then(data=>{
        if(data.success){
            dispatch(alertDialog(data.message,"success"));
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const loadData = (rep)=>dispatch=>{

    dispatch(changeRep(rep));

    if(!rep){
        dispatch(changeData({}))
        return;
    }

    agent.StandardItinerary.load(rep).then(data=>{
        dispatch(changeData(data))
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
        dispatch(changeData({}))
    })
}