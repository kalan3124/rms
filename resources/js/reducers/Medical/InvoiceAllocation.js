import { INVOICE_ALLOCATION_CHANGE_TEAM, INVOICE_ALLOCATION_CHANGE_MODE, INVOICE_ALLOCATION_CHANGE_PAGE, INVOICE_ALLOCATION_CHANGE_PER_PAGE, INVOICE_ALLOCATION_SELECT_INVOICE, INVOICE_ALLOCATION_LOAD_DATA, INVOICE_ALLOCATION_LOAD_SEARCH_RESULTS, INVOICE_ALLOCATION_CHANGE_VALUES, INVOICE_ALLOCATION_CHANGE_PANEL, INVOICE_ALLOCATION_CHANGE_PRODUCT_MODE, INVOICE_ALLOCATION_CHECK_PRODUCTS, INVOICE_ALLOCATION_LOAD_PRODUCTS, INVOICE_ALLOCATION_CHANGE_PRODUCTS_PAGE, INVOICE_ALLOCATION_CHANGE_PRODUCTS_PER_PAGE, INVOICE_ALLOCATION_CHANGE_PRODUCT_KEYWORD, INVOICE_ALLOCATION_CHANGE_MEMBER_QTY } from "../../constants/actionTypes";

const initialState = {
    team:undefined,
    teamMembers:{},
    checked:{},
    searchedResults:{},
    mode:"include",
    page:1,
    perPage:10,
    count:0,
    values:{},
    panel:-1,
    productMode: "include",
    productChecked:{},
    products:{},
    productPage:1,
    productPerPage:10,
    productKeyword:"",
    productCount:0,
}

export default (state=initialState,action)=>{
    switch (action.type) {
        case INVOICE_ALLOCATION_CHANGE_TEAM:
            return {
                ...state,
                team:action.team,
                panel: action.team?0:-1
            };
        case INVOICE_ALLOCATION_CHANGE_MODE:
            return {
                ...state,
                mode: action.mode,
                checked:{}
            };
        case INVOICE_ALLOCATION_CHANGE_PAGE:
            return {
                ...state,
                page: action.page
            };
        case INVOICE_ALLOCATION_CHANGE_PER_PAGE:
            return {
                ...state,
                perPage: action.perPage
            };
        case INVOICE_ALLOCATION_SELECT_INVOICE:
            let newChecked = {...state.checked};

            if(newChecked[action.row.id]){
                delete newChecked[action.row.id];
            } else {
                newChecked[action.row.id] = action.row;
            }

            return {
                ...state,
                checked:newChecked
            };
        case INVOICE_ALLOCATION_LOAD_DATA:
            return {
                ...state,
                checked:action.results.mapToObject('id'),
                mode: action.mode,
                page:1,
                perPage:10,
                teamMembers:action.teamMembers.mapToObject('id'),
                panel:0,
                productChecked: action.productSelected.mapToObject('id')
            };
        case INVOICE_ALLOCATION_LOAD_SEARCH_RESULTS:
            return {
                ...state,
                searchedResults:action.results.mapToObject('id'),
                count:action.count
            };
        case INVOICE_ALLOCATION_CHANGE_VALUES:
            return {
                ...state,
                values: action.values
            };
        case INVOICE_ALLOCATION_CHANGE_PANEL:
            return {
                ...state,
                panel: action.panel
            };
        case INVOICE_ALLOCATION_CHANGE_PRODUCT_MODE:
            return {
                ...state,
                productMode:action.mode
            };
        case INVOICE_ALLOCATION_CHECK_PRODUCTS:
            return {
                ...state,
                productChecked:action.row
            };
        case INVOICE_ALLOCATION_LOAD_PRODUCTS:
            return {
                ...state,
                products: action.results.mapToObject('id'),
                productCount: action.count
            };
        case INVOICE_ALLOCATION_CHANGE_PRODUCTS_PAGE:
            return {
                ...state,
                productPage: action.page
            };
        case INVOICE_ALLOCATION_CHANGE_PRODUCTS_PER_PAGE:
            return {
                ...state,
                productPerPage: action.perPage
            };
        case INVOICE_ALLOCATION_CHANGE_PRODUCT_KEYWORD:
            return {
                ...state,
                productKeyword: action.keyword
            };
        case INVOICE_ALLOCATION_CHANGE_MEMBER_QTY:
            return {
                ...state,
                teamMembers:{
                    ...state.teamMembers,
                    [action.id]:{
                        ...state.teamMembers[action.id],
                        value:action.value
                    }
                }
            };
        default:
            return state;
    }
}