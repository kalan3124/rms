import {
    ROUTE_CHEMIST_CHANGE_CHEMIST_KEYWORD,
    ROUTE_CHEMIST_CHANGE_ROUTE_KEYWORD,
    ROUTE_CHEMIST_ADD_CHEMISTS,
    ROUTE_CHEMIST_ADD_ROUTES,
    ROUTE_CHEMIST_LOADED_ROUTES,
    ROUTE_CHEMIST_LOADED_CHEMISTS,
    ROUTE_CHEMIST_REMOVE_CHEMISTS,
    ROUTE_CHEMIST_REMOVE_ROUTES,
    ROUTE_CHEMIST_CLEAR_PAGE,
    ROUTE_CHEMIST_LOADED_CHECKED_CHEMISTS,
    ROUTE_CHEMIST_AREA_CHANGE,
    ROUTE_CHEMIST_TYPE_CHANGE
} from "../../constants/actionTypes";

const initialState = {
    chemistKeyword: "",
    routeKeyword: "",
    chemists: [],
    routes: [],
    selectedChemists: [],
    selectedRoutes: [],
    area: [],
    type: ""
};

export default (state = initialState, { payload, type }) => {
    switch (type) {
        case ROUTE_CHEMIST_CHANGE_CHEMIST_KEYWORD:
            return {
                ...state,
                chemistKeyword: payload.keyword
            };
        case ROUTE_CHEMIST_CHANGE_ROUTE_KEYWORD:
            return {
                ...state,
                routeKeyword: payload.keyword
            };
        case ROUTE_CHEMIST_LOADED_CHEMISTS:
            return {
                ...state,
                chemists: payload.chemists
            };
        case ROUTE_CHEMIST_LOADED_ROUTES:
            return {
                ...state,
                routes: payload.routes
            };
        case ROUTE_CHEMIST_ADD_CHEMISTS:
            return {
                ...state,
                selectedChemists: [...state.selectedChemists, payload.chemist]
            };
        case ROUTE_CHEMIST_ADD_ROUTES:
            return {
                ...state,
                selectedRoutes: [payload.route]
            };
        case ROUTE_CHEMIST_REMOVE_ROUTES:
            return {
                ...state,
                selectedRoutes: state.selectedRoutes.filter(
                    route => route.value != payload.route.value
                )
            };
        case ROUTE_CHEMIST_REMOVE_CHEMISTS:
            return {
                ...state,
                selectedChemists: state.selectedChemists.filter(
                    chemist => chemist.value != payload.chemist.value
                )
            };
        case ROUTE_CHEMIST_CLEAR_PAGE:
            return {
                ...state,
                selectedChemists: [],
                selectedRoutes: []
            };
        case ROUTE_CHEMIST_LOADED_CHECKED_CHEMISTS:
            return {
                ...state,
                selectedChemists: [...payload.chemists]
            };
        case ROUTE_CHEMIST_AREA_CHANGE:
            return {
                ...state,
                area: payload.area
            };
        case ROUTE_CHEMIST_TYPE_CHANGE:
            return {
                ...state,
                type: payload.type
            };
        default:
            return state;
    }
};
