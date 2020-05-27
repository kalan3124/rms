import {
     USER_CHANGE_PASS_LOAD_DATA,
     USER_CHANGE_PASS_CHANGE_PASSWORD,
     USER_CHANGE_PASS_CHANGE_ATTEMPTS,
     USER_CHANGE_PASS_CHANGE_LOCK_TIME,
     USER_CHANGE_PASS_LOAD_OTHER
 } from "../../constants/actionTypes";
 import agent from "../../agent";
 import { alertDialog } from "../Dialogs";

 export const changePassword = (password)=>({
     type:USER_CHANGE_PASS_CHANGE_PASSWORD,
     payload:{password}
 });

 export const changeLockTime = (lock_time)=>({
    type:USER_CHANGE_PASS_CHANGE_LOCK_TIME,
    payload:{lock_time}
});

export const changeAttempts = (attempts)=>({
    type:USER_CHANGE_PASS_CHANGE_ATTEMPTS,
    payload:{attempts}
});

 export const loadData = (name,code,roll)=>({
     type:USER_CHANGE_PASS_LOAD_DATA,
     payload:{name,code,roll}
 })

 export const loadOtherData = (lock_time,attempts)=>({
    type:USER_CHANGE_PASS_LOAD_OTHER,
    payload:{lock_time,attempts}
})

 export const fetchData = ()=>dispatch=>{
     agent.Auth.check().then(({name,code,roll})=>{
         dispatch(loadData(name?name:"-",code?code:"-",roll?roll:"-"));
     }).catch(err=>{
         dispatch(alertDialog(err.response.data.message,'error'));
     })
 }


 export const fetchOtherData = ()=>dispatch=>{
    agent.Auth.loadOther().then(({lock_time,attempts})=>{
        dispatch(loadOtherData(lock_time,attempts));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

 export const updateData = (password,lock_time,attempts)=>dispatch=>{
    agent.Auth.change(password,lock_time,attempts).then(({message})=>{
        dispatch(alertDialog(message,'success'));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const updateOtherData = (lock_time,attempts)=>dispatch=>{
    agent.Auth.changeOther(lock_time,attempts).then(({message})=>{
        dispatch(alertDialog(message,'success'));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}
