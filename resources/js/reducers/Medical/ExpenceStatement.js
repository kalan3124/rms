import { EXP_STMNT_LOAD_TYPES, EXP_STMNT_LOAD_RESULTS, EXP_STMNT_CHANGE_VALUE, EXP_STMNT_LOAD_BATA_TYPES, EXP_STMNT_LOAD_BATA_CAT } from "../../constants/actionTypes";

const initialState = {
    types:{},
    values:{},
    page:1,
    perPage:25,
    sortBy:'exp_date',
    searched:false,
    rowData:[],
    values:{},
    resultCount:0,
    bataTypes:[],
    bataCategories:[],
    loadAddKm:0,
    loadPrivateKm:0,
    day_mileage_limit:0
}

export default (state=initialState,{payload,type})=>{
    switch (type) {
        case EXP_STMNT_LOAD_TYPES:
            return {
                ...state,
                types:payload.types
            };
        case EXP_STMNT_LOAD_BATA_CAT:
            return {
                ...state,
                bataCategories: payload.categories
            }
        case EXP_STMNT_LOAD_RESULTS:
            return {
                ...state,
                rowData:payload.results,
                searched:true,
                resultCount:payload.count,
                loadAddKm:payload.sum_addtional,
                loadPrivateKm:payload.sum_private,
                day_mileage_limit:payload.day_mileage_limit
            };
        case EXP_STMNT_CHANGE_VALUE:
            return {
                ...state,
                values:{
                    ...state.values,
                    [payload.name]:payload.value
                }
            }
        case EXP_STMNT_LOAD_BATA_TYPES:
            return {
                ...state,
                bataTypes:payload.bataTypes
            };
        default:
            return state;
    }
}