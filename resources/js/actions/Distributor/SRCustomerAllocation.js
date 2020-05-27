import { SR_CUSTOMER_LOADED_SRS, SR_CUSTOMER_LOADED_CUSTOMERS, SR_CUSTOMER_CHANGE_CUSTOMER_KEYWORD, SR_CUSTOMER_CHANGE_SR_KEYWORD, SR_CUSTOMER_ADD_CUSTOMERS, SR_CUSTOMER_ADD_SRS, SR_CUSTOMER_REMOVE_CUSTOMERS, SR_CUSTOMER_REMOVE_SRS, SR_CUSTOMER_CLEAR_PAGE, SR_CUSTOMER_LOADED_CHECKED_CUSTOMERS } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";
import { DISTRIBUTOR_SALES_REP_TYPE } from "../../constants/config";

export const addSr = (sr) =>({ 
    type: SR_CUSTOMER_ADD_SRS,
    payload:{sr}
})

export const addCustomer = customer=>({
    type: SR_CUSTOMER_ADD_CUSTOMERS,
    payload:{customer}
})

export const removeSr = (sr) =>({
    type: SR_CUSTOMER_REMOVE_SRS,
    payload:{sr}
})

export const removeCustomer = customer=>({
    type: SR_CUSTOMER_REMOVE_CUSTOMERS,
    payload:{customer}
})

export const loadedSrs = srs=>({
    type:SR_CUSTOMER_LOADED_SRS,
    payload:{srs}
})

export const loadedCustomers = customers =>({
    type: SR_CUSTOMER_LOADED_CUSTOMERS,
    payload: {customers}
})

export const changeCustomerKeyword = keyword =>({
    type:SR_CUSTOMER_CHANGE_CUSTOMER_KEYWORD,
    payload:{keyword}
})

export const changeSrKeyword = keyword =>({
    type: SR_CUSTOMER_CHANGE_SR_KEYWORD,
    payload:{keyword}
});

export const clearPage = ()=>({
    type:SR_CUSTOMER_CLEAR_PAGE
})

export const fetchAreas = area=>dispatch=>{
    dispatch(changeArea(area));

    agent.SrCustomer.loadSrsByArea(area).then((data)=>{
        dispatch(loadedSrs(data));
    });

    agent.SrCustomer.loadCustomersByArea(area).then((data)=>{
        dispatch(loadedCustomers(data));
    });
}


export const fetchSrs = (keyword)=>dispatch=>{
    dispatch(changeSrKeyword(keyword));

    agent.Crud.dropdown('user',keyword,{u_tp_id:DISTRIBUTOR_SALES_REP_TYPE}).then((data)=>{
        dispatch(loadedSrs(data));
    });
}

export const fetchCustomer = (keyword) =>dispatch=>{
    dispatch(changeCustomerKeyword(keyword));

    agent.Crud.dropdown('distributor_customer',keyword).then((data)=>{
        dispatch(loadedCustomers(data));
    });
}

export const submit = (srs,customers)=>dispatch=>{
    agent.SrCustomer.save(srs,customers).then(({success,message})=>{
        if(success){
            dispatch(alertDialog(message,'success'));
            dispatch(clearPage())
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const loadedCheckedCustomers = (customers)=>({
    type:SR_CUSTOMER_LOADED_CHECKED_CUSTOMERS,
    payload:{customers}
})

export const fetchCustomersBySr = srId =>dispatch=>{
    agent.SrCustomer.loadCustomer(srId).then(data=>{
        dispatch(loadedCheckedCustomers(data))
    })
}