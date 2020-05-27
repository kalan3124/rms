import { TEAM_PERFORMANCE_REPORT_LOAD_VALUES,TEAM_PERFORMANCE_REPORT_LOAD_RESULT,TEAM_PERFORMANCE_REPORT_PAGE,TEAM_PERFORMANCE_REPORT_PERPAGE } from "../../constants/actionTypes";

const initialState = {
    rowData:[],
    values:{},
    resultCount:0,
    searched:false,
    perPage:25,
    page:1,
    hodData:[]
}

export default (state=initialState,{payload,type})=>{
     switch (type) {
        case TEAM_PERFORMANCE_REPORT_LOAD_VALUES:
            return {
                 ...state,
                values:{
                    ...state.values,
                    [payload.name]:payload.value
                }
            };
        case TEAM_PERFORMANCE_REPORT_LOAD_RESULT:
            return {
                 ...state,
                 rowData: payload.results,
                 resultCount: payload.count,
                 searched:true,
                 hodData:payload.hod
            };
        case TEAM_PERFORMANCE_REPORT_PAGE:
            return {
                 ...state,
                 page: payload.page
            };
        case TEAM_PERFORMANCE_REPORT_PERPAGE:
            return {
                 ...state,
                 perPage: payload.perPage
            };
        
        default:
             return state;
    }  
 }