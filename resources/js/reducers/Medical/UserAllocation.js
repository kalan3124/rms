import { USER_ALLOCATIONS_USER_CHANGE } from "../../constants/actionTypes";

const initialState = {
    user:undefined,
    path:"",
};

export default (state=initialState,{type,payload})=>{
    switch (type) {
        case USER_ALLOCATIONS_USER_CHANGE:
           return {
               ...state,
               user:payload.user,
               path:payload.path
           }
        default:
            return state;
    }
}