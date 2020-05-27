import {
    REPORT_CLEAR_PAGE,
    REPORT_CHANGE_REPORT,
    REPORT_CHANGE_SEARCH_TERMS,
    REPORT_RESULTS_LOADED,
    REPORT_CHANGE_COLUMNS
} from '../../constants/actionTypes';


const emptySearchParams = {
    page:1,
    perPage:25,
    sortBy:undefined,
    sortMode:'desc',
    values:{},
}

const initialState = {
    updateColumnsOnSearch:false,
    results:[],
    resultCount:0,
    columns:{},
    title:'',
    inputs:{},
    inputsStructure:[],
    report:'',
    searched:false,
    additionalHeaders:[],
    ...emptySearchParams
}

export default (state=initialState,action)=>{
    switch (action.type) {
        case REPORT_CLEAR_PAGE:
            return initialState;
        case REPORT_CHANGE_REPORT:
            const {columns,title,inputs,inputsStructure,report,additionalHeaders,updateColumnsOnSearch} = action.payload;
            return {
                ...state,columns,title,inputs,inputsStructure,report,additionalHeaders,updateColumnsOnSearch,values:{}
            }
        case REPORT_CHANGE_COLUMNS:
            return {
                ...state,
                columns:action.payload.columns,
                additionalHeaders: action.payload.additionalHeaders
            }
        case REPORT_CHANGE_SEARCH_TERMS:
            const {searchTerms} = action.payload
            return {
                ...state,...searchTerms
            }
        case REPORT_RESULTS_LOADED:
            return {
                ...state,
                results:action.payload.results,
                resultCount:action.payload.count,
                searched:true
            }
        default:
            return state;
    }
}