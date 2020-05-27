import {
    USER_AREA_TAB_CHANGED,
    USER_AREA_TERRITORY_LEVELS_LOADED,
    USER_AREA_TERRITORIES_LOADED,
    USER_AREA_TERRITORY_NAME_CHANGE,
    USER_AREA_USER_NAME_CHANGE, 
    USER_AREA_USERS_LOADED,
    USER_AREA_USER_MENU_OPEN,
    USER_AREA_USER_MENU_CLOSE,
    USER_AREA_USER_SELECT,
    USER_AREA_USER_TAB_CHANGED,
    USER_AREA_LOAD_INFORMATION,
    USER_AREA_CHANGE_AREA
} from "../../constants/actionTypes";

const initialState = {
    tab:0,
    territoryLevels:[],
    territories:[],
    territoryName:"",
    userName:"",
    users:[],
    userMenuOpen:false,
    userMenuRef:null,
    user:null,
    activeUserTab:0,
    levels:[],
    areas:{}
};

export default (state=initialState,action)=>{
    switch (action.type) {
        case USER_AREA_TAB_CHANGED:
            return {
                ...state,
                tab:action.payload.tab
            };
        case USER_AREA_TERRITORY_LEVELS_LOADED:
            return {
                ...state,
                territoryLevels: action.payload.territoryLevels
            }
        case USER_AREA_TERRITORIES_LOADED:
            return {
                ...state,
                territories:action.payload.territories
            }
        case USER_AREA_TERRITORY_NAME_CHANGE:
            return {
                ...state,
                territoryName:action.payload.territoryName
            }
        case USER_AREA_USER_NAME_CHANGE:
            return {
                ...state,
                userName:action.payload.userName
            }
        case USER_AREA_USERS_LOADED:
            return {
                ...state,
                users:action.payload.users
            }
        case USER_AREA_USER_MENU_OPEN:
            return {
                ...state,
                userMenuRef:action.payload.userMenuRef,
                userMenuOpen:true
            }
        case USER_AREA_USER_MENU_CLOSE:
            return {
                ...state,
                userMenuOpen:false
            }
        case USER_AREA_USER_SELECT:
            return {
                ...state,
                user:action.payload.user
            }
        case USER_AREA_USER_TAB_CHANGED:
            return {
                ...state,
                activeUserTab:action.payload.tab
            }
        case USER_AREA_LOAD_INFORMATION:
            return {
                ...state,
                levels:action.payload.levels,
                areas:action.payload.areas
            }
        case USER_AREA_CHANGE_AREA:
            return {
                ...state,
                areas:action.payload.areas
            }
        default:
            return state;
    }
}