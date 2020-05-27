import { TEAM_PERFORMANCE_REPORT_LOAD_VALUES,TEAM_PERFORMANCE_REPORT_LOAD_RESULT,TEAM_PERFORMANCE_REPORT_PAGE,TEAM_PERFORMANCE_REPORT_PERPAGE } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeValue = (name,value)=>({
     type:TEAM_PERFORMANCE_REPORT_LOAD_VALUES,
     payload:{
         name,value
     }
})
 
export const loadedData = (results,count,hod)=>({
     type:TEAM_PERFORMANCE_REPORT_LOAD_RESULT,
     payload:{results,count,hod}
});

export const changePage = (page)=>({
    type:TEAM_PERFORMANCE_REPORT_PAGE,
    payload:{page}
});

export const changePerPage = (perPage)=>({
    type:TEAM_PERFORMANCE_REPORT_PERPAGE,
    payload:{perPage}
});

export const fetchData = (values,page,perPage)=>dispatch=>{
     agent.TeamPerformanceReport.search(values,page,perPage).then(({results,count,hod})=>{
         dispatch(loadedData(results,count,hod));
     }).catch(err=>{
         dispatch(alertDialog(err.response.data.message,'error'));
     })
}