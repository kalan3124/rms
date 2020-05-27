import { 
    DOC_TIME_TABLE_DOC_CHANGE,
    DOC_TIME_TABLE_MODAL_OPEN,
    DOC_TIME_TABLE_NEW_VALUES_EDIT,
    DOC_TIME_TABLE_MODAL_CLOSE,
    DOC_TIME_TABLE_SHEDULES_CHANGE
} from "../../constants/actionTypes";

const emptyShedules = {
    sunday: [],
    monday: [],
    tuesday: [],
    thursday: [],
    wednsday: [],
    friday: [],
    saturday: []
}

const initialState = {
    doctor:undefined,
    popupOpen:false,
    newValues:{},
    shedules:emptyShedules,
    lastId:0
}

export default (state=initialState,action)=>{
    switch (action.type) {
        case DOC_TIME_TABLE_DOC_CHANGE:
            return {
                ...state,
                doctor:action.payload.doctor
            }
        case DOC_TIME_TABLE_MODAL_OPEN:
            return {
                ...state,
                popupOpen:true
            }
        case DOC_TIME_TABLE_NEW_VALUES_EDIT:
            return {
                ...state,
                newValues:action.payload.newValues
            }
        case DOC_TIME_TABLE_MODAL_CLOSE:
            return {
                ...state,
                popupOpen:false,
                newValues:{}
            }
        case DOC_TIME_TABLE_SHEDULES_CHANGE:
            return {
                ...state,
                shedules:{...emptyShedules,...action.payload.shedules},
                lastId:action.payload.lastId,
                popupOpen:false,
                newValues:{}
            }
        default:
            return state;
    }
}