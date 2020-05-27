import { DOCTOR_TOWNS_DOCTORS_LOADED, DOCTOR_TOWNS_TOWNS_LOADED, DOCTOR_TOWNS_SELECT_DOCTORS, DOCTOR_TOWNS_SELECT_TOWNS, DCOTOR_TOWNS_APPEND_TOWNS, DOCTOR_TOWNS_CLEAR_SELECTIONS } from "../../constants/actionTypes";

const initialState = {
    selectedDoctors:{},
    selectedTowns:{},
    towns:{},
    doctors:{}
}

export default (state=initialState,{type,payload})=>{
    switch (type) {
        case DOCTOR_TOWNS_DOCTORS_LOADED:
            return {
                ...state,
                doctors:payload.doctors.mapToObject('value'),
            };
        case DOCTOR_TOWNS_TOWNS_LOADED:
            return {
                ...state,
                towns:payload.towns.mapToObject('value')
            };
        case DOCTOR_TOWNS_SELECT_TOWNS:
            return {
                ...state,
                selectedTowns:{
                    ...state.selectedTowns,
                    [payload.town.value]: state.selectedTowns[payload.town.value]?undefined: payload.town
                }
            }
        case DOCTOR_TOWNS_SELECT_DOCTORS:
            return {
                ...state,
                selectedDoctors:{
                    ...state.selectedDoctors,
                    [payload.doctor.value]:state.selectedDoctors[payload.doctor.value]?undefined: payload.doctor
                }
            };
        case DCOTOR_TOWNS_APPEND_TOWNS:
            return {
                ...state,
                selectedTowns:{
                    ...state.selectedTowns,
                    ...payload.towns.mapToObject('value')
                }
            };
        case DOCTOR_TOWNS_CLEAR_SELECTIONS:
            return {
                ...state,
                selectedDoctors:{},
                selectedTowns:{}
            };
        default:
            return state;
    }
}