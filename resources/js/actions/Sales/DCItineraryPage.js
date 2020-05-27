import {
    DC_ITINERARY_DAY_TYPE_SELECT,
    DC_ITINERARY_DAY_TYPE_UNSELECT,
    DC_ITINERARY_LOAD,
    DC_ITINERARY_DATE_SELECT,
    DC_ITINERARY_USER_CHANGE,
    DC_ITINERARY_MODE_CHANGE,
    DC_ITINERARY_CLEAR_DATE,
    DC_ITINERARY_UPDATING_VALUES_CHANGE,
    DC_ITINERARY_CHANGE_DATE,
    DC_ITINERARY_UPDATING_VALUES_CANCEL,
    // DC_ITINERARY_AREA_CHANGE,
    DC_ITINERARY_TYPE_CHANGE
} from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeValues = (values) => ({
    type: DC_ITINERARY_UPDATING_VALUES_CHANGE,
    payload: { values }
});

export const confirmChanges = ()=>({
    type:DC_ITINERARY_CHANGE_DATE
})

export const cancelChanges = ()=>({
    type:DC_ITINERARY_UPDATING_VALUES_CANCEL
})

export const selectDayType = (date, dayType) => ({
    type: DC_ITINERARY_DAY_TYPE_SELECT,
    payload: { date, dayType }
});

export const unselectDayType = (date, dayType) => ({
    type: DC_ITINERARY_DAY_TYPE_UNSELECT,
    payload: { date, dayType }
});

export const loadDetails = (dates,approved,dayTypes,modes)=>({
    type: DC_ITINERARY_LOAD,
    payload: {dates,approved,dayTypes,modes}
});

export const selectDate = (date,mode)=>({
    type: DC_ITINERARY_DATE_SELECT,
    payload:{date,mode}
})

export const changeMode = mode =>({
    type: DC_ITINERARY_MODE_CHANGE,
    payload: {mode}
})

export const changeUser = user =>({
    type: DC_ITINERARY_USER_CHANGE,
    payload: {user}
})

// export const changeArea = area =>({
//     type: DC_ITINERARY_AREA_CHANGE,
//     payload: {area}
// })

export const clearDate = (date)=>({
    type: DC_ITINERARY_CLEAR_DATE,
    payload:{date}
})

export const fetchInformations = (user,year,month)=>dispatch=>{
    agent.DCItinerary.load(user,year,month).then(({success,message,dates,approved,dayTypes,modes})=>{
        if(success){
            dispatch(loadDetails(dates,approved,dayTypes,modes))
        } else {
            dispatch(alertDialog(message,'error'));
        }
    })
}

export const submit = (user,year,month,dates)=>dispatch=>{
    agent.DCItinerary.save(user,year,month,dates).then(({success,message})=>{
        if(success){
            dispatch(alertDialog(message,'success'))
        } else {
            dispatch(alertDialog(message,'error'))
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    });
}

export const changeType = type =>({
    type: DC_ITINERARY_TYPE_CHANGE,
    payload:{type}
})
