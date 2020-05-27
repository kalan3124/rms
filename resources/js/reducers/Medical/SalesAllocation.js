import {
    SALES_ALLOCATION_CHANGE_TEAM,
    SALES_ALLOCATION_CHANGE_UPDATING_MODE,
    SALES_ALLOCATION_CHANGE_MODE,
    SALES_ALLOCATION_LOAD_DATA,
    SALES_ALLOCATION_LOAD_SECTION_DATA,
    SALES_ALLOCATION_SELECT_ROW,
    SALES_ALLOCATION_CHANGE_SEARCH_TERM,
    SALES_ALLOCATION_CHANGE_PAGE,
    SALES_ALLOCATION_CHANGE_PER_PAGE,
    SALES_ALLOCATION_CHANGE_MEMBER_PERCENTAGE
} from "../../constants/actionTypes";

const initialState = {
    team: undefined,
    updatingMode: {
        towns: "include",
        customers: "include",
        products: "include"
    },
    activeMode: undefined,
    checked: {
        towns: {},
        customers: {},
        products: {}
    },
    sectionData: {},
    searchTerm: "",
    resultsCount: 0,
    page: 1,
    perPage: 10,
    members:{}
};

export default (state = initialState, action) => {
    switch (action.type) {
        case SALES_ALLOCATION_CHANGE_TEAM:
            return {
                ...state,
                team: action.team,
                activeMode:action.team?"towns":""
            };
        case SALES_ALLOCATION_CHANGE_UPDATING_MODE:
            return {
                ...state,
                updatingMode: {
                    ...state.updatingMode,
                    [state.activeMode]: action.updatingMode
                },
                checked: {
                    ...state.checked,
                    [state.activeMode]: {}
                }
            };
        case SALES_ALLOCATION_CHANGE_MODE:
            return {
                ...state,
                activeMode:
                    state.activeMode == action.mode ? undefined : action.mode,
                searchTerm: "",
                page: 1,
                perPage: 10
            };
        case SALES_ALLOCATION_LOAD_DATA:
            return {
                ...state,
                updatingMode: action.modes,
                checked: {
                    towns:action.results.towns.mapToObject('id'),
                    customers: action.results.customers.mapToObject('id'),
                    products: action.results.products.mapToObject('id'),
                },
                members: action.members.mapToObject('id'),
                activeMode:"towns"
            };
        case SALES_ALLOCATION_LOAD_SECTION_DATA:
            return {
                ...state,
                sectionData: action.results.mapToObject("id"),
                resultsCount: action.count
            };
        case SALES_ALLOCATION_SELECT_ROW:
            let newResults = { ...state.checked[state.activeMode] };

            if (state.checked[state.activeMode][action.row.id]) {
                delete newResults[action.row.id];
            } else {
                newResults[action.row.id] = action.row;
            }

            return {
                ...state,
                checked: {
                    ...state.checked,
                    [state.activeMode]: {
                        ...newResults
                    }
                }
            };
        case SALES_ALLOCATION_CHANGE_SEARCH_TERM:
            return {
                ...state,
                searchTerm: action.value,
                page: 1,
                perPage: 10
            };
        case SALES_ALLOCATION_CHANGE_PAGE:
            return {
                ...state,
                page: action.page
            };
        case SALES_ALLOCATION_CHANGE_PER_PAGE:
            return {
                ...state,
                perPage: action.perPage
            };
        case SALES_ALLOCATION_CHANGE_MEMBER_PERCENTAGE:
            return {
                ...state,
                members:{
                    ...state.members,
                    [action.id]:{
                        ...state.members[action.id],
                        value:action.value
                    }
                }
            };
        default:
            return state;
    }
};
