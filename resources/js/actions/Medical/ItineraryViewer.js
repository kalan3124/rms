import { ITINERARY_VIEW_CHANGE_USER, ITINERARY_VIEW_CHANGE_DATE, ITINERARY_VIEW_LOAD_ITINERARIES, ITINERARY_VIEW_LOAD_ITINERARY, ITINERARY_VIEW_CALENDAR_CLOSE } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeUser = user=>({
    type:ITINERARY_VIEW_CHANGE_USER,
    payload:{user}
});

export const changeMonth = month=>({
    type:ITINERARY_VIEW_CHANGE_DATE,
    payload:{month}
});

export const loadItineraries = itineraries=>({
    type:ITINERARY_VIEW_LOAD_ITINERARIES,
    payload:{itineraries}
});

export const fetchItineries = (user,month)=>dispatch=>{
    agent.ItineraryViewer.search(user,month).then(({message,success,itineraries})=>{
        if(success){
            dispatch(loadItineraries(itineraries));
            if(!itineraries.length){
                dispatch(alertDialog("No itineraries found for this month.",'error'))
            }
        } else {
            dispatch(alertDialog(message,'error'))
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'))
    })
}

export const loadItinerary = dates=>({
    type:ITINERARY_VIEW_LOAD_ITINERARY,
    payload:{dates}
});

export const calendarClose = ()=>({
    type:ITINERARY_VIEW_CALENDAR_CLOSE
})

export const fetchItinerary = id =>dispatch=>{
    agent.ItineraryViewer.load(id).then(({dates})=>{
        dispatch(loadItinerary(dates))
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'))
    })
}