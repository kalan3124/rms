import {  ITINERARY_APPROVAL_CHANGE_TYPE, ITINERARY_APPROVAL_RESULT_LOADED, ITINERARY_APPROVAL_CHANGE_TEAM, ITINERARY_APPROVAL_OPEN_ITINERARY, ITINERARY_APPROVAL_CLOSE_ITINERARY,ITINERARY_APPROVAL_CHANGE_DIVISION } from "../../constants/actionTypes";

const initialState = {
    results:[],
    team:undefined,
    type:1,
    totalResults:0,
    searched:false,
    openedItinerary:undefined,
    dates:[],
    division:undefined
};

export default (state=initialState,{type,payload})=>{
    switch (type) {
    case ITINERARY_APPROVAL_CHANGE_TEAM:
        return {
            ...state,
            team:payload.team
        };
    case ITINERARY_APPROVAL_CHANGE_DIVISION:
        return {
            ...state,
            division:payload.division
        };
    case ITINERARY_APPROVAL_CHANGE_TYPE:
        return {
            ...state,
            type:payload.type
        };
    case ITINERARY_APPROVAL_RESULT_LOADED:
        return {
            ...state,
            results: payload.results,
            totalResults: payload.count,
            searched:true
        };
    case ITINERARY_APPROVAL_OPEN_ITINERARY:
        return {
            ...state,
            openedItinerary: payload.id,
            dates:payload.dates
        };
    case ITINERARY_APPROVAL_CLOSE_ITINERARY:
        return {
            ...state,
            openedItinerary:false
        }
    default:
        return state;
    }
}