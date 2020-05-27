import { STANDARD_ITINERARY_REP_CHANGE, STANDARD_ITINERARY_FORM_OPEN, STANDARD_ITINERARY_FORM_CLOSE, STANDARD_ITINERARY_NEW_DATE_CHANGED, STANDARD_ITINERARY_DATA_CHANGED,STANDARD_ITINERARY_DIVISION_CHANGE,STANDARD_ITINERARY_TEAM_CHANGE } from "../../constants/actionTypes";

const initialState = {
    data:{},
    rep:undefined,
    formOpen:false,
    formData:{

    },
    division:undefined,
    team:undefined
}

export default (state=initialState,action)=>{
    switch (action.type) {
        case STANDARD_ITINERARY_REP_CHANGE:
            return {
                ...state,
                rep:action.payload.rep
            };
        case STANDARD_ITINERARY_FORM_OPEN:
            return {
                ...state,
                formOpen:true,
            };
        case STANDARD_ITINERARY_FORM_CLOSE:
            return {
                ...state,
                formOpen:false
            };
        case STANDARD_ITINERARY_NEW_DATE_CHANGED:
            return {
                ...state,
                formData:action.payload.formData
            }
        case STANDARD_ITINERARY_DATA_CHANGED:
            return {
                ...state,
                data:action.payload.data
            };
        case STANDARD_ITINERARY_DIVISION_CHANGE:
            return {
                ...state,
                division:action.payload.division
            };
        case STANDARD_ITINERARY_TEAM_CHANGE:
            return {
                ...state,
                team:action.payload.team
            };
        default:
            return state;
    }
}