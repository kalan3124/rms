import {
    DIRECT_INVOICE_CHANGE_DISTRIBUTOR,
    DIRECT_INVOICE_CHANGE_SALESMAN,
    DIRECT_INVOICE_CHANGE_CUSTOMER,
    DIRECT_INVOICE_CHANGE_CHANGE_INVOICE_NUMBER,
    DIRECT_INVOICE_ADD_LINE,
    DIRECT_INVOICE_REMOVE_LINE,
    DIRECT_INVOICE_CHANGE_DISCOUNT,
    DIRECT_INVOICE_CHANGE_PRODUCT,
    DIRECT_INVOICE_LOAD_LINE_INFO,
    DIRECT_INVOICE_CHANGE_QTY,
    DIRECT_INVOICE_CLEAR_PAGE,
    DIRECT_INVOICE_LOAD_BONUS_DATA,
    DIRECT_INVOICE_CHANGE_BONUS_QTY,
    DIRECT_INVOICE_LOAD_BATCH_DETAILS,
    DIRECT_INVOICE_OPEN_BATCH_EDIT_FORM,
    DIRECT_INVOICE_CHANGE_BATCH_QTY,
    DIRECT_INVOICE_CANCEL_BATCH_EDIT_FORM,
} from '../../constants/actionTypes';
import agent from '../../agent';
import { alertDialog } from '../Dialogs';
import { BONUS_FETCH } from '../../constants/debounceTypes';

export const changeDistributor = distributor => ({
    type: DIRECT_INVOICE_CHANGE_DISTRIBUTOR,
    payload: {
        distributor
    }
});

export const changeSalesman = salesman => ({
    type: DIRECT_INVOICE_CHANGE_SALESMAN,
    payload: {
        salesman
    }
});

export const changeCustomer = customer => ({
    type: DIRECT_INVOICE_CHANGE_CUSTOMER,
    payload: {
        customer
    }
});

export const changeInvoiceNumber = number => ({
    type: DIRECT_INVOICE_CHANGE_CHANGE_INVOICE_NUMBER,
    payload: {
        number
    }
});

export const addLine = () => ({
    type: DIRECT_INVOICE_ADD_LINE,
});

export const removeLine = (id) => ({
    type: DIRECT_INVOICE_REMOVE_LINE,
    payload: {
        id
    }
});

export const changeDiscount = (id, discount) => ({
    type: DIRECT_INVOICE_CHANGE_DISCOUNT,
    payload: {
        id,
        discount
    }
});

export const changeProduct = (id, product) => ({
    type: DIRECT_INVOICE_CHANGE_PRODUCT,
    payload: {
        id,
        product
    }
});

export const loadedLineInfo = (id, stock, price, availableBatches) => ({
    type: DIRECT_INVOICE_LOAD_LINE_INFO,
    payload: {
        id,
        stock,
        price,
        availableBatches
    }
});

export const changeQty = (id, qty) => ({
    type: DIRECT_INVOICE_CHANGE_QTY,
    payload: {
        id,
        qty
    }
});

export const clearPage = () => ({
    type: DIRECT_INVOICE_CLEAR_PAGE
})

export const fetchLineInfo = (id, distributor, product, customer) => dispatch => {
    agent.DirectInvoice.loadLineInfo(distributor, product, customer).then(({ success, price, stock, availableBatches }) => {
        dispatch(loadedLineInfo(id, stock, price, availableBatches));
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}

export const save = (distributor, salesman, customer, lines, bonusLines, requestApproval) => dispatch => {
    agent.DirectInvoice.save(distributor, salesman, customer, lines, bonusLines, requestApproval).then(({ success, message }) => {
        dispatch(alertDialog(message, 'success'));
        dispatch(clearPage());
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}

export const fetchNextInvoiceNumber = (distributor, salesman) => dispatch => {
    agent.DirectInvoice.loadNumber(distributor, salesman).then(({ success, number }) => {
        dispatch(changeInvoiceNumber(number));
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}

export const loadedBonus = (bonusLines) => ({
    type: DIRECT_INVOICE_LOAD_BONUS_DATA,
    payload: { bonusLines }
});

export const fetchBonus = (disId, lines) => {
    const thunk = dispatch => {
        agent.DirectInvoice.loadBonus(disId, lines).then(({ lines }) => {
            dispatch(loadedBonus(lines));
        });
    }

    thunk.meta = {
        debounce: {
            time: 300,
            key: BONUS_FETCH
        }
    }

    return thunk;
}

export const changeBonusQty = (id, productId, qty) => ({
    type: DIRECT_INVOICE_CHANGE_BONUS_QTY,
    payload: { id, productId, qty }
})

export const loadBatchDetails = (id, batchDetails) => ({
    type: DIRECT_INVOICE_LOAD_BATCH_DETAILS,
    payload: {
        batchDetails,
        id
    }
});

export const fetchBatchDetails = (id, distributor, product, qty) => dispatch => {
    agent.DirectInvoice.loadBatchDetails(distributor, product, qty).then(({ batchDetails, success }) => {
        if (success) {
            dispatch(loadBatchDetails(id, batchDetails))
        }
    });
};

export const openBatchEditForm = (line) => ({
    type: DIRECT_INVOICE_OPEN_BATCH_EDIT_FORM,
    payload: {
        line
    }
});

export const changeBatchQty = (batch, qty) => ({
    type: DIRECT_INVOICE_CHANGE_BATCH_QTY,
    payload: {
        batch,
        qty
    }
});

export const cancelBatchEditForm = () => ({
    type: DIRECT_INVOICE_CANCEL_BATCH_EDIT_FORM
})