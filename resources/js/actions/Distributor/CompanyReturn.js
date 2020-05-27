import {
    COMPANY_RETURN_CHANGE_GRN_NUMBER,
    COMPANY_RETURN_LOAD_INFO,
    COMPANY_RETURN_CHANGE_QTY,
    COMPANY_RETURN_CHANGE_SALLABLE,
    COMPANY_RETURN_CLEAR_PAGE,
    COMPANY_RETURN_CHANGE_REASON,
    COMPANY_RETURN_CHANGE_REMARK
} from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeNumber = (grnNumber)=>({
    type: COMPANY_RETURN_CHANGE_GRN_NUMBER,
    payload: {grnNumber}
});

export const loadedInfo = (lines, number)=>({
    type: COMPANY_RETURN_LOAD_INFO,
    payload: {lines, number}
});

export const fetchInfo = (grnNumber)=> dispatch =>{
    agent.CompanyReturn.load(grnNumber).then(({lines, number})=>{
        dispatch(loadedInfo(lines, number));
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}

export const changeQty = (id,qty)=>({
    type: COMPANY_RETURN_CHANGE_QTY,
    payload: {id,qty}
});

export const changeReason = (id,reason)=>({
    type: COMPANY_RETURN_CHANGE_REASON,
    payload: {id, reason}
});

export const changeSalable = (id,salable)=>({
    type: COMPANY_RETURN_CHANGE_SALLABLE,
    payload: {id, salable}
});

export const changeRemark = (remark)=>({
    type: COMPANY_RETURN_CHANGE_REMARK,
    payload: {remark}
});

export const clearPage = ()=>({
    type: COMPANY_RETURN_CLEAR_PAGE
});

export const save = (grnNumber, lines, remark)=>dispatch=>{
    agent.CompanyReturn.save(grnNumber,lines,remark).then(({success, message})=>{
        if(success){
            dispatch(alertDialog(message,"success"));
            dispatch(clearPage());
        }
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}
