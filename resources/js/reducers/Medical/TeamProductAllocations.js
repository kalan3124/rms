import { TEAM_PROD_ALLOC_TEAM_CHANGE, TEAM_PROD_ALLOC_DATA_LOADED, TEAM_PROD_ALLOC_CHANGE_DATA } from "../../constants/actionTypes";

const initialState = {
    team:undefined,
    unallocated:[],
    allocated:{},
    members:[]
}

export default (state=initialState,action)=>{
    switch (action.type) {
        case TEAM_PROD_ALLOC_TEAM_CHANGE:
            return {
                ...state,
                team:action.payload.team
            }
        case TEAM_PROD_ALLOC_DATA_LOADED:
            return {
                ...state,
                unallocated:action.payload.unallocated,
                allocated:action.payload.allocated,
                members:action.payload.members
            }
        case TEAM_PROD_ALLOC_CHANGE_DATA:
            return {
                ...state,
                unallocated:action.payload.unallocated,
                allocated:action.payload.allocated
            }
        default:
            return state;
    }
}