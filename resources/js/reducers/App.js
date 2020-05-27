import {
    USER_LOADING,
    USER_ACCESS,
    GUEST_ACCESS,
    XHR_ERROR
} from '../constants/actionTypes';

const today = new Date();

const initialState = {
    user:undefined,
    userLoading:false,
    error:false,
    timeDif:0
}

export default (state=initialState,action)=>{
    switch (action.type) {
        case USER_LOADING:
            return {
                ...state,
                userLoading:true
            }
        case USER_ACCESS:
            return {
                ...state,
                user:action.payload.user,
                userLoading:false,
                timeDif:Math.abs(new Date() - new Date(action.payload.time))
            }
        case GUEST_ACCESS:
            return {
                ...state,
                userLoading:false
            }
        case XHR_ERROR:
            return {
                ...state,
                error:'XHR'
            }
        default:
            return state;
    }
}