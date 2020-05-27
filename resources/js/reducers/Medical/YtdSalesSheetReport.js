import { YTD_SALES_SHEET_REPORT_LOAD_RESULT,YTD_SALES_SHEET_REPORT_LOAD_VALUES,YTD_SALES_SHEET_REPORT_PAGE_NUMBER,YTD_SALES_SHEET_REPORT_ROWS_COUNT,YTD_SALES_SHEET_REPORT_ORDER_BY_CHANGE,YTD_SALES_SHEET_REPORT_LOADING } from "../../constants/actionTypes";


const initialState = {
    values:{},
    searched:false,
    rowData:[],
    resultCount:0,
    page:0,
    perPage: 0,
    sortBy: 'created_at',
    sortMode: 'desc',
    rowDataNew:[]
 }

 export default (state=initialState,{payload,type})=>{
    switch (type) {
        case YTD_SALES_SHEET_REPORT_LOAD_VALUES:
            return {
                ...state,
                values:{
                    ...state.values,
                    [payload.name]:payload.value
                }
            };
    case YTD_SALES_SHEET_REPORT_LOAD_RESULT:
            return {
                ...state,
                rowData:payload.results,
                searched:true,
                resultCount:payload.count,
                rowDataNew:payload.results_ytd,
                page:1,
                perPage:25
            };
    // case YTD_SALES_SHEET_REPORT_PAGE_NUMBER:
    //         return {
    //             ...state,
    //             page:action.payload.page
    //         }
    // case YTD_SALES_SHEET_REPORT_ROWS_COUNT:
    //     return {
    //         ...state,
    //         perPage:action.payload.perPage
    //     }
    // case YTD_SALES_SHEET_REPORT_ORDER_BY_CHANGE:
    //     return {
    //         ...state,
    //         sortBy: action.payload.column,
    //         sortMode: action.payload.mode
    //     }
        default:
            return state;
    }
 }