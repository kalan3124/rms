import {
    PURCHASE_ORDER_CONFIRM_NUMBER_CHANGE,
    PURCHASE_ORDER_CONFIRM_PRODUCT_CHANGE,
    PURCHASE_ORDER_CONFIRM_QTY_CHANGE,
    PURCHASE_ORDER_CONFIRM_REMOVE_LINE,
    PURCHASE_ORDER_CONFIRM_ADD_LINE,
    PURCHASE_ORDER_CONFIRM_DETAILS_CHANGE,
    PURCHASE_ORDER_CONFIRM_CLEAR_PAGE,
    PURCHASE_ORDER_CONFIRM_LOAD_DATA,
    PURCHASE_ORDER_CONFIRM_LOADING,
} from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeNumber = number => ({
    type: PURCHASE_ORDER_CONFIRM_NUMBER_CHANGE,
    payload: { number }
});

export const changeProduct = (number, product) => ({
    type: PURCHASE_ORDER_CONFIRM_PRODUCT_CHANGE,
    payload: { number, product }
});

export const changeQty = (number, qty) => ({
    type: PURCHASE_ORDER_CONFIRM_QTY_CHANGE,
    payload: { number, qty }
});

export const removeLine = number => ({
    type: PURCHASE_ORDER_CONFIRM_REMOVE_LINE,
    payload: { number }
});

export const changeProductDetails = (number, price, packSize, code, stockInHand, stockPending) => ({
    type: PURCHASE_ORDER_CONFIRM_DETAILS_CHANGE,
    payload: { number, price, packSize, code, stockInHand, stockPending }
});

export const changeDetails = (lines) => ({
    type: PURCHASE_ORDER_CONFIRM_LOAD_DATA,
    payload: { lines }
});

export const fetchProductDetails = (id, product, number) => dispatch => {
    agent.PurchaseOrderConfirm.getDetails(product, number).then(({ price, pack_size, code, stockInHand, stockPending }) => {
        dispatch(changeProductDetails(id, price, pack_size, code, stockInHand, stockPending));
    });
}

export const fetchDetails = (number) => dispatch => {
    agent.PurchaseOrderConfirm.load(number).then(({ lines }) => {
        dispatch(changeDetails(lines));
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
        dispatch(clearPage());
    })
}

export const addLine = () => ({
    type: PURCHASE_ORDER_CONFIRM_ADD_LINE
})

export const clearPage = () => ({
    type: PURCHASE_ORDER_CONFIRM_CLEAR_PAGE
})

export const loading = (loading) => ({
    type: PURCHASE_ORDER_CONFIRM_LOADING,
    payload: {
        loading
    }
})

export const save = (number, lines) => dispatch => {
    dispatch(loading(true));
    agent.PurchaseOrderConfirm.save(number, lines).then(({ success, message }) => {
        if (success) {
            dispatch(alertDialog(message, "success"));
            dispatch(clearPage());
        } else {
            dispatch(alertDialog(message, "error"));
        }
        dispatch(loading(false));
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
        dispatch(loading(false));
    })
};