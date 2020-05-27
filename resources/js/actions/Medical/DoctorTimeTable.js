import {
    DOC_TIME_TABLE_DOC_CHANGE,
    DOC_TIME_TABLE_MODAL_OPEN,
    DOC_TIME_TABLE_NEW_VALUES_EDIT,
    DOC_TIME_TABLE_MODAL_CLOSE,
    DOC_TIME_TABLE_SHEDULES_CHANGE
} from "../../constants/actionTypes";
import agent from "../../agent";
import {
    alertDialog
} from "../Dialogs";
import moment from 'moment';

export const doctorChange = doctor => ({
    type: DOC_TIME_TABLE_DOC_CHANGE,
    payload: {
        doctor
    }
})

export const addTime = () => ({
    type: DOC_TIME_TABLE_MODAL_OPEN
})

export const editNewValues = newValues => ({
    type: DOC_TIME_TABLE_NEW_VALUES_EDIT,
    payload: {
        newValues
    }
})

export const modalClose = () => ({
    type: DOC_TIME_TABLE_MODAL_CLOSE
})

export const changeShedules = (shedules, lastId) => ({
    type: DOC_TIME_TABLE_SHEDULES_CHANGE,
    payload: {
        shedules,
        lastId
    }
})

export const fetchTimeTable = doc => dispatch => {
    dispatch(doctorChange(doc));
    if (!doc) return;

    agent.TimeTable.load(doc).then(({
        shedules,
        count
    }) => {

        let modedShedules = {
            ...shedules
        };
        let today = new Date();
        let todayString = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();

        Object.keys(modedShedules).forEach(dayName => {
            modedShedules[dayName] = modedShedules[dayName].map(shedule => {

                let modedShedule = {
                    ...shedule
                };

                modedShedule.startTime = moment(todayString + ' ' + modedShedule.startTime.substr(0, 5), 'YYYY-MM-DD HH:mm:')
                modedShedule.endTime = moment(todayString + ' ' + modedShedule.endTime.substr(0, 5), 'YYYY-MM-DD HH:mm:');

                return modedShedule;
            })
        })

        dispatch(changeShedules(modedShedules, count))
    }).catch(err => {
        dispatch(alertDialog("Can not find a time table for the selected doctor. You want to set one. Unless the doctor is not apearing in the android app.", "warning"))
    })
}

export const saveTimeTable = (doc,shedules) => dispatch => {
    if (!doc) {
        dispatch(alertDialog("Please select a doctor to continue!","warning"));
        return;
    }
    agent.TimeTable.save(doc,shedules).then(({message})=>{
        dispatch(alertDialog("Successfully saved the time table","success"));
        dispatch(fetchTimeTable(doc));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'))
    })
}
