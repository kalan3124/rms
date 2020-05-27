import {
    SR_ALLOCATION_CHANGE_CHECKED_DSR,
    SR_ALLOCATION_CHANGE_CHECKED_SR,
    SR_ALLOCATION_DSR_LOADED,
    SR_ALLOCATION_SR_LOADED,
    SR_ALLOCATION_CHANGE_DCR_NAME,
    SR_ALLOCATION_CHANGE_SR_NAME,
    SR_ALLOCATION_CLEAR_PAGE,
    SR_ALLOCATION_APPEND_CHECKED_SR
} from "../../constants/actionTypes";
import agent from "../../agent";
import {
    SEARCHING_RECORDS
} from "../../constants/debounceTypes";
import {
    alertDialog
} from "../Dialogs";

export const changeCheckedDsr = dsrChecked => ({
    type: SR_ALLOCATION_CHANGE_CHECKED_DSR,
    payload: {
        dsrChecked
    }
});

export const changeCheckedSr = srChecked => ({
    type: SR_ALLOCATION_CHANGE_CHECKED_SR,
    payload: {
        srChecked
    }
});

export const loadedDsr = dsrResults => ({
    type: SR_ALLOCATION_DSR_LOADED,
    payload: {
        dsrResults
    }
});

export const loadedSr = srResults => ({
    type: SR_ALLOCATION_SR_LOADED,
    payload: {
        srResults
    }
});

export const changeDsrName = dsrName => ({
    type: SR_ALLOCATION_CHANGE_DCR_NAME,
    payload: {
        dsrName
    }
});

export const changeSrName = srName => ({
    type: SR_ALLOCATION_CHANGE_SR_NAME,
    payload: {
        srName
    }
});

export const appendCheckedDsr = (dsr)=>({
    type: SR_ALLOCATION_APPEND_CHECKED_SR,
    payload:{dsr}
})

export const clearPage = ()=>({
    type:SR_ALLOCATION_CLEAR_PAGE
})

export const fetchDsr = (dsr, delay = true) => {
    let thunk = dispatch => {
        agent.Crud.dropdown('user', dsr,{'u_tp_id':15}).then(results => {
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
    agent.SrAllocation.save(sr,dsr).then(({success,message})=>{
        if(success){
            dispatch(alertDialog(message,'success'));
            dispatch(clearPage())
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const load = (dsr)=>dispatch=>{
    agent.SrAllocation.load(dsr).then(({dsr})=>{
        dispatch(appendCheckedDsr(dsr))
    })
}
