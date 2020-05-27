import { SIDEBAR_LOADED, SIDEBAR_LOADING } from "../constants/actionTypes";

const initialState = {
    sidebarLoading:false,
    sidebarItems:{}
}

export default (state=initialState,action)=>{
    switch (action.type) {
        case SIDEBAR_LOADING:
            return {
                ...state,
                sidebarLoading:true
            }
        case SIDEBAR_LOADED:
            return {
                ...state,
                sidebarLoading:false,
                sidebarItems:action.payload.items
            }
        default:
            return state;
    }
}