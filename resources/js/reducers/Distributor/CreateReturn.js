import {
    CREATE_RETURN_CHANGE_DISTRIBUTOR,
    CREATE_RETURN_CHANGE_SALESMAN,
    CREATE_RETURN_CHANGE_CUSTOMER,
    CREATE_RETURN_CHANGE_CHANGE_INVOICE_NUMBER,
    CREATE_RETURN_ADD_LINE,
    CREATE_RETURN_REMOVE_LINE,
    CREATE_RETURN_CHANGE_DISCOUNT,
    CREATE_RETURN_CHANGE_PRODUCT,
    CREATE_RETURN_LOAD_LINE_INFO,
    CREATE_RETURN_CHANGE_QTY,
    CREATE_RETURN_CLEAR_PAGE,
    CREATE_RETURN_LOAD_BONUS_DATA,
    CREATE_RETURN_CHANGE_BONUS_QTY,
    CREATE_RETURN_CHANGE_SALABLE,
    CREATE_RETURN_CHANGE_REASON,
    CREATE_RETURN_CHANGE_BATCH,
    CREATE_RETURN_CHANGE_BONUS_BATCH
} from '../../constants/actionTypes';

const emptyLine = {
    product: undefined,
    price: 0.00,
    stock: 0,
    qty: 0,
    discount: 0,
    id: 0,
    salable:false,
    reason: null,
    batch: null
}

const initialState = {
    distributor: undefined,
    salesman: undefined,
    customer: undefined,
    invNumber: "",
    lines:{
        0:{...emptyLine}
    },
    lastId:0,
    bonusLines: {}
}

export default (state=initialState,action)=>{
    switch (action.type) {
        case CREATE_RETURN_CHANGE_DISTRIBUTOR:
            return {
                ...state,
                distributor: action.payload.distributor,
                salesman: action.payload.distributor?state.salesman:null,
            };
        case CREATE_RETURN_CHANGE_SALESMAN:
            return {
                ...state,
                salesman: action.payload.salesman
            };
        case CREATE_RETURN_CHANGE_CUSTOMER:
            return {
                ...state,
                customer: action.payload.customer
            };
        case CREATE_RETURN_CHANGE_CHANGE_INVOICE_NUMBER:
            return {
                ...state,
                invNumber: action.payload.number
            };
        case CREATE_RETURN_ADD_LINE:
            return {
                ...state,
                lines:{
                    ...state.lines,
                    [state.lastId+1]:{...emptyLine,id:state.lastId+1}
                },
                lastId: state.lastId+1
            };
        case CREATE_RETURN_REMOVE_LINE:
            const modedLines = {...state.lines};

            delete modedLines[action.payload.id];

            return {
                ...state,
                lines: {
                    ... modedLines
                }
            };
        case CREATE_RETURN_LOAD_LINE_INFO:
            return {
                ...state,
                lines:{
                    ...state.lines,
                    [action.payload.id]:{
                        ...state.lines[action.payload.id],
                        stock: action.payload.stock,
                        price: action.payload.price
                    }
                }
            };
        case CREATE_RETURN_CHANGE_PRODUCT:
            return {
                ...state,
                lines:{
                    ...state.lines,
                    [action.payload.id]:{
                        ...state.lines[action.payload.id],
                        product: action.payload.product
                    }
                }
            };
        case CREATE_RETURN_CHANGE_BATCH:
            return {
                ...state,
                lines:{
                    ...state.lines,
                    [action.payload.id]:{
                        ...state.lines[action.payload.id],
                        batch: action.payload.batch
                    }
                }
            };
        case CREATE_RETURN_CHANGE_DISCOUNT:
            return {
                ...state,
                lines:{
                    ...state.lines,
                    [action.payload.id]:{
                        ...state.lines[action.payload.id],
                        discount: action.payload.discount
                    }
                }
            };
        case CREATE_RETURN_CHANGE_QTY:
            return {
                ...state,
                lines:{
                    ...state.lines,
                    [action.payload.id]:{
                        ...state.lines[action.payload.id],
                        qty: action.payload.qty
                    }
                }
            };
        case CREATE_RETURN_CHANGE_SALABLE:
            return {
                ...state,
                lines:{
                    ...state.lines,
                    [action.payload.id]:{
                        ...state.lines[action.payload.id],
                        salable: action.payload.salable
                    }
                }
            };
        case CREATE_RETURN_CHANGE_REASON:
            return {
                ...state,
                lines:{
                    ...state.lines,
                    [action.payload.id]:{
                        ...state.lines[action.payload.id],
                        reason: action.payload.reason
                    }
                }
            };
        case CREATE_RETURN_CLEAR_PAGE:
            return  initialState;
        case CREATE_RETURN_LOAD_BONUS_DATA:
            return {
                ...state,
                bonusLines: action.payload.bonusLines.mapToObject('id')
            };
        case CREATE_RETURN_CHANGE_BONUS_QTY:
            return {
                ...state,
                bonusLines: {
                    ...state.bonusLines,
                    [action.payload.id]:{
                        ...state.bonusLines[action.payload.id],
                        products:{
                            ...state.bonusLines[action.payload.id].products,
                            [action.payload.productId]:{
                                ...state.bonusLines[action.payload.id].products[action.payload.productId],
                                qty:action.payload.qty
                            }
                        }
                    }
                }
            }
        case CREATE_RETURN_CHANGE_BONUS_BATCH:
            return {
                ...state,
                bonusLines: {
                    ...state.bonusLines,
                    [action.payload.id]:{
                        ...state.bonusLines[action.payload.id],
                        products:{
                            ...state.bonusLines[action.payload.id].products,
                            [action.payload.productId]:{
                                ...state.bonusLines[action.payload.id].products[action.payload.productId],
                                batch:action.payload.batch
                            }
                        }
                    }
                }
            }
        default:
            return state;
    }
}