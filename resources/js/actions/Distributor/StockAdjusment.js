import {
    STOCK_ADJUSMENT_CHANGE_TYPE,
    STOCK_ADJUSMENT_LOAD_NO,
    STOCK_ADJUSMENT_CHANGE_DIS,
    STOCK_ADJUSMENT_LOAD_DATA,
    STOCK_ADJUSMENT_CHANGE_DATA,
    STOCK_ADJUSMENT_AJUST_QTY,
    STOCK_ADJUSMENT_PAGE_CLEAR
} from "../../constants/actionTypes";
import {
    alertDialog
} from "../Dialogs";

import agent from '../../agent';

export const changeAdjType = (type) => ({
    type: STOCK_ADJUSMENT_CHANGE_TYPE,
    payload: { type }
});

export const changeDis = (dis_id) => ({
    type: STOCK_ADJUSMENT_CHANGE_DIS,
    payload: { dis_id }
});

export const loadAdjNumber = (number) => ({
    type: STOCK_ADJUSMENT_LOAD_NO,
    payload: { number }
});

export const loadData = (rowData) => ({
    type: STOCK_ADJUSMENT_LOAD_DATA,
    payload: { rowData }
});

export const changeAjuQty = (aju_new_qty) => ({
    type: STOCK_ADJUSMENT_AJUST_QTY,
    payload: { aju_new_qty }
});

export const changeData = (lastId, product, pro_name, ava_qty, bt_id, aju_qty, batch, reason, total, pro_name_rowspan) => ({
    type: STOCK_ADJUSMENT_CHANGE_DATA,
    payload: { lastId, product, pro_name, ava_qty, bt_id, aju_qty, batch, reason, total, pro_name_rowspan }
});

export const load = (number, dis_id) => dispatch => {
    agent.StockAdjusment.loadAdjNo(number, dis_id).then(({ number }) => {
        dispatch(loadAdjNumber(number))
    })
}

export const fetchData = (data) => dispatch => {
    agent.StockAdjusment.loadData(data).then(({ results }) => {
        dispatch(loadData(results))
    })
}

export const submitData = (data, type, dis_id, adjNumber) => dispatch => {
    agent.StockAdjusment.saveData(data, type, dis_id, adjNumber).then(({ results, message }) => {
        if (results) {
            dispatch(alertDialog(message, 'success'));
            // dispatch(pageClear());
            setTimeout(dispatch(pageClear()), 1000);
        }
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}

export const alert = (msg) => dispatch => {
    dispatch(alertDialog(msg, 'error'));
}

export const pageClear = () => ({
    type: STOCK_ADJUSMENT_PAGE_CLEAR,
});