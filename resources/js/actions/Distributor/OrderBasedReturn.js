import { 
    ORDER_BASED_RETURN_CHANGE_INVOICE_NUMBER, 
    ORDER_BASED_RETURN_LOAD_INVOICE_INFO, 
    ORDER_BASED_RETURN_LOAD_BONUS_DETAILS, 
    ORDER_BASED_RETURN_CHANGE_QTY, 
    ORDER_BASED_RETURN_CLEAR_PAGE,
    ORDER_BASED_RETURN_CHANGE_BONUS_QTY,
    ORDER_BASED_RETURN_CHANGE_REASON,
    ORDER_BASED_RETURN_CHANGE_SALABLE
} from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeInvoiceNumber = invNumber =>({
    type: ORDER_BASED_RETURN_CHANGE_INVOICE_NUMBER,
    payload:{ invNumber}
});

export const loadedInvoiceInfo = (lines,bonusLines,returnNumber) =>({
    type: ORDER_BASED_RETURN_LOAD_INVOICE_INFO,
    payload: {lines,bonusLines,returnNumber}
});

export const loadedBonus = (bonusLines) =>({
    type: ORDER_BASED_RETURN_LOAD_BONUS_DETAILS,
    payload: {bonusLines}
});

export const changeQty = (id,qty)=>({
    type: ORDER_BASED_RETURN_CHANGE_QTY,
    payload: {id,qty}
});

export const changeReason = (id,reason)=>({
    type: ORDER_BASED_RETURN_CHANGE_REASON,
    payload: {id,reason}
});

export const changeSalable = (id,salable)=>({
    type: ORDER_BASED_RETURN_CHANGE_SALABLE ,
    payload: {id,salable}
});

export const changeBonusQty = (id,productId,batchId,qty) =>({
    type: ORDER_BASED_RETURN_CHANGE_BONUS_QTY,
    payload:{id,productId,batchId,qty}
});

export const clearPage = ()=>({
    type: ORDER_BASED_RETURN_CLEAR_PAGE
});

export const fetchInvoiceInfo = (invNumber)=>dispatch=>{
    agent.OrderBasedReturn.fetchInvoiceInfo(invNumber).then(({lines,bonusLines,returnNumber})=>{
        dispatch(loadedInvoiceInfo(lines,bonusLines,returnNumber))
    }).catch(({response})=>{
        dispatch(alertDialog(response.data.message,'error'));
    })
};

export const fetchBonus = (invNumber,lines)=>dispatch=>{
    agent.OrderBasedReturn.fetchBonus(invNumber,lines).then(({bonusLines})=>{
        dispatch(loadedBonus(bonusLines));
    });
};

export const save = (invNumber,lines,bonusLines)=> dispatch=> {
    agent.OrderBasedReturn.save(invNumber,lines,bonusLines).then(({success,message})=>{
        if(success){
            dispatch(alertDialog(message,'success'));
            dispatch(clearPage());
        }
    }).catch(({response})=>{
        dispatch(alertDialog(response.data.message,'error'));
    })
}