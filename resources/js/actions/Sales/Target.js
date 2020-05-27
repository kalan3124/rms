import {
    SALES_TARGET_CHANGE_DATE,
    SALES_TARGET_DATA_LOADED,
    SALES_TARGET_CHANGE_ITEM_TARGET,
    SALES_TARGET_CLEAR_PAGE
} from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeMonth = month=>({
    type:SALES_TARGET_CHANGE_DATE,
    payload:{month}
});

export const dataLoaded = (products)=>({
    type:SALES_TARGET_DATA_LOADED,
    payload:{products}
})

export const fetchData = (rep,month)=>dispatch=>{
    agent.SalesTarget.load(rep,month).then(({products})=>{
        dispatch(dataLoaded((products.constructor === Array)?{}:products));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const changeItemTarget = (itemType,itemId,type,value,price)=>({
    type: SALES_TARGET_CHANGE_ITEM_TARGET,
    payload:{itemType,itemId,type,value,price}
})

export const clearPage = ()=>({
    type:SALES_TARGET_CLEAR_PAGE
})

export const saveForm = (rep,products,month)=>dispatch=>{
    agent.SalesTarget.save(rep,products,month).then(({success,message})=>{
        if(success)
            dispatch(alertDialog(message,'success'));
        else
            dispatch(alertDialog(message,'success'));
        dispatch(clearPage());
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}