import {
    SALES_EXPENSES_EDIT_CHANGE_REP,
    SALES_EXPENSES_EDIT_CHANGE_MONTH,
    SALES_EXPENSES_EDIT_CHANGE_DATA,
    SALES_EXPENSES_EDIT_LOAD_DATA,
    SALES_EXPENSES_EDIT_PLUS_DATA,
    SALES_EXPENSES_OPEN_MODAL,
    SALES_EXPENSES_CHANGE_VALUE,
    SALES_EXPENSES_ASM_ROLL
} from "../../constants/actionTypes";
import agent from "../../agent";
import {
    alertDialog
} from "../Dialogs";

export const openModal = open => ({
    type: SALES_EXPENSES_OPEN_MODAL,
    payload: {
        open
    }
});

export const getroll = roll => ({
    type: SALES_EXPENSES_ASM_ROLL,
    payload: {
        roll
    }
});

export const changeValue = (name,value)=>({
    type:SALES_EXPENSES_CHANGE_VALUE,
    payload:{
        name,value
    }
});

export const changeRep = rep => ({
    type: SALES_EXPENSES_EDIT_CHANGE_REP,
    payload: {
        rep
    }
});

export const changeMonth = month => ({
    type: SALES_EXPENSES_EDIT_CHANGE_MONTH,
    payload: {
        month
    }
});

export const searchedData = results => ({
    type: SALES_EXPENSES_EDIT_LOAD_DATA,
    payload: {
        results
    }
});

export const changedPlusData = (date, bataType, stationery, parking, user, remark, app, mileage) => ({
    type: SALES_EXPENSES_EDIT_PLUS_DATA,
    payload: {
        date,
        bataType,
        stationery,
        parking,
        user,
        remark,
        app,
        mileage
    }
});

export const changedData = (lastId, date, bataType, stationery, parking, user, remark, app, mileage, exp_id, status,actual_mileage,mileage_amount,vht_rate,def_actual_mileage) => ({
    type: SALES_EXPENSES_EDIT_CHANGE_DATA,
    payload: {
        lastId,
        date,
        bataType,
        stationery,
        parking,
        user,
        remark,
        app,
        mileage,
        exp_id,
        status,
        actual_mileage,
        mileage_amount,
        vht_rate,
        def_actual_mileage
    }
});

export const fetchData = (rep, month) => dispatch => {
    agent.ExpensesEdit.search(rep, month).then(({
        results
    }) => {
        if (results.length > 0)
            dispatch(searchedData(results));
        else
            dispatch(alertDialog('Could not find any data', 'error'));
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}

export const submitData = (expenses) => dispatch => {
    agent.ExpensesEdit.save(expenses).then(({
        message
    }) => {
        dispatch(alertDialog(message, 'success'));
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}

export const saveAsmExp = (values) => dispatch => {
    agent.ExpensesEdit.asm(values).then(({
        message
    }) => {
         dispatch(alertDialog(message, 'success'));
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}

export const fetchRoll = () => dispatch => {
    agent.ExpensesEdit.roll().then(({
        roll
    }) => {
        dispatch(getroll(roll))
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}
