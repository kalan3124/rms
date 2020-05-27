import {
    HOD_GPS_USER_CHANGE,
    HOD_GPS_DATA_LOADED,
    HOD_GPS_DATE_CHANGE,
    HOD_GPS_USER_LOADED,
    HOD_GPS_STOP_LOADED,
    HOD_GPS_CLEAR
} from '../../constants/actionTypes';
import moment from 'moment'

const initialState = {
    date: moment().format("YYYY-MM-DD"),
    user: undefined,
    coordinates: [],
    hodUsers: [],
    stop: 0
};

export default (state = initialState, action) => {
    switch (action.type) {
        case HOD_GPS_USER_CHANGE:
            return {
                ...state,
                user: action.payload.user
            }
        case HOD_GPS_DATE_CHANGE:
            return {
                ...state,
                date: action.payload.date
            }
        case HOD_GPS_DATA_LOADED:
            return {
                ...state,
                coordinates: action.payload.coordinates
            };
        case HOD_GPS_USER_LOADED:
            return {
                ...state,
                hodUsers: action.payload.hodUsers
            };
        case HOD_GPS_STOP_LOADED:
            return {
                ...state,
                stop: action.payload.stop
            };
        case HOD_GPS_CLEAR:
            return {
                ...state,
                coordinates: []
            };
        default:
            return state;
    }
}