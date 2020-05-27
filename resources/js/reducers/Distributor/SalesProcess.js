import { SALES_DATA_PROCESS_CHANGE_MONTH, SALES_DATA_PROCESS_CHANGE_PERCENTAGE } from "../../constants/actionTypes";

const initialState = {
    percentage: undefined,
    month:null,
    message: "Please wait starting your process!",
    status: "running"
};

export default (state=initialState,action)=>{
    switch (action.type) {
        case SALES_DATA_PROCESS_CHANGE_MONTH:
            return {
                ...state,
                month: action.payload.month
            };
        case SALES_DATA_PROCESS_CHANGE_PERCENTAGE:
            return {
                ...state,
                percentage: action.payload.percentage,
                message: action.payload.message,
                status: action.payload.status,
            };
        default:
            return state
    }
}