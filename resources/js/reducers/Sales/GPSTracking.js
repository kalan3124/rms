import {
    SALES_GPS_USER_CHANGE,
    SALES_GPS_DATA_LOADED,
    SALES_GPS_DATE_CHANGE,
    SALES_GPS_TIME_CHANGE
} from '../../constants/actionTypes';
import moment from 'moment';

const initialState = {
    date:moment().format("YYYY-MM-DD"),
    user:undefined,
    coordinates:[],
    currentTime:moment().unix(),
};

export default (state=initialState,action)=>{
    switch (action.type) {
        case SALES_GPS_USER_CHANGE:
            return {
                ...state,
                user:action.payload.user
            }
        case SALES_GPS_DATE_CHANGE:
            return {
                ...state,
                date:action.payload.date
            }
        case SALES_GPS_DATA_LOADED:
            return {
                ...state,
                coordinates:action.payload.coordinates,
                currentTime:action.payload.startTime,
                checkin: action.payload.checkin,
                checkout: action.payload.checkout
            };
        case SALES_GPS_TIME_CHANGE:
            return {
                ...state,
                currentTime: action.payload.currentTime
            };
        default:
            return state;
    }
}