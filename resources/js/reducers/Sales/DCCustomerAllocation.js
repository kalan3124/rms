import {
    DC_ALLOCATION_CHANGE_DC_USER_NAME,
    DC_ALLOCATION_CHANGE_DC_CHEMIST_NAME,
    DC_ALLOCATION_USER_LOADED,
    DC_ALLOCATION_CHEMIST_LOADED,
    DC_ALLOCATION_CHANGE_CHECKED_USER,
    DC_ALLOCATION_CHANGE_CHECKED_CHEMIST,
    DC_ALLOCATION_APPEND_CHECKED_CHEMIST,
    DC_ALLOCATION_CLEAR_PAGE
} from "../../constants/actionTypes";

const initialState = {
    dcUser: "",
    dcChemist: "",
    userResults: [],
    userChecked: [],
    chemistResults: [],
    chemistChecked: [],
}

export default (state = initialState, {
    type,
    payload
}) => {
    switch (type) {
        case DC_ALLOCATION_CHANGE_DC_USER_NAME:
            return {
                ...state,
                dcUser: payload.dcUser
            };
        case DC_ALLOCATION_CHANGE_DC_CHEMIST_NAME:
            return {
                ...state,
                dcChemist: payload.dcChemist
            };
        case DC_ALLOCATION_USER_LOADED:
            return {
                ...state,
                userResults: payload.userResults
            };
        case DC_ALLOCATION_CHEMIST_LOADED:
            return {
                ...state,
                chemistResults: payload.chemistResults
            };
        case DC_ALLOCATION_CHANGE_CHECKED_USER:
            return {
                ...state,
                userChecked: payload.userChecked
            };
        case DC_ALLOCATION_CHANGE_CHECKED_CHEMIST:
            return {
                ...state,
                chemistChecked: payload.chemistChecked
            };
        case DC_ALLOCATION_APPEND_CHECKED_CHEMIST:
            return {
                ...state,
                chemistChecked: [...state.chemistChecked,...payload.chemist]
            };
        case DC_ALLOCATION_CLEAR_PAGE:
            return {
                ...state,
                userChecked: [],
                chemistChecked: []
            };
        default:
            return state;
    }
}
