import { 
    TEAM_PRODUCT_CHANGE_CHECKED_PRODUCTS,
    TEAM_PRODUCT_CHANGE_CHECKED_TEAMS,
    TEAM_PRODUCT_PRODUCTS_LOADED,
    TEAM_PRODUCT_TEAMS_LOADED,
    TEAM_PRODUCT_CHANGE_PRODUCT_NAME,
    TEAM_PRODUCT_CHANGE_TEAM_NAME,
    TEAM_PRODUCT_CLEAR_PAGE,
    TEAM_PRODUCT_APPEND_CHECKED_PRODUCTS,
    TEAM_PRODUCT_CHANGE_PRINCIPAL
} from "../../constants/actionTypes";

const initialState = {
    productResults:[],
    productChecked:[],
    teamResults:[],
    teamChecked:[],
    productName:"",
    teamName:"",
    principal:[]
}

export default (state=initialState,{type,payload})=>{
    switch (type) {
        case TEAM_PRODUCT_CHANGE_CHECKED_PRODUCTS:
            return {
                ...state,
                productChecked: payload.productChecked
            };
        case TEAM_PRODUCT_APPEND_CHECKED_PRODUCTS:
            return {
                ...state,
                productChecked:[...state.productChecked,...payload.products]
            };
        case TEAM_PRODUCT_CHANGE_CHECKED_TEAMS:
            return {
                ...state,
                teamChecked: payload.teamChecked
            };
        case TEAM_PRODUCT_PRODUCTS_LOADED:
            return {
                ...state,
                productResults: payload.productResults
            };
        case TEAM_PRODUCT_TEAMS_LOADED:
            return {
                ...state,
                teamResults: payload.teamResults
            };
        case TEAM_PRODUCT_CHANGE_PRODUCT_NAME:
            return {
                ...state,
                productName: payload.productName
            };
        case TEAM_PRODUCT_CHANGE_TEAM_NAME:
            return {
                ...state,
                teamName: payload.teamName
            };
        case TEAM_PRODUCT_CLEAR_PAGE:
            return {
                ...state,
                teamChecked:[],
                productChecked:[]
            };
        case TEAM_PRODUCT_CHANGE_PRINCIPAL:
            return {
                ...state,
                principal:payload.principal
            };
        default:
            return state;
    }
}