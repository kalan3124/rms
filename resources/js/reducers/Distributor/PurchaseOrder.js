import {
    PURCHASE_ORDER_DISTRIBUTOR_CHANGE,
    PURCHASE_ORDER_NUMBER_CHANGE,
    PURCHASE_ORDER_PRODUCT_CHANGE,
    PURCHASE_ORDER_QTY_CHANGE,
    PURCHASE_ORDER_DETAILS_CHANGE,
    PURCHASE_ORDER_ADD_LINE,
    PURCHASE_ORDER_REMOVE_LINE,
    PURCHASE_ORDER_CLEAR_PAGE,
    PURCHASE_ORDER_DSR_CHANGE,
    PURCHASE_ORDER_SITE_CHANGE,
    PURCHASE_ORDER_LOADING
} from "../../constants/actionTypes";

const initialState = {
    distributor:undefined,
    number: undefined,
    lines: {
        0:{
            product:null,
            price: 0.00,
            qty:0,
            id: 0,
            pack_size:null,
            code:null
        }
    },
    lastId: 0,
    dsr: undefined,
    site: undefined,
    clicked: false
}

export default (state=initialState,action)=>{
    switch (action.type) {
        case PURCHASE_ORDER_DISTRIBUTOR_CHANGE:
            return {
                ...state,
                distributor: action.payload.distributor
            };
        case PURCHASE_ORDER_NUMBER_CHANGE:
            return {
                ...state,
                number: action.payload.number
            };
        case PURCHASE_ORDER_PRODUCT_CHANGE:
            return {
                ...state,
                lines:{
                    ...state.lines,
                    [action.payload.number]:{
                        ...state.lines[action.payload.number],
                        product: action.payload.product
                    }
                }
            }
        case PURCHASE_ORDER_QTY_CHANGE:
            return {
                ...state,
                lines:{
                    ...state.lines,
                    [action.payload.number]:{
                        ...state.lines[action.payload.number],
                        qty: action.payload.qty
                    }
                }
            }

        case PURCHASE_ORDER_DETAILS_CHANGE:
            return {
                ...state,
                lines:{
                    ...state.lines,
                    [action.payload.number]:{
                        ...state.lines[action.payload.number],
                        price: action.payload.price,
                        stockInHand: action.payload.stockInHand,
                        stockPending: action.payload.stockPending,
                        packSize: action.payload.packSize,
                        code: action.payload.code
                    }
                }
            };
        case PURCHASE_ORDER_ADD_LINE:
            return {
                ...state,
                lines:{
                    ...state.lines,
                    [state.lastId+1]:{
                        product:null,
                        price: 0.00,
                        qty:0,
                        id: state.lastId+1
                    }
                },
                lastId: state.lastId+1
            };
        case PURCHASE_ORDER_SITE_CHANGE:
            return {
                ...state,
                site: action.payload.site
            }
        case PURCHASE_ORDER_REMOVE_LINE:

            const modedLines = {...state.lines};

            delete modedLines[action.payload.number];

            return {
                ...state,
                lines:modedLines
            };
        case PURCHASE_ORDER_CLEAR_PAGE:
            return initialState;
        case PURCHASE_ORDER_DSR_CHANGE:
            return {
                ...state,
                dsr: action.payload.dsr
            };
        case PURCHASE_ORDER_LOADING:
            return {
                ...state,
                clicked: action.payload.clicked
            }
        default:
            return state;
    }
}
