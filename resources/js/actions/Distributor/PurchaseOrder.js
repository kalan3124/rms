import {
 PURCHASE_ORDER_DISTRIBUTOR_CHANGE,
 PURCHASE_ORDER_NUMBER_CHANGE,
 PURCHASE_ORDER_PRODUCT_CHANGE,
 PURCHASE_ORDER_QTY_CHANGE,
 PURCHASE_ORDER_REMOVE_LINE,
 PURCHASE_ORDER_ADD_LINE,
 PURCHASE_ORDER_DETAILS_CHANGE,
 PURCHASE_ORDER_CLEAR_PAGE,
 PURCHASE_ORDER_DSR_CHANGE,
 PURCHASE_ORDER_SITE_CHANGE,
 PURCHASE_ORDER_LOADING
} from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeDistributor = distributor=>({
    type: PURCHASE_ORDER_DISTRIBUTOR_CHANGE,
    payload: {distributor}
});

export const changeNumber = number=>({
    type: PURCHASE_ORDER_NUMBER_CHANGE,
    payload: {number}
});

export const fetchNumber = distributor => dispatch=>{
    agent.PurchaseOrder.getPurchaseNumber(distributor).then(({number})=>{
        dispatch(changeNumber(number));
    });
};

export const changeProduct = (number,product)=>({
    type: PURCHASE_ORDER_PRODUCT_CHANGE,
    payload:{number,product}
});

export const changeQty = (number,qty)=>({
    type: PURCHASE_ORDER_QTY_CHANGE,
    payload: {number,qty}
});

export const removeLine = number=>({
    type: PURCHASE_ORDER_REMOVE_LINE,
    payload: {number}
});

export const changeDetails = (number,price,packSize,code,stockInHand,stockPending)=>({
    type: PURCHASE_ORDER_DETAILS_CHANGE,
    payload: {number,price,packSize,code,stockInHand,stockPending}
});


export const fetchProductDetails = (number,product,distributor)=>dispatch=>{
    agent.PurchaseOrder.getDetails(product,distributor).then(({price,pack_size,code,stockInHand,stockPending})=>{
        dispatch(changeDetails(number,price,pack_size,code,stockInHand,stockPending));
    })
}

export const addLine = ()=>({
    type: PURCHASE_ORDER_ADD_LINE
})

export const clearPage = ()=>({
    type: PURCHASE_ORDER_CLEAR_PAGE
})

export const save = (distributor,dsr,site,lines)=>dispatch=>{
    dispatch(loading(true));
    agent.PurchaseOrder.save(distributor,dsr,site,lines).then(({success,message})=>{

        if(success){
            dispatch(alertDialog(message,"success"));
            dispatch(clearPage());
        } else {
            dispatch(alertDialog(message,"error"));
        }
    }).catch(err=>{
        dispatch(loading(false));
        dispatch(alertDialog(err.response.data.message,'error'));
    })
};

export const changeSalesRep = dsr => ({
    type: PURCHASE_ORDER_DSR_CHANGE,
    payload: {
        dsr
    }
});

export const changeSite = site => ({
    type: PURCHASE_ORDER_SITE_CHANGE,
    payload: {
        site
    }
});

export const loading = (clicked)=> ({
    type: PURCHASE_ORDER_LOADING,
    payload: {
        clicked
    }
})
