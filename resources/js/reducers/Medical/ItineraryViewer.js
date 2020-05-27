import { ITINERARY_VIEW_CHANGE_USER, ITINERARY_VIEW_CHANGE_DATE, ITINERARY_VIEW_LOAD_ITINERARIES, ITINERARY_VIEW_LOAD_ITINERARY, ITINERARY_VIEW_CALENDAR_CLOSE } from "../../constants/actionTypes";

const initialState = {
    user:undefined,
    itineraries:[],
    month:undefined,
    calendarOpen:false,
    dates:{}
};

export default (state=initialState,{type,payload})=>{
    switch (type) {
    case ITINERARY_VIEW_CHANGE_USER:
        return {
            ...state,
            user:payload.user
        };
    case ITINERARY_VIEW_CHANGE_DATE:
        return {
            ...state,
            month: payload.month
        };
    case ITINERARY_VIEW_LOAD_ITINERARIES:
        return {
            ...state,
            itineraries: payload.itineraries
        };
    case ITINERARY_VIEW_LOAD_ITINERARY:
        return {
            ...state,
            dates:payload.dates.mapToObject('date'),
            calendarOpen:true
        };
    case ITINERARY_VIEW_CALENDAR_CLOSE:
        return {
            ...state,
            calendarOpen:false,
            dates:{}
        };
    default:
        return state;
    }
}