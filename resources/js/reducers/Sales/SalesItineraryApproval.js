import { SALES_ITINERARY_APPROVAL_CHANGE_USER,SALES_ITINERARY_APPROVAL_CHANGE_TYPE,SALES_ITINERARY_APPROVAL_RESULT_LOADED,SALES_ITINERARY_APPROVAL_OPEN_ITINERARY,SALES_ITINERARY_APPROVAL_CLOSE_ITINERARY,SALES_ITINERARY_APPROVAL_CHANGE_DIVISION,SALES_ITINERARY_APPROVAL_CHANGE_AREA, SALES_ITINERARY_APPROVAL_CHANGE_MODE } from "../../constants/actionTypes";

const initialState = {
     results:[],
     user:undefined,
     type:1,
     totalResults:0,
     searched:false,
     openedItinerary:undefined,
     dates:[],
     area:undefined,
     mode: undefined
};

export default (state=initialState,{type,payload})=>{
     switch (type) {
     case SALES_ITINERARY_APPROVAL_CHANGE_USER:
         return {
             ...state,
             user:payload.user
         };
     case SALES_ITINERARY_APPROVAL_CHANGE_TYPE:
         return {
             ...state,
             type:payload.type
         };
     case SALES_ITINERARY_APPROVAL_RESULT_LOADED:
         return {
             ...state,
             results: payload.results,
             totalResults: payload.count,
             searched:true
         };
     case SALES_ITINERARY_APPROVAL_OPEN_ITINERARY:
         return {
             ...state,
             openedItinerary: payload.id,
             dates:payload.dates
         };
     case SALES_ITINERARY_APPROVAL_CLOSE_ITINERARY:
         return {
             ...state,
             openedItinerary:false
         };
    case SALES_ITINERARY_APPROVAL_CHANGE_AREA:
         return {
             ...state,
             area:payload.area
         };
    case SALES_ITINERARY_APPROVAL_CHANGE_MODE:
        return {
            ...state,
            mode:payload.mode
        }
     default:
         return state;
     }
}