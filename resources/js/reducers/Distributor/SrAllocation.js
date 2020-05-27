import {
    SR_ALLOCATION_CHANGE_CHECKED_DSR,
    SR_ALLOCATION_CHANGE_CHECKED_SR,
    SR_ALLOCATION_DSR_LOADED,
    SR_ALLOCATION_SR_LOADED,
    SR_ALLOCATION_CHANGE_DCR_NAME,
    SR_ALLOCATION_CHANGE_SR_NAME,
    SR_ALLOCATION_CLEAR_PAGE,
    SR_ALLOCATION_APPEND_CHECKED_SR
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
        case SR_ALLOCATION_CHANGE_CHECKED_DSR:
            return {
                ...state,
                dsrChecked: payload.dsrChecked
            };
        case SR_ALLOCATION_CHANGE_CHECKED_SR:
            return {
                ...state,
                srChecked:[...state.srChecked,...payload.srChecked]
            };
        case SR_ALLOCATION_DSR_LOADED:
            return {
                ...state,
                dsrResults: payload.dsrResults
            };
        case SR_ALLOCATION_SR_LOADED:
            return {
                ...state,
                srResults: payload.srResults
            };
        case SR_ALLOCATION_CHANGE_DCR_NAME:
            return {
                ...state,
                dsrName: payload.dsrName
            };
        case SR_ALLOCATION_CHANGE_SR_NAME:
            return {
                ...state,
                srName: payload.srName
            };
        case SR_ALLOCATION_APPEND_CHECKED_SR:
            return {
                ...state,
                srChecked: [...payload.dsr]
            };
        case SR_ALLOCATION_CLEAR_PAGE:
            return {
                ...state,
                dsrChecked: [],
                    srChecked: []
            };
        default:
            return state;
    }
}
