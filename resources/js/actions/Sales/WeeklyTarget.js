import {
    SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_REP,
    SALES_WEEKLY_TARGET_ALLOCATION_LOAD_QTY_VALUE,
    SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_MONTH,
    SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_PREASANTAGE,
    SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_TARGET,
    SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_PLUS,
    SALES_WEEKLY_TARGET_ALLOCATION_PREASANTAGE_CAL,
    SALES_WEEKLY_TARGET_ALLOCATION_CHECK_MONTHLY_TARGET_EXCEEDED,
    SALES_WEEKLY_TARGET_ALLOCATION_CHECK_ERROR,
    SALES_WEEKLY_TARGET_ALLOCATION_CHECK_TYPE,
    SALES_WEEKLY_TARGET_ALLOCATION_DROP_WEEK,
    SALES_WEEKLY_TARGET_ALLOCATION_PAGE_CLEAR
} from "../../constants/actionTypes";
import agent from "../../agent";
import {
    alertDialog
} from "../Dialogs";

export const changeRep = rep => ({
    type: SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_REP,
    payload: {
        rep
    }
});

export const changeMonth = month => ({
    type: SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_MONTH,
    payload: {
        month
    }
});

export const changePresantage = presantage => ({
    type: SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_PREASANTAGE,
    payload: {
        presantage
    }
});

export const changeCalculation = calculation => ({
    type: SALES_WEEKLY_TARGET_ALLOCATION_PREASANTAGE_CAL,
    payload: {
        calculation
    }
});

export const changeCheckTarget = target => ({
    type: SALES_WEEKLY_TARGET_ALLOCATION_CHECK_MONTHLY_TARGET_EXCEEDED,
    payload: {
        target
    }
});

export const changeCheckError = error => ({
    type: SALES_WEEKLY_TARGET_ALLOCATION_CHECK_ERROR,
    payload: {
        error
    }
});

export const changeCheckType = (type,month_end) => ({
    type: SALES_WEEKLY_TARGET_ALLOCATION_CHECK_TYPE,
    payload: {
        type,
        month_end
    }
});

export const changeDrop = lastId => ({
    type: SALES_WEEKLY_TARGET_ALLOCATION_DROP_WEEK,
    payload: {
        lastId
    }
});

export const changePlusTargets = (start_week, end_week, week_presantage, value) => ({
    type: SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_PLUS,
    payload: {
        start_week,
        end_week,
        week_presantage,
        value
    }
});

export const changeTargets = (lastId, start_week, end_week, week_presantage, value) => ({
    type: SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_TARGET,
    payload: {
        lastId,
        start_week,
        end_week,
        week_presantage,
        value
    }
});

export const dataQtyValueLoaded = (totValue, totQty, totCurrent, ifCheckWeekly) => ({
    type: SALES_WEEKLY_TARGET_ALLOCATION_LOAD_QTY_VALUE,
    payload: {
        totValue,
        totQty,
        totCurrent,
        ifCheckWeekly
    }
});

export const pageClear = () => ({
    type: SALES_WEEKLY_TARGET_ALLOCATION_PAGE_CLEAR,
});

export const fetchData = (rep, month) => dispatch => {
    agent.SalesWeeklyTarget.search(rep, month).then(({
        totValue,
        totQty,
        totCurrent,
        ifCheckWeekly,
        type,
        month_end
    }) => {
        if (totValue == undefined || totQty == undefined || totValue == 0)
            dispatch(alertDialog("Sr don't have a target for month", 'error'));
        dispatch(dataQtyValueLoaded(totValue, totQty, totCurrent, ifCheckWeekly));
        dispatch(changeCheckType(type,month_end));
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}

export const submitData = (targetsData, rep, month,type) => dispatch => {
    agent.SalesWeeklyTarget.save(targetsData, rep, month,type).then(({
        message
    }) => {
        dispatch(alertDialog(message, 'success'));
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}


// error msg

export const errorMsg = (msg, type) => dispatch => {
    dispatch(alertDialog(msg, type));
}
