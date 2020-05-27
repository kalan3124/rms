import {
    SALESMAN_ALLOCATION_CHANGE_CHECKED_DSR,
    SALESMAN_ALLOCATION_CHANGE_CHECKED_SR,
    SALESMAN_ALLOCATION_DSR_LOADED,
    SALESMAN_ALLOCATION_SR_LOADED,
    SALESMAN_ALLOCATION_CHANGE_DCR_NAME,
    SALESMAN_ALLOCATION_CHANGE_SR_NAME,
    SALESMAN_ALLOCATION_CLEAR_PAGE,
    SALESMAN_ALLOCATION_APPEND_CHECKED_SR
} from "../../constants/actionTypes";

const initialState = {
    dsrResults: [],
    dsrChecked: [],
    srResults: [],
    srChecked: [],
    dsrName: "",
    srName: ""
}

export default (state = initialState, {
    type,
    payload
}) => {
    switch (type) {
        case SALESMAN_ALLOCATION_CHANGE_CHECKED_DSR:
            return {
                ...state,
                dsrChecked: payload.dsrChecked
            };
        case SALESMAN_ALLOCATION_CHANGE_CHECKED_SR:
            return {
                ...state,
                srChecked:[...state.srChecked,...payload.srChecked]
            };
        case SALESMAN_ALLOCATION_DSR_LOADED:
            return {
                ...state,
                dsrResults: payload.dsrResults
            };
        case SALESMAN_ALLOCATION_SR_LOADED:
            return {
                ...state,
                srResults: payload.srResults
            };
        case SALESMAN_ALLOCATION_CHANGE_DCR_NAME:
            return {
                ...state,
                dsrName: payload.dsrName
            };
        case SALESMAN_ALLOCATION_CHANGE_SR_NAME:
            return {
                ...state,
                srName: payload.srName
            };
        case SALESMAN_ALLOCATION_APPEND_CHECKED_SR:
            return {
                ...state,
                srChecked: [...payload.dsr]
            };
        case SALESMAN_ALLOCATION_CLEAR_PAGE:
            return {
                ...state,
                dsrChecked: [],
                    srChecked: []
            };
        default:
            return state;
    }
}