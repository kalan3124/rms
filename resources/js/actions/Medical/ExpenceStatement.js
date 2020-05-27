import { EXP_STMNT_LOAD_TYPES, EXP_STMNT_CHANGE_VALUE, EXP_STMNT_LOAD_RESULTS, EXP_STMNT_LOAD_BATA_TYPES, EXP_STMNT_LOAD_BATA_CAT } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const loadedTypes = types=>({
    type:EXP_STMNT_LOAD_TYPES,
    payload:{types}
});

export const loadBataCats = categories=>({
    type:EXP_STMNT_LOAD_BATA_CAT,
    payload:{categories}
})

export const fetchTypes = ()=>dispatch=>{
    agent.ExpenceStatement.types().then(({success,reasons,bataTypes,bataCategory})=>{
        dispatch(loadedBataTypes(bataTypes));
        dispatch(loadBataCats(bataCategory))
        dispatch(loadedTypes(reasons.mapToObject('value')));
    })
};

export const changeValue = (name,value)=>({
    type:EXP_STMNT_CHANGE_VALUE,
    payload:{
        name,value
    }
})

export const loadedData = (results,count,sum_addtional,sum_private,day_mileage_limit)=>({
    type:EXP_STMNT_LOAD_RESULTS,
    payload:{results,count,sum_addtional,sum_private,day_mileage_limit}
});

export const fetchData = (values)=>dispatch=>{
    agent.ExpenceStatement.search(values).then(({results,count,sum_addtional,sum_private,day_mileage_limit})=>{
        dispatch(loadedData(results,count,sum_addtional,sum_private,day_mileage_limit));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const loadedBataTypes = bataTypes=>({
    type:EXP_STMNT_LOAD_BATA_TYPES,
    payload:{bataTypes}
});