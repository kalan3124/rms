import { DOC_APRV_CHANGE_USER, DOC_APRV_CHANGE_TO_DATE, DOC_APRV_CHANGE_FROM_DATE, DOC_APRV_DATA_LOADED, DOC_APRV_SELECT_TO_EDIT, DOC_APRV_CHANGE_DOCTOR, DOC_APRV_REFRESH } from "../../constants/actionTypes";

const initialState = {
    user:undefined,
    toDate:undefined,
    fromDate:undefined,
    data:{},
    updatingKey:0
};


export default (state=initialState,{payload,type})=>{
    switch (type) {
    case DOC_APRV_CHANGE_USER:
        return {
            ...state,
            user:payload.user
        };
    case DOC_APRV_CHANGE_TO_DATE:
        return {
            ...state,
            toDate:payload.date
        };
    case DOC_APRV_CHANGE_FROM_DATE:
        return {
            ...state,
            fromDate:payload.date
        };
    case DOC_APRV_DATA_LOADED:
        return {
            ...state,
            data: payload.data
        };
    case DOC_APRV_SELECT_TO_EDIT:
        return {
            ...state,
            updatingKey:payload.key
        };
    case DOC_APRV_CHANGE_DOCTOR:
        return {
            ...state,
            data:{
                ...state.data,
                [payload.key]:{
                    ...state.data[payload.key],
                    ...payload.values
                }
            }
        }
    default:
        return state;
    }
}