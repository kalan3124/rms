import {
    SITE_ALLOCATION_CHANGE_CHECKED_DSR,
    SITE_ALLOCATION_CHANGE_CHECKED_SITE,
    SITE_ALLOCATION_DSR_LOADED,
    SITE_ALLOCATION_SITE_LOADED,
    SITE_ALLOCATION_CHANGE_DSR_NAME,
    SITE_ALLOCATION_CHANGE_SITE_NAME,
    SITE_ALLOCATION_CLEAR_PAGE,
    SITE_ALLOCATION_APPEND_CHECKED_SITE
} from "../../constants/actionTypes";

const initialState = {
    dsrResults: [],
    dsrChecked: [],
    siteResults: [],
    siteChecked: [],
    dsrName: "",
    siteName: ""
}

export default (state = initialState, {
    type,
    payload
}) => {
    switch (type) {
        case SITE_ALLOCATION_CHANGE_CHECKED_DSR:
            return {
                ...state,
                dsrChecked: payload.dsrChecked
            };
        case SITE_ALLOCATION_CHANGE_CHECKED_SITE:
            return {
                ...state,
                siteChecked: [...state.siteChecked, ...payload.siteChecked]
            };
        case SITE_ALLOCATION_DSR_LOADED:
            return {
                ...state,
                dsrResults: payload.dsrResults
            };
        case SITE_ALLOCATION_SITE_LOADED:
            return {
                ...state,
                siteResults: payload.siteResults
            };
        case SITE_ALLOCATION_CHANGE_DSR_NAME:
            return {
                ...state,
                dsrName: payload.dsrName
            };
        case SITE_ALLOCATION_CHANGE_SITE_NAME:
            return {
                ...state,
                siteName: payload.siteName
            };
        case SITE_ALLOCATION_APPEND_CHECKED_SITE:
            return {
                ...state,
                dsrChecked: [...state.dsrChecked,...payload.dsr]
            };
        case SITE_ALLOCATION_CLEAR_PAGE:
            return {
                ...state,
                dsrChecked: [],
                    siteChecked: []
            };
        default:
            return state;
    }
}
