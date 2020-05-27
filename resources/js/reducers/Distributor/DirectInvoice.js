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

const emptyLine = {
    product: undefined,
    price: 0.00,
    stock: 0,
    qty: 0,
    batchDetails: [],
    discount: 0,
    id: 0,
    availableBatches: {}
}

const initialState = {
    distributor: undefined,
    salesman: undefined,
    customer: undefined,
    invNumber: "",
    lines: {
        0: {...emptyLine }
    },
    lastId: 0,
    bonusLines: {},
    batchEditLine: undefined
}

export default (state = initialState, action) => {
    switch (action.type) {
        case DIRECT_INVOICE_CHANGE_DISTRIBUTOR:
            return {
                ...state,
                distributor: action.payload.distributor,
                salesman: action.payload.distributor ? state.salesman : null,
            };
        case DIRECT_INVOICE_CHANGE_SALESMAN:
            return {
                ...state,
                salesman: action.payload.salesman
            };
        case DIRECT_INVOICE_CHANGE_CUSTOMER:
            return {
                ...state,
                customer: action.payload.customer
            };
        case DIRECT_INVOICE_CHANGE_CHANGE_INVOICE_NUMBER:
            return {
                ...state,
                invNumber: action.payload.number
            };
        case DIRECT_INVOICE_ADD_LINE:
            return {
                ...state,
                lines: {
                    ...state.lines,
                    [state.lastId + 1]: {...emptyLine, id: state.lastId + 1 }
                },
                lastId: state.lastId + 1
            };
        case DIRECT_INVOICE_REMOVE_LINE:
            const modedLines = {...state.lines };

            delete modedLines[action.payload.id];

            return {
                ...state,
                lines: {
                    ...modedLines
                }
            };
        case DIRECT_INVOICE_LOAD_LINE_INFO:
            return {
                ...state,
                lines: {
                    ...state.lines,
                    [action.payload.id]: {
                        ...state.lines[action.payload.id],
                        stock: action.payload.stock,
                        price: action.payload.price,
                        availableBatches: action.payload.availableBatches.mapToObject('id')
                    }
                }
            };
        case DIRECT_INVOICE_CHANGE_PRODUCT:
            return {
                ...state,
                lines: {
                    ...state.lines,
                    [action.payload.id]: {
                        ...state.lines[action.payload.id],
                        product: action.payload.product
                    }
                }
            };
        case DIRECT_INVOICE_CHANGE_DISCOUNT:
            return {
                ...state,
                lines: {
                    ...state.lines,
                    [action.payload.id]: {
                        ...state.lines[action.payload.id],
                        discount: action.payload.discount
                    }
                }
            };
        case DIRECT_INVOICE_CHANGE_QTY:
            return {
                ...state,
                lines: {
                    ...state.lines,
                    [action.payload.id]: {
                        ...state.lines[action.payload.id],
                        qty: action.payload.qty
                    }
                }
            };
        case DIRECT_INVOICE_CLEAR_PAGE:
            return initialState;
        case DIRECT_INVOICE_LOAD_BONUS_DATA:
            return {
                ...state,
                bonusLines: action.payload.bonusLines.mapToObject('id')
            };
        case DIRECT_INVOICE_CHANGE_BONUS_QTY:
            return {
                ...state,
                bonusLines: {
                    ...state.bonusLines,
                    [action.payload.id]: {
                        ...state.bonusLines[action.payload.id],
                        products: {
                            ...state.bonusLines[action.payload.id].products,
                            [action.payload.productId]: {
                                ...state.bonusLines[action.payload.id].products[action.payload.productId],
                                qty: action.payload.qty
                            }
                        }
                    }
                }
            }
        case DIRECT_INVOICE_LOAD_BATCH_DETAILS:
            return {
                ...state,
                lines: {
                    ...state.lines,
                    [action.payload.id]: {
                        ...state.lines[action.payload.id],
                        batchDetails: action.payload.batchDetails
                    }
                }
            }
        case DIRECT_INVOICE_OPEN_BATCH_EDIT_FORM:
            return {
                ...state,
                batchEditLine: action.payload.line
            };
        case DIRECT_INVOICE_CHANGE_BATCH_QTY:
            return {
                ...state,
                lines: {
                    ...state.lines,
                    [state.batchEditLine]: {
                        ...state.lines[state.batchEditLine],
                        availableBatches: {
                            ...state.lines[state.batchEditLine].availableBatches,
                            [action.payload.batch]: {
                                ...state.lines[state.batchEditLine].availableBatches[action.payload.batch],
                                qty: action.payload.qty
                            }
                        }
                    }
                }
            };
        case DIRECT_INVOICE_CANCEL_BATCH_EDIT_FORM:
            return {
                ...state,
                lines: {
                    ...state.lines,
                    [state.batchEditLine]: {
                        ...state.lines[state.batchEditLine],
                        availableBatches: Object.values(state.lines[state.batchEditLine].availableBatches).map(batch => ({...batch, qty: 0 })).mapToObject('id')
                    }
                },
                batchEditLine: undefined
            };
        default:
            return state;
    }
}