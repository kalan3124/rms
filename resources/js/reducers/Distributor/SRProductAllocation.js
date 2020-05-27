import { SR_PRODUCT_CHANGE_PRODUCT_KEYWORD, SR_PRODUCT_CHANGE_SR_KEYWORD, SR_PRODUCT_ADD_PRODUCTS, SR_PRODUCT_ADD_SRS, SR_PRODUCT_LOADED_SRS, SR_PRODUCT_LOADED_PRODUCTS, SR_PRODUCT_REMOVE_PRODUCTS, SR_PRODUCT_REMOVE_SRS, SR_PRODUCT_CLEAR_PAGE, SR_PRODUCT_LOADED_CHECKED_PRODUCTS } from "../../constants/actionTypes";

const initialState = {  
    productKeyword: "",
    srKeyword: "",
    products:[],
    srs:[],
    selectedProducts:[],
    selectedSrs:[],
    area:[]
}

export default (state=initialState,{payload,type})=>{
    switch (type) {
        case SR_PRODUCT_CHANGE_PRODUCT_KEYWORD:
            return {
                ...state,
                productKeyword: payload.keyword
            };
        case SR_PRODUCT_CHANGE_SR_KEYWORD:
            return {
                ...state,
                srKeyword: payload.keyword
            };
        case SR_PRODUCT_LOADED_PRODUCTS:
            return {
                ...state,
                products:payload.products
            };
        case SR_PRODUCT_LOADED_SRS:
            return {
                ...state,
                srs:payload.srs 
            };
        case SR_PRODUCT_ADD_PRODUCTS:
            return {
                ...state,
                selectedProducts: [
                    ...state.selectedProducts,
                    payload.product
                ]
            };
        case SR_PRODUCT_ADD_SRS:
            return {
                ...state,
                selectedSrs: [
                    ...state.selectedSrs,
                    payload.sr
                ]
            };
        case SR_PRODUCT_REMOVE_SRS:
            return {
                ...state,
                selectedSrs: state.selectedSrs.filter(sr=>sr.value!=payload.sr.value)
            };
        case SR_PRODUCT_REMOVE_PRODUCTS:
            return {
                ...state,
                selectedProducts: state.selectedProducts.filter(product=>product.value!=payload.product.value)
            };
        case SR_PRODUCT_CLEAR_PAGE:
            return {
                ...state,
                selectedProducts:[],
                selectedSrs:[]
            };
        case SR_PRODUCT_LOADED_CHECKED_PRODUCTS:
            return {
                ...state,
                selectedProducts:[...payload.products]
            };
        default:
            return state;
    }
}