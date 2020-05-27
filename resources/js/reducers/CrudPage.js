import {
    CRUD_PAGE_LOADING,
    CRUD_PAGE_LOADED,
    CRUD_PAGE_FORM_VALUES_CHANGE,
    CRUD_PAGE_SEARCH_RESULTS_LOADING,
    CRUD_PAGE_SEARCH_RESULTS_LOADED,
    CRUD_PAGE_ORDER_BY_CHANGE,
    CRUD_FORM_CLEAR,
    CRUD_CHOOSE_TO_UPDATE,
    CRUD_CREATE_CLICK,
    CRUD_SEARCH_CLICK,
    CRUD_MODAL_CLOSE,
    CRUD_CHANGE_PAGE_NUMBER,
    CRUD_CHANGE_ROWS_COUNT,
    CRUD_CHOOSE_TO_DELETE,
    CRUD_CLEAR_DELETE,
    CRUD_CHOOSE_TO_RESTORE,
    CRUD_CLEAR_RESTORE
} from "../constants/actionTypes";

const initialState = {
    title: '',
    inputs: {},
    columns: {},
    loading: false,
    privilegedActions: [],
    structure: [],
    inline: true,
    updatingId: null,
    values: {},
    results: [],
    searching: false,
    sortBy: 'created_at',
    sortMode: 'desc',
    page: 1,
    perPage: 25,
    popupMode: undefined,
    resultCount:0,
    lastValues:{}
}

export default (state = initialState, action) => {
    switch (action.type) {
        case CRUD_PAGE_LOADING:
            return {
                ...state,
                loading: true,
                sortBy:'created_at',
                sortMode:'desc',
                values:{},
                results:[],
                inputs:{},
                columns:{}
            };
        case CRUD_PAGE_LOADED:
            const {
                title,
                inputs,
                columns,
                privilegedActions,
                inline,
                structure
            } = action.payload;
            return {
                ...state,
                loading: false,
                title,
                inputs,
                columns,
                privilegedActions,
                inline,
                structure
            };
        case CRUD_PAGE_FORM_VALUES_CHANGE:
            return {
                ...state,
                values: action.payload.values,
                lastValues: action.payload.values
            }
        case CRUD_PAGE_SEARCH_RESULTS_LOADING:
            return {
                ...state,
                searching: true
            }
        case CRUD_PAGE_SEARCH_RESULTS_LOADED:
            return {
                ...state,
                searching: false,
                results: action.payload.results,
                resultCount: action.payload.count
            }
        case CRUD_PAGE_ORDER_BY_CHANGE:
            return {
                ...state,
                sortBy: action.payload.column,
                sortMode: action.payload.mode
            }
        case CRUD_FORM_CLEAR:
            return {
                ...state,
                values: {},
                updatingId: undefined,
                popupMode:undefined
            }
        case CRUD_CHOOSE_TO_UPDATE:
            return {
                ...state,
                updatingId: action.payload.id,
                popupMode: 'update'
            }
        case CRUD_SEARCH_CLICK:
            return {
                ...state,
                popupMode: 'search',
            }
        case CRUD_CREATE_CLICK:
            return {
                ...state,
                popupMode: 'create',
            }
        case CRUD_MODAL_CLOSE:
            return {
                ...state,
                popupMode:undefined
            }
        case CRUD_CHANGE_PAGE_NUMBER:
            return {
                ...state,
                page:action.payload.page
            }
        case CRUD_CHANGE_ROWS_COUNT:
            return {
                ...state,
                perPage:action.payload.perPage
            }
        case CRUD_CHOOSE_TO_DELETE:
            return {
                ...state,
                updatingId:action.payload.id
            }
        case CRUD_CHOOSE_TO_RESTORE:
            return {
                ...state,
                updatingId: action.payload.id
            };
        case CRUD_CLEAR_DELETE:
            return {
                ...state,
                updatingId:undefined
            }
        case CRUD_CLEAR_RESTORE:
            return {
                ...state,
                updatingId: undefined
            }
        default:
            return state;
    }
}
