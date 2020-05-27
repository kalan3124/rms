import {
    GPS_USER_CHANGE,
    GPS_DATA_LOADED,
    GPS_DATE_CHANGE,
    GPS_TIME_CHANGE,
} from '../../constants/actionTypes';
import moment from 'moment'

const initialState = {
    date:moment().format("YYYY-MM-DD"),
    user:undefined,
    coordinates:[],
    currentTime:moment().unix(),
};

export default (state=initialState,action)=>{
    switch (action.type) {
        case GPS_USER_CHANGE:
            return {
                ...state,
                user:action.payload.user
            }
        case GPS_DATE_CHANGE:
            return {
                ...state,
                date:action.payload.date
            }
        case GPS_DATA_LOADED:
            return {
                ...state,
                coordinates:action.payload.coordinates,
                currentTime:action.payload.startTime,
                checkin: action.payload.checkin,
                checkout: action.payload.checkout
            };
        case GPS_TIME_CHANGE:
            return {
                ...state,
                currentTime: action.payload.currentTime
            };
        default:
            return state;
    }
}