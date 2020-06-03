import { DASHBOARD_LOADED } from "../constants/actionTypes";

const initialState = {
    items:{
        main:[],
        sales:[],
        common:[],
        distributor:[]
    }
}

export default (state=initialState,action)=>{
    switch (action.type) {
        case DASHBOARD_LOADED:
            return {
                ...state,
                items:action.payload.items
            }
        default:
            return state;
    }
}
