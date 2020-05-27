import {
    HEADER_USER_MENU_TOGGLE
} from '../constants/actionTypes';


const initialState = {
    userMenuExpanded:false,
    profileInfoAnchor:undefined
}

export default (state=initialState,action)=>{
    switch (action.type) {
        case HEADER_USER_MENU_TOGGLE:
            return {
                ...state,
                userMenuExpanded:!state.userMenuExpanded,
                profileInfoAnchor:action.payload.element
            }
        default:
            return state;
    }
}
