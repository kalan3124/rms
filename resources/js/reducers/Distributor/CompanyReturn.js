import { COMPANY_RETURN_CHANGE_GRN_NUMBER, COMPANY_RETURN_LOAD_INFO, COMPANY_RETURN_CHANGE_QTY, COMPANY_RETURN_CHANGE_REASON, COMPANY_RETURN_CHANGE_SALLABLE, COMPANY_RETURN_CHANGE_REMARK, COMPANY_RETURN_CLEAR_PAGE } from "../../constants/actionTypes";

const initialState = {
    grnNumber: "",
    returnNumber: "",
    lines: {},
    remark: ""
};

export default (state=initialState, action)=>{
    switch (action.type) {
        case COMPANY_RETURN_CHANGE_GRN_NUMBER:
            return {
                ...state,
                grnNumber: action.payload.grnNumber
            };
        case COMPANY_RETURN_LOAD_INFO:
            return {
                ...state,
                lines: action.payload.lines.mapToObject('id'),
                returnNumber: action.payload.number
            };
        case COMPANY_RETURN_CHANGE_QTY:
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
        case COMPANY_RETURN_CHANGE_REASON:
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
        case COMPANY_RETURN_CHANGE_SALLABLE:
            return {
                ...state,
                lines: {
                    ...state.lines,
                    [action.payload.id]: {
                        ...state.lines[action.payload.id],
                        salable: action.payload.salable
                    }
                }
            };
        case COMPANY_RETURN_CHANGE_REMARK:
            return {
                ...state,
                remark: action.payload.remark,
            };
        case COMPANY_RETURN_CLEAR_PAGE:
            return {
                ...initialState
            };
        default:
            return state;
    }
}
