import { DOC_APRV_CHANGE_FROM_DATE, DOC_APRV_CHANGE_TO_DATE, DOC_APRV_CHANGE_USER, DOC_APRV_DATA_LOADED, DOC_APRV_SELECT_TO_EDIT, DOC_APRV_CHANGE_DOCTOR } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeUser = user=>({
    type:DOC_APRV_CHANGE_USER,
    payload:{user}
});

export const changeFromDate = date=>({
    type:DOC_APRV_CHANGE_FROM_DATE,
    payload:{date}
});

export const changeToDate = date=>({
    type:DOC_APRV_CHANGE_TO_DATE,
    payload:{date}
});

export const loadedDate = data =>({
    type:DOC_APRV_DATA_LOADED,
    payload:{data}
});

export const loadData = (user,toDate,fromDate)=>dispatch=>{
    agent.DoctorApprove.search(user,toDate,fromDate).then(({results,success})=>{
        if(success)
            dispatch(loadedDate(results.mapToObject('id')));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const selectToEdit = key=>({
    type:DOC_APRV_SELECT_TO_EDIT,
    payload:{key}
})

export const editDoctor = (key,values)=>({
    type:DOC_APRV_CHANGE_DOCTOR,
    payload:{key,values}
})

export const save = (key,values,callback)=>dispatch=>{
    agent.DoctorApprove.submit(key,values).then(({success,message})=>{
        if(success){
            dispatch(alertDialog(message,'success'));
            callback();
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const cancel = (key,callback)=>dispatch=>{
    agent.DoctorApprove.delete(key).then(({message,success})=>{
        if(success){
            dispatch(alertDialog(message,'success'));
            callback();
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}