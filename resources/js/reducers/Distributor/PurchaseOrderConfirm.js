import {
    PURCHASE_ORDER_CONFIRM_NUMBER_CHANGE,
    PURCHASE_ORDER_CONFIRM_PRODUCT_CHANGE,
    PURCHASE_ORDER_CONFIRM_QTY_CHANGE,
    PURCHASE_ORDER_CONFIRM_DETAILS_CHANGE,
    PURCHASE_ORDER_CONFIRM_ADD_LINE,
    PURCHASE_ORDER_CONFIRM_REMOVE_LINE,
    PURCHASE_ORDER_CONFIRM_CLEAR_PAGE,
    PURCHASE_ORDER_CONFIRM_LOAD_DATA,
    PURCHASE_ORDER_CONFIRM_LOADING,
} from "../../constants/actionTypes";

const initialState = {
    number: undefined,
    lines: {
        0: {
            product: null,
            price: 0.00,
            qty: 0,
            id: 0,
            packSize: null,
            code: null,
            stockInHand: 0,
            stockPending: 0
        }
    },
    lastId: 0,
    searched: false,
    loading: false
}

export default (state = initialState, action) => {
    switch (action.type) {
        case PURCHASE_ORDER_CONFIRM_NUMBER_CHANGE:
            return {
                ...state,
                number: action.payload.number
            };
        case PURCHASE_ORDER_CONFIRM_PRODUCT_CHANGE:
            return {
                ...state,
                lines: {
                    ...state.lines,
                    [action.payload.number]: {
                        ...state.lines[action.payload.number],
                        product: action.payload.product
                    }
                }
            }
        case PURCHASE_ORDER_CONFIRM_QTY_CHANGE:
            return {
                ...state,
                lines: {
                    ...state.lines,
                    [action.payload.number]: {
                        ...state.lines[action.payload.number],
                        qty: action.payload.qty
                    }
                }
            }

        case PURCHASE_ORDER_CONFIRM_DETAILS_CHANGE:
            return {
                ...state,
                lines: {
                    ...state.lines,
                    [action.payload.number]: {
                        ...state.lines[action.payload.number],
                        price: action.payload.price,
                        stockInHand: action.payload.stockInHand,
                        stockPending: action.payload.stockPending,
                        packSize: action.payload.packSize,
                        code: action.payload.code
                    }
                }
            };
        case PURCHASE_ORDER_CONFIRM_ADD_LINE:
            return {
                ...state,
                lines: {
                    ...state.lines,
                    [state.lastId + 1]: {
                        product: null,
                        price: 0.00,
                        qty: 0,
                        id: state.lastId + 1
                    }
                },
                lastId: state.lastId + 1
            };
        case PURCHASE_ORDER_CONFIRM_REMOVE_LINE:

            const modedLines = {...state.lines };

            delete modedLines[action.payload.number];

            return {
                ...state,
                lines: modedLines
            };
        case PURCHASE_ORDER_CONFIRM_CLEAR_PAGE:
            return initialState;
        case PURCHASE_ORDER_CONFIRM_LOAD_DATA:
            return {
                ...state,
                lines: action.payload.lines.mapToObject('id'),
                lastId: action.payload.lines.length - 1,
                searched: true
            }
        case PURCHASE_ORDER_CONFIRM_LOADING:
            return {
                ...state,
                loading: action.payload.loading
            };
        default:
            return state;
    }
}