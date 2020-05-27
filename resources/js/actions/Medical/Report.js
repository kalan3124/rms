import {
    REPORT_CLEAR_PAGE,
    REPORT_CHANGE_REPORT,
    REPORT_CHANGE_SEARCH_TERMS,
    REPORT_RESULTS_LOADED,
    REPORT_CHANGE_COLUMNS
} from '../../constants/actionTypes';
import agent from '../../agent';
import { alertDialog } from '../Dialogs';


export const clearPage = ()=>({
    type:REPORT_CLEAR_PAGE
})

export const reportLoaded = (report,columns,title,inputs,inputsStructure,additionalHeaders,updateColumnsOnSearch)=>({
    type:REPORT_CHANGE_REPORT,
    payload:{columns,title,inputs,inputsStructure,report,additionalHeaders,updateColumnsOnSearch}
})

export const changeReport = report=>dispatch=>{
    dispatch(clearPage());

    agent.Report.info(report).then(({columns,title,inputs,inputsStructure,additionalHeaders,updateColumnsOnSearch})=>{
        dispatch(reportLoaded(report,columns,title,inputs,inputsStructure,additionalHeaders,updateColumnsOnSearch))
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const resultsLoaded = (results,count)=>({
    type:REPORT_RESULTS_LOADED,
    payload:{results,count}
})

export const changeColumns = (columns,additionalHeaders)=>({
    type:REPORT_CHANGE_COLUMNS,
    payload:{columns,additionalHeaders}
})

export const changeSearchTerms = (report,searchTerms)=>dispatch =>{
    dispatch({
        type:REPORT_CHANGE_SEARCH_TERMS,
        payload:{searchTerms}
    });

    agent.Report.search(report,searchTerms).then(({results,count,columns,additionalHeaders})=>{
        dispatch(resultsLoaded(results,count));
        if(columns)
            dispatch(changeColumns(columns,additionalHeaders))
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const dialog = (message,type)=>dispatch=>dispatch(alertDialog(message,type))