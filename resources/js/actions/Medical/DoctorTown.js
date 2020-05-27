import { DOCTOR_TOWNS_DOCTORS_LOADED, DOCTOR_TOWNS_TOWNS_LOADED, DOCTOR_TOWNS_SELECT_DOCTORS, DOCTOR_TOWNS_SELECT_TOWNS, DCOTOR_TOWNS_APPEND_TOWNS, DOCTOR_TOWNS_CLEAR_SELECTIONS } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const loadedDoctors = doctors=>({
    type:DOCTOR_TOWNS_DOCTORS_LOADED,
    payload:{doctors}
});

export const loadedTowns = towns=>({
    type:DOCTOR_TOWNS_TOWNS_LOADED,
    payload:{towns}
});

export const selectTown = town=>({
    type:DOCTOR_TOWNS_SELECT_TOWNS,
    payload:{town}
});

export const selectDoctor = doctor=>({
    type:DOCTOR_TOWNS_SELECT_DOCTORS,
    payload:{doctor}
});

export const fetchDoctors = (keyword)=>dispatch=>{
    agent.Crud.dropdown('doctor',keyword).then((items)=>{
        dispatch(loadedDoctors(items));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    });
}

export const fetchTowns = (keyword)=>dispatch=>{
    agent.Crud.dropdown('sub_town',keyword).then((items)=>{
        dispatch(loadedTowns(items));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    });
}

export const loadedTownsByDoctor  = towns=>({
    type:DCOTOR_TOWNS_APPEND_TOWNS,
    payload:{towns}
});

export const fetchTownsByDoctor = doctor=>dispatch=>{
    agent.DoctorTown.getTownsByDoctor(doctor).then(({towns})=>{
        dispatch(loadedTownsByDoctor(towns));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
};

export const clearSelections = ()=>({
    type:DOCTOR_TOWNS_CLEAR_SELECTIONS
});

export const saveDoctorsAndTowns = (doctors,towns)=>dispatch=>{
    agent.DoctorTown.save(doctors,towns).then(({success,message})=>{
        if(success){
            dispatch(alertDialog(message,'success'));
            dispatch(clearSelections());
        } else {
            dispatch(alertDialog(message,'error'));
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}