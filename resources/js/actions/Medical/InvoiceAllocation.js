import {
    INVOICE_ALLOCATION_CHANGE_TEAM,
    INVOICE_ALLOCATION_CHANGE_MODE,
    INVOICE_ALLOCATION_CHANGE_PAGE,
    INVOICE_ALLOCATION_CHANGE_PER_PAGE,
    INVOICE_ALLOCATION_SELECT_INVOICE,
    INVOICE_ALLOCATION_LOAD_DATA,
    INVOICE_ALLOCATION_LOAD_SEARCH_RESULTS,
    INVOICE_ALLOCATION_CHANGE_VALUES,
    INVOICE_ALLOCATION_CHANGE_PANEL,
    INVOICE_ALLOCATION_CHANGE_PRODUCT_MODE,
    INVOICE_ALLOCATION_CHECK_PRODUCTS,
    INVOICE_ALLOCATION_LOAD_PRODUCTS,
    INVOICE_ALLOCATION_CHANGE_PRODUCTS_PAGE,
    INVOICE_ALLOCATION_CHANGE_PRODUCTS_PER_PAGE,
    INVOICE_ALLOCATION_CHANGE_PRODUCT_KEYWORD,
    INVOICE_ALLOCATION_CHANGE_MEMBER_QTY
} from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeTeam = team => ({
    type: INVOICE_ALLOCATION_CHANGE_TEAM,
    team
});

export const changeMode = mode => ({
    type: INVOICE_ALLOCATION_CHANGE_MODE,
    mode
});

export const changePage = page => ({
    type: INVOICE_ALLOCATION_CHANGE_PAGE,
    page
});

export const changePerPage = perPage => ({
    type: INVOICE_ALLOCATION_CHANGE_PER_PAGE,
    perPage
});

export const selectInvoice = row => ({
    type: INVOICE_ALLOCATION_SELECT_INVOICE,
    row
});

export const loadData = (results,mode,teamMembers,productSelected) => ({
    type: INVOICE_ALLOCATION_LOAD_DATA,
    results,
    mode,
    teamMembers,
    productSelected
});

export const loadSearch = (results,count) => ({
    type: INVOICE_ALLOCATION_LOAD_SEARCH_RESULTS,
    results,
    count
});

export const searchInvoices = (team, terms, page, perPage) => dispatch => {
    agent.InvoiceAllocation.search(team, terms, page, perPage)
        .then(({ results, success,count }) => {
            if (success) {
                dispatch(loadSearch(results,count));
            }
        })
        .catch(err => {
            dispatch(alertDialog(err.response.data.message, "error"));
        });
};

export const fetchData = team => dispatch => {
    agent.InvoiceAllocation.load(team)
        .then(({ success, selected,mode,teamMembers,productSelected }) => {
            if (success) {
                dispatch(loadData(selected,mode,teamMembers,productSelected));
            }
        })
        .catch(err => {
            dispatch(alertDialog(err.response.data.message, "error"));
        });
};

export const changeSearchTerms = values=>({
    type: INVOICE_ALLOCATION_CHANGE_VALUES,
    values
});

export const changePanel = panel =>({
    type: INVOICE_ALLOCATION_CHANGE_PANEL,
    panel
})

export const submit = (team,mode,selected,productChecked,teamMembers)=>dispatch=>{
    agent.InvoiceAllocation.save(team,mode,selected,productChecked,teamMembers).then(({success,message})=>{
        if(success){
            dispatch( alertDialog(message,'success'));
            dispatch(fetchData(team))
        }
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, "error"));
    });
}

export const changeProductMode = mode =>({
    type:INVOICE_ALLOCATION_CHANGE_PRODUCT_MODE,
    productMode: mode
});

export const checkProduct = row =>({
    type: INVOICE_ALLOCATION_CHECK_PRODUCTS,
    row
});

export const loadProducts = (results,count) =>({
    type: INVOICE_ALLOCATION_LOAD_PRODUCTS,
    results,count
});

export const changeProductPage = page=>({
    type: INVOICE_ALLOCATION_CHANGE_PRODUCTS_PAGE,
    page
});

export const changeProductPerPage = perPage =>({
    type: INVOICE_ALLOCATION_CHANGE_PRODUCTS_PER_PAGE,
    perPage
});

export const searchProducts = (invoices,keyword,page,perPage) =>dispatch=>{
    agent.InvoiceAllocation.searchProducts(invoices,keyword,page,perPage).then(({results,count})=>{
        dispatch(loadProducts(results,count));
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, "error"));
    });
}

export const changeProductKeyword = keyword=>({
    type: INVOICE_ALLOCATION_CHANGE_PRODUCT_KEYWORD,
    keyword
});

export const changeMemberQty = (id,value)=>({
    type: INVOICE_ALLOCATION_CHANGE_MEMBER_QTY,
    id,
    value
})