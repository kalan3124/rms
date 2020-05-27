import {
    HOD_GPS_USER_CHANGE,
    HOD_GPS_DATA_LOADED,
    HOD_GPS_DATE_CHANGE,
    HOD_GPS_USER_LOADED,
    HOD_GPS_STOP_LOADED,
    HOD_GPS_CLEAR
} from '../../constants/actionTypes';
import {
    alertDialog
} from '../Dialogs';
import agent from '../../agent';
import {
    APP_URL
} from '../../constants/config';

export const changeUser = user => ({
    type: HOD_GPS_USER_CHANGE,
    payload: {
        user
    }
})

export const changeDate = date => ({
    type: HOD_GPS_DATE_CHANGE,
    payload: {
        date
    }
})

export const dataLoaded = (coordinates) => ({
    type: HOD_GPS_DATA_LOADED,
    payload: {
        coordinates
    }
})

export const loadUser = (hodUsers) => ({
    type: HOD_GPS_USER_LOADED,
    payload: {
        hodUsers
    }
})

export const loadStop = (stop) => ({
    type: HOD_GPS_STOP_LOADED,
    payload: {
        stop
    }
})

export const clearPage = () => ({
    type: HOD_GPS_CLEAR
})

export const alert = (msg) => dispatch => {
    dispatch(alertDialog(msg, "error"))
}

export const search = (user, date) => dispatch => {

    agent.HodGps.search(user, date).then(({
        coordinates,
        hodUsers
    }) => {
        dispatch(dataLoaded(coordinates));
        dispatch(loadUser(hodUsers));
    }).catch(err => {
        console.error(err);
        dispatch(alertDialog(err.response.data.message, "error"));
    })
};