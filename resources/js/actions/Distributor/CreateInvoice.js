import {
    INVOICE_CREATION_CHANGE_SO_NUMBER,
    INVOICE_CREATION_LOAD_SO_DETAILS,
    INVOICE_CREATION_CHANGE_QTY,
    INVOICE_CREATION_CHANGE_DISCOUNT,
    INVOICE_CREATION_CLEAR_PAGE,
    INVOICE_CREATION_LOAD_BONUS,
    INVOICE_CREATION_CHANGE_BONUS_QTY,
    INVOICE_CREATION_LOAD_BATCH_DETAILS,
    INVOICE_CREATION_CHANGE_BATCH_QTY,
    INVOICE_CREATION_OPEN_BATCH_EDIT_FORM,
    INVOICE_CREATION_CANCEL_BATCH_EDIT_FORM
} from "../../constants/actionTypes";
import agent from '../../agent';
import { alertDialog } from "../Dialogs";

export const changeSONumber = (number) => ({
    type: INVOICE_CREATION_CHANGE_SO_NUMBER,
    payload: { number }
});

export const loadDetails = (details, discount, bonusDetails, remark) => ({
    type: INVOICE_CREATION_LOAD_SO_DETAILS,
    payload: { details, discount, bonusDetails, remark }
});

export const changeQty = (id, qty) => ({
    type: INVOICE_CREATION_CHANGE_QTY,
    payload: { id, qty }
});

export const fetchSalesOrderDetails = (soNumber) => dispatch => {
    agent.InvoiceCreation.load(soNumber).then(({ details, discount, bonusDetails, remark }) => {
        dispatch(loadDetails(details, discount, bonusDetails, remark));
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}

export const changeDiscount = (id, discountPercent) => ({
    type: INVOICE_CREATION_CHANGE_DISCOUNT,
    payload: { id, discountPercent }
})

export const save = (soNumber, details, discount, bonusDetails) => dispatch => {
    agent.InvoiceCreation.save(soNumber, details, discount, bonusDetails).then((response) => {
        dispatch(alertDialog(response.message, 'success'));
        dispatch(clearItineraryPage())
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}

export const requestApproval = (soNumber, details, discount, bonusDetails) => dispatch => {
    agent.InvoiceCreation.save(soNumber, details, discount, bonusDetails, true).then((response) => {
        dispatch(alertDialog(response.message, 'success'));
        dispatch(clearItineraryPage())
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    });
}

export const clearItineraryPage = () => ({
    type: INVOICE_CREATION_CLEAR_PAGE
})

export const loadBonus = bonusDetails => ({
    type: INVOICE_CREATION_LOAD_BONUS,
    payload: { bonusDetails }
});

export const changeBonus = (id, productId, qty) => ({
    type: INVOICE_CREATION_CHANGE_BONUS_QTY,
    payload: { id, productId, qty }
});

export const fetchBonus = (soNumber, details) => dispatch => {
    agent.InvoiceCreation.loadBonus(soNumber, details).then(({ bonusDetails }) => {
        dispatch(loadBonus(bonusDetails));
    });
}

export const loadBatchDetails = (id, batchDetails) => ({
    type: INVOICE_CREATION_LOAD_BATCH_DETAILS,
    payload: { id, batchDetails }
});

export const fetchBatchDetails = (id, soNumber, product, qty) => dispatch => {
    agent.InvoiceCreation.loadBatchDetails(soNumber, product, qty).then(({ batchDetails, success }) => {
        if (success) {
            dispatch(loadBatchDetails(id, batchDetails));
        }
    })
}

export const openBatchEditForm = (line) => ({
    type: INVOICE_CREATION_OPEN_BATCH_EDIT_FORM,
    payload: {
        line
    }
});

export const changeBatchQty = (batch, qty) => ({
    type: INVOICE_CREATION_CHANGE_BATCH_QTY,
    payload: {
        batch,
        qty
    }
});

export const cancelBatchEditForm = () => ({
    type: INVOICE_CREATION_CANCEL_BATCH_EDIT_FORM
});