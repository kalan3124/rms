import { 
    ITINERARY_MR_CHANGE,
    ITINERARY_FM_CHANGE,
    ITINERARY_YEAR_MONTH_CHANGE,
    ITINERARY_LOADING,
    ITINERARY_LOADED,
    ITINERARY_NOT_SET,
    ITINERARY_DATE_CHANGE,
    ITINERARY_DAY_TYPES_LOADED,
    ITINERARY_DATE_SELECTED,
    ITINERARY_DAYS_LOADED,
    ITINERARY_DATE_CANCEL,
    ITINERARY_CHANGE_ADDITIONAL_VALUES,
    ITINERARY_CONFIRM_ADDITIONAL_VALUES,
    ITINERARY_CLEAR_DATE,
    ITINERARY_LOADED_FM_TEAM_MEMBERS,
    ITINERARY_CHANGE_JOIN_FIELD_WORKER,
    ITINERARY_CHANGE_OTHER_DAY,
    ITINERARY_CLEAR
} from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeMR = mr=>({
    type:ITINERARY_MR_CHANGE,
    payload:{mr}
})

export const changeFM = fm=>({
    type:ITINERARY_FM_CHANGE,
    payload:{fm}
})

export const changeYearMonth  = yearMonth =>({
    type:ITINERARY_YEAR_MONTH_CHANGE,
    payload:{yearMonth}
})

export const loading = ()=>({
    type:ITINERARY_LOADING
})

export const loaded = (dates,approved)=>({
    type:ITINERARY_LOADED,
    payload:{dates,approved}
})

export const notFound = ()=>({
    type:ITINERARY_NOT_SET
})

export const fetchYearMonth =(yearMonth,mr,fm)=>(dispatch=>{
    dispatch(changeYearMonth(yearMonth));
    if(typeof mr =='undefined'&&typeof fm =='undefined') return;
    dispatch(loading())
    agent.Itinerary.load(yearMonth,mr,fm).then(({dates,approved})=>{
        dispatch(loaded(dates.mapToObject('date'),approved))
    }).catch((err)=>{
        if(err.response.data.message!="")
            dispatch(alertDialog(err.response.data.message,'error'))
        dispatch(notFound())
    })
})

export const changeDate = values=>({
    type:ITINERARY_DATE_CHANGE,
    payload:{values}
})

export const selectDate = (dateDetails,mode)=>({
    type:ITINERARY_DATE_SELECTED,
    payload:{dateDetails,mode}
})

export const cancelDate = ()=>({
    type:ITINERARY_DATE_CANCEL
})

export const daysLoaded =days=>({
    type:ITINERARY_DAYS_LOADED,
    payload:{days}
})

export const fetchDays = (rep,type)=>dispatch=>{
    agent.StandardItinerary.load(rep).then(days=>{
        dispatch(daysLoaded(days));
    }).catch(err=>{
        if(type==1){
            // Standard itinerary is not required for FM. Only MR.
            dispatch(alertDialog(err.response.data.message,'error'))
        }
    })
}

export const fetchDayTypes =()=> (dispatch=>{
    dispatch(loading());
    agent.Itinerary.dayTypes().then(data=>{
        dispatch({
            type:ITINERARY_DAY_TYPES_LOADED,
            payload:{dayTypes:data}
        })
    })
})

export const save = (dates,yearMonth,mr,fm) =>dispatch=>{
    dispatch(loading());
    agent.Itinerary.save(dates,yearMonth,mr,fm).then(data=>{
        dispatch(alertDialog(data.message,'success'));
        dispatch(fetchYearMonth(yearMonth,mr,fm));
    }).catch(err=>{
        dispatch(fetchYearMonth(yearMonth,mr,fm));
        dispatch(alertDialog(err.response.data.message,'error'))
    })
}

export const changeAdditionalValues = additionalValues=>({
    type:ITINERARY_CHANGE_ADDITIONAL_VALUES,
    payload:{additionalValues}
})

export const confirmAdditionalValues = (date,additionalValues)=>({
    type:ITINERARY_CONFIRM_ADDITIONAL_VALUES,
    payload:{date,additionalValues}
})

export const clearDate = date=>({
    type:ITINERARY_CLEAR_DATE,
    payload:{date}
})

export const changeJoinFieldWorker = (date,value)=>({
    type:ITINERARY_CHANGE_JOIN_FIELD_WORKER,
    payload:{value,date}
})

export const changeOtherDay = (date,value)=>({
    type:ITINERARY_CHANGE_OTHER_DAY,
    payload:{date,value}
});

export const clearItinerary = ()=>({
    type:ITINERARY_CLEAR
})