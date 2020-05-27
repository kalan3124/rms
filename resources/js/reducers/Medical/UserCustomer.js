import { 
    USER_CUSTOMERS_LOADED,
    USER_CUSTOMER_RIGHT_TAB_CHANGE,
    USER_CUSTOMER_LEFT_TAB_CHANGE,
    USER_CUSTOMER_USER_NAME_CHANGE,
    USER_CUSTOMER_USER_SELECT,
    USER_CUSTOMER_RESULTS_LOADED,
    USER_CUSTOMER_KEYWORD_CHANGE, 
    USER_CUSTOMER_USERS_LOADED,
    USER_CUSTOMER_USER_MENU_OPEN,
    USER_CUSTOMER_USER_MENU_CLOSE,
    USER_CUSTOMER_SUB_TOWN_CHANGE,
    USER_CUSTOMER_CHEMISTS_CHANGE,
    USER_CUSTOMER_DOCTORS_CHANGE,
    USER_CUSTOMER_STAFFS_CHANGE
} from "../../constants/actionTypes";

const initalState = {
    doctors:{},
    chemists:{},
    staffs:{},
    results:[],
    keyword:"",
    leftTab:0,
    rightTab:0,
    userName:"",
    users:[],
    userMenuOpen:false,
    subTown:undefined,
    userMenuRef:undefined
}

export default (state=initalState,{type,payload})=>{
    switch (type) {
        case USER_CUSTOMERS_LOADED:
            return {
                ...state,
                chemists:payload.chemists,
                doctors: payload.doctors,
                staffs: payload.staffs
            };
        case USER_CUSTOMER_RIGHT_TAB_CHANGE:
            return {
                ...state,
                rightTab: payload.tab
            };
        case USER_CUSTOMER_LEFT_TAB_CHANGE:
            return {
                ...state,
                leftTab: payload.tab
            };
        case USER_CUSTOMER_USER_NAME_CHANGE:
            return {
                ...state,
                userName: payload.userName
            };
        case USER_CUSTOMER_RESULTS_LOADED:
            return {
                ...state,
                results: payload.results
            };
        case USER_CUSTOMER_KEYWORD_CHANGE:
            return {
                ...state,
                keyword: payload.keyword
            };
        case USER_CUSTOMER_USERS_LOADED:
            return {
                ...state,
                users: payload.users
            };
        case USER_CUSTOMER_USER_MENU_OPEN:
            return {
                ...state,
                userMenuOpen:true,
                userMenuRef:payload.userMenuRef
            };
        case USER_CUSTOMER_USER_MENU_CLOSE:
            return {
                ...state,
                userMenuOpen:false
            };
        case USER_CUSTOMER_SUB_TOWN_CHANGE:
            return {
                ...state,
                subTown: payload.subTown
            };
        case USER_CUSTOMER_CHEMISTS_CHANGE:
            return {
                ...state,
                chemists: payload.chemists
            }
        case USER_CUSTOMER_DOCTORS_CHANGE:
            return {
                ...state,
                doctors: payload.doctors
            }
        case USER_CUSTOMER_STAFFS_CHANGE:
            return {
                ...state,
                staffs: payload.staffs
            }
        default:
            return state;
    }
}