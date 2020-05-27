import { MR_PS_REPORT_LOAD_VALUES,MR_PS_REPORT_LOAD_RESULT } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeValue = (name,value)=>({
     type:MR_PS_REPORT_LOAD_VALUES,
     payload:{
         name,value
     }
 });

 export const loadedData = (results,count)=>({
     type:MR_PS_REPORT_LOAD_RESULT,
     payload:{results,count}
 });

 export const fetchData = (values)=>dispatch=>{
     agent.MrPsItineraryReport.search(values).then(({results,count})=>{
         dispatch(loadedData(results,count));
     }).catch(err=>{
         dispatch(alertDialog(err.response.data.message,'error'));
     })
 }