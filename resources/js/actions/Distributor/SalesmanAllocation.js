import {
    SALESMAN_ALLOCATION_CHANGE_CHECKED_DSR,
    SALESMAN_ALLOCATION_CHANGE_CHECKED_SR,
    SALESMAN_ALLOCATION_DSR_LOADED,
    SALESMAN_ALLOCATION_SR_LOADED,
    SALESMAN_ALLOCATION_CHANGE_DCR_NAME,
    SALESMAN_ALLOCATION_CHANGE_SR_NAME,
    SALESMAN_ALLOCATION_CLEAR_PAGE,
    SALESMAN_ALLOCATION_APPEND_CHECKED_SR
} from "../../constants/actionTypes";
import agent from "../../agent";
import {
    SEARCHING_RECORDS
} from "../../constants/debounceTypes";
import {
    alertDialog
} from "../Dialogs";

export const changeCheckedDsr = dsrChecked => ({
    type: SALESMAN_ALLOCATION_CHANGE_CHECKED_DSR,
    payload: {
        dsrChecked
    }
});

export const changeCheckedSr = srChecked => ({
    type: SALESMAN_ALLOCATION_CHANGE_CHECKED_SR,
    payload: {
        srChecked
    }
});

export const loadedDsr = dsrResults => ({
    type: SALESMAN_ALLOCATION_DSR_LOADED,
    payload: {
        dsrResults
    }
});

export const loadedSr = srResults => ({
    type: SALESMAN_ALLOCATION_SR_LOADED,
    payload: {
        srResults
    }
});

export const changeDsrName = dsrName => ({
    type: SALESMAN_ALLOCATION_CHANGE_DCR_NAME,
    payload: {
        dsrName
    }
});

export const changeSrName = srName => ({
    type: SALESMAN_ALLOCATION_CHANGE_SR_NAME,
    payload: {
        srName
    }
});

export const appendCheckedDsr = (dsr)=>({
    type: SALESMAN_ALLOCATION_APPEND_CHECKED_SR,
    payload:{dsr}
})

export const clearPage = ()=>({
    type:SALESMAN_ALLOCATION_CLEAR_PAGE
})

export const fetchDsr = (dsr, delay = true) => {
    let thunk = dispatch => {
        agent.Crud.dropdown('user', dsr,{'u_tp_id':10}).then(results => {
            dispatch(loadedDsr(results))
        });
    }

    if (delay) thunk.meta = {
        debounce: {
            time: 300,
            key: SEARCHING_RECORDS
        }
    }

    return thunk;
}

export const fetchSr = (sr, delay = true) => {
    let thunk = dispatch => {
        agent.Crud.dropdown('user', sr,{'u_tp_id':14}).then(results => {
            dispatch(loadedSr(results))
        })
    }

    if (delay) thunk.meta = {
        debounce: {
            time: 300,
            key: SEARCHING_RECORDS
        }
    }

    return thunk;
}

export const save = (sr,dsr)=>dispatch=>{
    agent.SalesmanAllocation.save(sr,dsr).then(({success,message})=>{
        if(success){
            dispatch(alertDialog(message,'success'));
            dispatch(clearPage())
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const load = (dsr)=>dispatch=>{
    agent.SalesmanAllocation.load(dsr).then(({dsr})=>{
        dispatch(appendCheckedDsr(dsr))
    })
}
