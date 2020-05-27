import { TARGET_CHANGE_TYPE, TARGET_CHANGE_REP, TARGET_CHANGE_MAIN_VALUE, TARGET_CHANGE_MAIN_QTY, TARGET_DATA_LOADED, TARGET_CHANGE_ITEM_TARGET, TARGET_CLEAR_PAGE, TARGET_CHANGE_DATE, TARGET_MENU_OPEN, TARGET_MENU_CLOSE } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeType = type=>({
    type:TARGET_CHANGE_TYPE,
    payload:{type}
});

export const changeMainValue = value=>({
    type:TARGET_CHANGE_MAIN_VALUE,
    payload:{value}
});

export const changeMainQty = qty=>({
    type:TARGET_CHANGE_MAIN_QTY,
    payload:{qty}
});

export const dataLoaded = (value,qty,brands,products,principals)=>({
    type:TARGET_DATA_LOADED,
    payload:{value,qty,brands,products,principals}
})

export const fetchData = (rep,month)=>dispatch=>{
    agent.Target.load(rep,month).then(({valueTarget,qtyTarget,brands,products,principals})=>{
        dispatch(dataLoaded(valueTarget,qtyTarget,(brands.constructor === Array)?{}:brands,(products.constructor === Array)?{}:products,(principals.constructor === Array)?{}:principals));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const changeItemTarget = (itemType,itemId,type,value,price)=>({
    type:TARGET_CHANGE_ITEM_TARGET,
    payload:{itemType,itemId,type,value,price}
});

export const clearPage = ()=>({
    type:TARGET_CLEAR_PAGE
})

export const saveForm = (rep,mainValue,mainQty,products,brands,principals,month)=>dispatch=>{
    agent.Target.save(rep,mainValue,mainQty,products,brands,principals,month).then(({success,message})=>{
        if(success)
            dispatch(alertDialog(message,'success'));
        else
            dispatch(alertDialog(message,'success'));
        dispatch(clearPage());
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const changeMonth = month=>({
    type:TARGET_CHANGE_DATE,
    payload:{month}
});

export const openTargetMenu = ref =>({
    type:TARGET_MENU_OPEN,
    payload:{ref}
});

export const closeTargetMenu = ()=>({
    type:TARGET_MENU_CLOSE
});