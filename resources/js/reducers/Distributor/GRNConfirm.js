import {
    GRN_CONFIRM_CHANGE_GRN_NO,
    GRN_CONFIRM_LOAD_PRODUCTS,
    GRN_CONFIRM_CLEAR_PAGE,
    GRN_CONFIRM_CHANGE_QTY
} from '../../constants/actionTypes';

const initialState = {
    grnNumber: "",
    lines: {},
    grnId: 0
}

export default (state=initialState,action)=>{
    switch (action.type) {
        case GRN_CONFIRM_CHANGE_GRN_NO:
            return {
                ...state,
                grnNumber: action.payload.number
            };
        case GRN_CONFIRM_LOAD_PRODUCTS:
            return {
                ...state,
                lines: action.payload.products.mapToObject('id'),
                grnId: action.payload.grnId
            };
        case GRN_CONFIRM_CHANGE_QTY:
            return {
                ...state,
                lines: {
                    ...state.lines,
                    [action.payload.id]:{
                        ...state.lines[action.payload.id],
                        qty: action.payload.qty
                    }
                }
            }
        case GRN_CONFIRM_CLEAR_PAGE:
            return initialState;
        default:
            return state;
    }
}
