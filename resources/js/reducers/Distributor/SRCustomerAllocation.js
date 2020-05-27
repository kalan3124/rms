import { SR_CUSTOMER_CHANGE_CUSTOMER_KEYWORD, SR_CUSTOMER_CHANGE_SR_KEYWORD, SR_CUSTOMER_ADD_CUSTOMERS, SR_CUSTOMER_ADD_SRS, SR_CUSTOMER_LOADED_SRS, SR_CUSTOMER_LOADED_CUSTOMERS, SR_CUSTOMER_REMOVE_CUSTOMERS, SR_CUSTOMER_REMOVE_SRS, SR_CUSTOMER_CLEAR_PAGE, SR_CUSTOMER_LOADED_CHECKED_CUSTOMERS } from "../../constants/actionTypes";

const initialState = {  
    customerKeyword: "",
    srKeyword: "",
    customers:[],
    srs:[],
    selectedCustomers:[],
    selectedSrs:[],
    area:[]
}

export default (state=initialState,{payload,type})=>{
    switch (type) {
        case SR_CUSTOMER_CHANGE_CUSTOMER_KEYWORD:
            return {
                ...state,
                customerKeyword: payload.keyword
            };
        case SR_CUSTOMER_CHANGE_SR_KEYWORD:
            return {
                ...state,
                srKeyword: payload.keyword
            };
        case SR_CUSTOMER_LOADED_CUSTOMERS:
            return {
                ...state,
                customers:payload.customers
            };
        case SR_CUSTOMER_LOADED_SRS:
            return {
                ...state,
                srs:payload.srs 
            };
        case SR_CUSTOMER_ADD_CUSTOMERS:
            return {
                ...state,
                selectedCustomers: [
                    ...state.selectedCustomers,
                    payload.customer
                ]
            };
        case SR_CUSTOMER_ADD_SRS:
            return {
                ...state,
                selectedSrs: [
                    ...state.selectedSrs,
                    payload.sr
                ]
            };
        case SR_CUSTOMER_REMOVE_SRS:
            return {
                ...state,
                selectedSrs: state.selectedSrs.filter(sr=>sr.value!=payload.sr.value)
            };
        case SR_CUSTOMER_REMOVE_CUSTOMERS:
            return {
                ...state,
                selectedCustomers: state.selectedCustomers.filter(customer=>customer.value!=payload.customer.value)
            };
        case SR_CUSTOMER_CLEAR_PAGE:
            return {
                ...state,
                selectedCustomers:[],
                selectedSrs:[]
            };
        case SR_CUSTOMER_LOADED_CHECKED_CUSTOMERS:
            return {
                ...state,
                selectedCustomers:[...payload.customers]
            };
        default:
            return state;
    }
}