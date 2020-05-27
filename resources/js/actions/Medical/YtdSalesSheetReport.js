import { YTD_SALES_SHEET_REPORT_LOAD_RESULT,YTD_SALES_SHEET_REPORT_LOAD_VALUES,YTD_SALES_SHEET_REPORT_PAGE_NUMBER,YTD_SALES_SHEET_REPORT_ROWS_COUNT,YTD_SALES_SHEET_REPORT_ORDER_BY_CHANGE,YTD_SALES_SHEET_REPORT_LOADING } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const loading = () => ({
    type: YTD_SALES_SHEET_REPORT_LOADING
});
export const changeValue = (name,value)=>({
     type:YTD_SALES_SHEET_REPORT_LOAD_VALUES,
     payload:{
         name,value
     }
 })

 export const loadedData = (results,count,results_ytd)=>({
     type:YTD_SALES_SHEET_REPORT_LOAD_RESULT,
     payload:{results,count,results_ytd}
 });
 
 export const fetchData = (values)=>dispatch=>{
    agent.YtdSalesSheetReport.search(values).then(({results,count,results_ytd})=>{
        dispatch(loadedData(results,count,results_ytd));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
 }
//  export const changePageNumber = (values,sortBy,sortMode,page,perPage)=>(
//     dispatch=>{
//         dispatch({
//             type:YTD_SALES_SHEET_REPORT_PAGE_NUMBER,
//             payload:{page}
//         });
//         dispatch(fetchData(form,values,sortBy,sortMode,page,perPage));
//     }
// )

// export const changeRowCount= (values,sortBy,sortMode,page,perPage)=>(
//     dispatch=>{
//         dispatch({
//             type:YTD_SALES_SHEET_REPORT_ROWS_COUNT,
//             payload:{perPage}
//         });
//         dispatch(fetchData(values,sortBy,sortMode,page,perPage));
//     }
// )

// export const changeSort = (values,sortBy,sortMode,...args) => (
//     dispatch => {
//         dispatch({
//             type: YTD_SALES_SHEET_REPORT_ORDER_BY_CHANGE,
//             payload: {
//                 column:sortBy,
//                 mode:sortMode
//             }
//         });
//         dispatch(fetchData(values,sortBy,sortMode,...args));
//     }
// )

// export const fetchInformation = (form) => (
//     dispatch => {
//         dispatch(loading());
//     }
// )
 