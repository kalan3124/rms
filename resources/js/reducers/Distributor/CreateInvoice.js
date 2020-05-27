import {
    INVOICE_CREATION_CHANGE_SO_NUMBER,
    INVOICE_CREATION_LOAD_SO_DETAILS,
    INVOICE_CREATION_CHANGE_QTY,
    INVOICE_CREATION_CHANGE_DISCOUNT,
    INVOICE_CREATION_CLEAR_PAGE,
    INVOICE_CREATION_LOAD_BONUS,
    INVOICE_CREATION_CHANGE_BONUS_QTY,
    INVOICE_CREATION_LOAD_BATCH_DETAILS,
    INVOICE_CREATION_OPEN_BATCH_EDIT_FORM,
    INVOICE_CREATION_CHANGE_BATCH_QTY,
    INVOICE_CREATION_CANCEL_BATCH_EDIT_FORM
} from "../../constants/actionTypes";

const initialState = {
    soNumber: "",
    details: {},
    loaded: false,
    discount: 0.00,
    bonusDetails: {},
    remark: ""
}

export default (state = initialState, action) => {
    switch (action.type) {
        case INVOICE_CREATION_CHANGE_SO_NUMBER:
            return {
                ...state,
                soNumber: action.payload.number
            };
        case INVOICE_CREATION_LOAD_SO_DETAILS:
            return {
                ...state,
                details: action.payload.details.mapToObject('id'),
                discount: action.payload.discount,
                bonusDetails: action.payload.bonusDetails.mapToObject('id'),
                remark: action.payload.remark,
                loaded: true
            };
        case INVOICE_CREATION_CHANGE_QTY:
            return {
                ...state,
                details: {
                    ...state.details,
                    [action.payload.id]: {
                        ...state.details[action.payload.id],
                        invoiceQty: action.payload.qty
                    }
                }
            };
        case INVOICE_CREATION_CHANGE_DISCOUNT:
            return {
                ...state,
                details: {
                    ...state.details,
                    [action.payload.id]: {
                        ...state.details[action.payload.id],
                        discountPercent: action.payload.discountPercent
                    }
                }
            }
        case INVOICE_CREATION_CLEAR_PAGE:
            return {
                ...initialState,
            }
        case INVOICE_CREATION_LOAD_BONUS:
            return {
                ...state,
                bonusDetails: action.payload.bonusDetails.mapToObject('id')
            };
        case INVOICE_CREATION_CHANGE_BONUS_QTY:
            return {
                ...state,
                bonusDetails: {
                    ...state.bonusDetails,
                    [action.payload.id]: {
                        ...state.bonusDetails[action.payload.id],
                        products: {
                            ...state.bonusDetails[action.payload.id].products,
                            [action.payload.productId]: {
                                ...state.bonusDetails[action.payload.id].products[action.payload.productId],
                                qty: action.payload.qty
                            }
                        }
                    }
                }
            };
        case INVOICE_CREATION_LOAD_BATCH_DETAILS:
            return {
                ...state,
                details: {
                    ...state.details,
                    [action.payload.id]: {
                        ...state.details[action.payload.id],
                        batchDetails: action.payload.batchDetails
                    }
                }
            }
        case INVOICE_CREATION_OPEN_BATCH_EDIT_FORM:
            return {
                ...state,
                batchEditLine: action.payload.line
            };
        case INVOICE_CREATION_CHANGE_BATCH_QTY:
            return {
                ...state,
                details: {
                    ...state.details,
                    [state.batchEditLine]: {
                        ...state.details[state.batchEditLine],
                        availableBatches: {
                            ...state.details[state.batchEditLine].availableBatches,
                            [action.payload.batch]: {
                                ...state.details[state.batchEditLine].availableBatches[action.payload.batch],
                                qty: action.payload.qty
                            }
                        }
                    }
                }
            }
        case INVOICE_CREATION_CANCEL_BATCH_EDIT_FORM:
            return {
                ...state,
                details: {
                    ...state.details,
                    [state.batchEditLine]: {
                        ...state.details[state.batchEditLine],
                        availableBatches: Object.values(state.details[state.batchEditLine].availableBatches).map(batch => ({...batch, qty: 0 })).mapToObject('id')
                    }
                },
                batchEditLine: undefined
            };
        default:
            return state;
    }
}