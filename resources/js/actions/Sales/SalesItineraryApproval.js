import { SALES_ITINERARY_APPROVAL_CHANGE_USER,SALES_ITINERARY_APPROVAL_CHANGE_TYPE,SALES_ITINERARY_APPROVAL_RESULT_LOADED,SALES_ITINERARY_APPROVAL_OPEN_ITINERARY,SALES_ITINERARY_APPROVAL_CLOSE_ITINERARY,SALES_ITINERARY_APPROVAL_CHANGE_DIVISION,SALES_ITINERARY_APPROVAL_CHANGE_AREA, SALES_ITINERARY_APPROVAL_CHANGE_MODE } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeUser = user=>({
     type:SALES_ITINERARY_APPROVAL_CHANGE_USER,
     payload:{user}
});

export const changeArea = area=>({
    type:SALES_ITINERARY_APPROVAL_CHANGE_AREA,
    payload:{area}
});
 
 export const changeType = type=>({
     type:SALES_ITINERARY_APPROVAL_CHANGE_TYPE,
     payload:{type}
 });
 
 export const loadedResults = (results,count)=>({
     type:SALES_ITINERARY_APPROVAL_RESULT_LOADED,
     payload:{results,count}
 });
 
 export const fetchResults = (user,type,area,mode)=>dispatch=>{
     agent.SalesItineraryApproval.search(user,type,area,mode).then(({results,count})=>{
         dispatch(loadedResults(results,count))
     }).catch(err=>{
         dispatch(alertDialog(err.response.data.message,'error'));
     })
 }
 
 export const approve = id =>dispatch=>{
     agent.SalesItineraryApproval.approve(id).then(({success,message})=>{
         if(success)
             dispatch(alertDialog(message,"success"))
     }).catch(err=>{
         dispatch(alertDialog(err.response.data.message,'error'));
     })
 }
 
 export const fetchItinerary = id =>dispatch=>{
     agent.ItineraryViewer.load(id).then(({message,success,dates})=>{
         if(success)
             dispatch(openItinerary(id,dates));
     })
 }
 
 export const openItinerary = (id,dates)=>({
     type:SALES_ITINERARY_APPROVAL_OPEN_ITINERARY,
     payload:{id,dates}
 });
 
 export const closeItinerary = ()=>({
     type:SALES_ITINERARY_APPROVAL_CLOSE_ITINERARY
 })

 export const changeMode = mode =>({
     type: SALES_ITINERARY_APPROVAL_CHANGE_MODE,
     payload: {mode}
 })