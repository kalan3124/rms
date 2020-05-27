import {
    PERMISSION_TYPE_LOADED,
    PERMISSION_EXPAND_PANEL,
    PERMISSION_CHANGE_VALUES,
    PERMISSION_CHANGE_TAB,
    PERMISSION_CHANGE_KEYWORD,
    PERMISSION_RESULTS_LOADED,
    PERMISSION_SELECT_ITEMS,
    PERMISSION_CLEAR_PAGE
} from "../constants/actionTypes";

const initialState = {
    permissions: {},
    permissionValues: [],
    expanded: undefined,
    activeTab: 0,
    keyword: "",
    results: [],
    users: [],
    permissionGroups: []
}

export default (state = initialState, {
    payload,
    type
}) => {
    switch (type) {
        case PERMISSION_TYPE_LOADED:
            return {
                ...state,
                permissions: payload.permissions
            };
        case PERMISSION_EXPAND_PANEL:
            return {
                ...state,
                expanded: payload.expanded
            };
        case PERMISSION_CHANGE_VALUES:
            return {
                ...state,
                permissionValues: payload.permissionValues
            };
        case PERMISSION_CHANGE_TAB:
            return {
                ...state,
                activeTab: payload.tab
            };
        case PERMISSION_CHANGE_KEYWORD:
            return {
                ...state,
                keyword: payload.keyword
            };
        case PERMISSION_RESULTS_LOADED:
            return {
                ...state,
                results: payload.results
            };
        case PERMISSION_SELECT_ITEMS:
            return {
                ...state,
                users: payload.users,
                permissionGroups: payload.permissionGroups
            };
        case PERMISSION_CLEAR_PAGE:
            return {
                ...state,
                permissionValues: [],
                expanded: undefined,
                activeTab: 0,
                users: [],
                permissionGroups: []
            }
        default:
            return state;
    }
}
