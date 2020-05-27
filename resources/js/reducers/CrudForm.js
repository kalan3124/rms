import { CRUD_SEARCH_TOGGLE } from "../constants/actionTypes";

const initialState = {
    search:false
}

export default (state=initialState,action)=>{
    switch (action.type) {
        case CRUD_SEARCH_TOGGLE:
            return {
                ...state,
                search:!state.search
            }
        default:
            return state;
    }
}