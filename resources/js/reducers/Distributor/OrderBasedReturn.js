import { 
    ORDER_BASED_RETURN_CHANGE_INVOICE_NUMBER, 
    ORDER_BASED_RETURN_LOAD_INVOICE_INFO, 
    ORDER_BASED_RETURN_LOAD_BONUS_DETAILS, 
    ORDER_BASED_RETURN_CHANGE_QTY,
    ORDER_BASED_RETURN_CLEAR_PAGE,
    ORDER_BASED_RETURN_CHANGE_BONUS_QTY,
    ORDER_BASED_RETURN_CHANGE_REASON,
    ORDER_BASED_RETURN_CHANGE_SALABLE
} from "../../constants/actionTypes";

const initialState = {
    invNumber: "",
    lines:{},
    bonusLines:{},
    loaded: false,
    returnNumber: ""
}

export default (state=initialState, action)=>{
    switch (action.type) {
        case ORDER_BASED_RETURN_CHANGE_INVOICE_NUMBER:
            return {
                ...state,
                invNumber: action.payload.invNumber
            };
        case ORDER_BASED_RETURN_LOAD_INVOICE_INFO:
            return {
                ...state,
                lines: action.payload.lines.mapToObject('id'),
                bonusLines: action.payload.bonusLines.mapToObject('id'),
                returnNumber: action.payload.returnNumber,
                loaded: true,
            };
        case ORDER_BASED_RETURN_LOAD_BONUS_DETAILS:
            return {
                ...state,
                bonusLines: action.payload.bonusLines.mapToObject('id')
            };
        case ORDER_BASED_RETURN_CHANGE_QTY:
            return {
                ...state,
                lines: {
                    ...state.lines,
                    [action.payload.id]:{
                        ...state.lines[action.payload.id],
                        qty: action.payload.qty
                    }
                }
            };
        case ORDER_BASED_RETURN_CHANGE_REASON:
            return {
                ...state,
                lines: {
                    ...state.lines,
                    [action.payload.id]:{
                        ...state.lines[action.payload.id],
                        reason: action.payload.reason
                    }
                }
            };
        case ORDER_BASED_RETURN_CHANGE_SALABLE:
                return {
                    ...state,
                    lines: {
                        ...state.lines,
                        [action.payload.id]:{
                            ...state.lines[action.payload.id],
                            salable: action.payload.salable
                        }
                    }
                };
        case ORDER_BASED_RETURN_CLEAR_PAGE:
            return initialState;
        case ORDER_BASED_RETURN_CHANGE_BONUS_QTY:
            return {
                ...state,
                bonusLines:{
                    ...state.bonusLines,
                    [action.payload.id]:{
                        ...state.bonusLines[action.payload.id],
                        products:{
                            ...state.bonusLines[action.payload.id].products,
                            [action.payload.productId]:{
                                ...state.bonusLines[action.payload.id].products[action.payload.productId],
                                batchWise:{
                                    ...state.bonusLines[action.payload.id].products[action.payload.productId].batchWise,
                                    [action.payload.batchId]:{
                                        ...state.bonusLines[action.payload.id].products[action.payload.productId].batchWise[action.payload.batchId],
                                        qty:action.payload.qty
                                    }
                                }
                            }
                        }
                    }
                }
            }
        default:
            return state;
    }
}