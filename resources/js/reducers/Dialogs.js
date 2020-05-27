import {
    DIALOG_ALERT,
    DIALOG_CONFIRMATION,
    DIALOG_CLOSE_SNACK
} from '../constants/actionTypes';

const initialState = {
    alerts:[]
}

export default (state=initialState,action)=>{
    switch (action.type) {
        case DIALOG_ALERT:
            return {
                ...state,
                alerts:[
                    ...state.alerts,
                    {
                        type:action.payload.type,
                        message:action.payload.message
                    }
                ]
            }
        case DIALOG_CONFIRMATION:
            return {
                ...state,
                alerts:[
                    ...state.alerts,
                    {
                        type:'confirm',
                        message:action.payload.message,
                        onConfirm:action.payload.onConfirm,
                        onCancel:action.payload.onCancel
                    }
                ]
            }
        case DIALOG_CLOSE_SNACK:
            return {
                ...state,
                alerts:state.alerts.slice(0,action.payload.index).concat(state.alerts.slice(action.payload.index+1,state.alerts.length))
            }
        default:
            return state;
    }
}