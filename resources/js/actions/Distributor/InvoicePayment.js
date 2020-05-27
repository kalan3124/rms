import {
PAYMENT_INVOICE_NUMBER_CHANGE,
PAYMENT_INVOICE_CHANGE_DISTRIBUTOR,
PAYMENT_INVOICE_CHANGE_SALESMAN,
PAYMENT_INVOICE_CHANGE_CUSTOMER,
PAYMENT_INVOICE_CHANGE_DATA,
PAYMENT_CONFIRM_CLEAR_PAGE,
 PAYMENT_INVOICE_LOAD_DATA,
 PAYMENT_CHECKED_INVOICE,
 PAYMENT_INVOICE_PAYMENT_AMOUNT_CHANGE,
 PAYMENT_INVOICE_CHANGE_PAYMENT_TYPE,
 PAYMENT_INVOICE_CHANGE_CHEQUE_DATE,
 PAYMENT_INVOICE_CHANGE_CHEQUE_NO,
 PAYMENT_INVOICE_CHANGE_CHEQUE_BANK,
 PAYMENT_INVOICE_CHANGE_CHEQUE_BRANCH,
} from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeNumber = number=>({
    type: PAYMENT_INVOICE_NUMBER_CHANGE,
    payload: {number}
});

export const changePaymentAmount = payment=>({
    type: PAYMENT_INVOICE_PAYMENT_AMOUNT_CHANGE,
    payload: {payment}
});

export const changeDistributor = distributor=>({
    type: PAYMENT_INVOICE_CHANGE_DISTRIBUTOR,
    payload: {
        distributor
    }
});

export const changeSalesman = salesman => ({
    type: PAYMENT_INVOICE_CHANGE_SALESMAN,
    payload: {
        salesman
    }
});

export const changeCustomer = customer => ({
    type: PAYMENT_INVOICE_CHANGE_CUSTOMER,
    payload: {
        customer
    }
});

export const changePaymentType = pType => ({
    type: PAYMENT_INVOICE_CHANGE_PAYMENT_TYPE,
    payload: {
        pType
    }
});

export const changeChequeNo = c_no => ({
    type: PAYMENT_INVOICE_CHANGE_CHEQUE_NO,
    payload: {
        c_no
    }
});

export const changeChequeBank = c_bank => ({
    type: PAYMENT_INVOICE_CHANGE_CHEQUE_BANK,
    payload: {
        c_bank
    }
});

export const changeChequeBranch = c_branch => ({
    type: PAYMENT_INVOICE_CHANGE_CHEQUE_BRANCH,
    payload: {
        c_branch
    }
});

export const changeChequeDate = c_date => ({
    type: PAYMENT_INVOICE_CHANGE_CHEQUE_DATE,
    payload: {
        c_date
    }
});

export const changeCheckedInvoices = invoiceChecked=>({
    type:PAYMENT_CHECKED_INVOICE,
    payload:{invoiceChecked}
});

export const changedData = (id,in_id,date,code,in_amount,os_amount,payment_amount,payment,balance,status) => ({
    type: PAYMENT_INVOICE_CHANGE_DATA,
    payload: {
        id,in_id,date,code,in_amount,os_amount,payment_amount,payment,balance,status
    }
});

export const changeDetails = (customer,cos_amount,lines)=>({
    type: PAYMENT_INVOICE_LOAD_DATA,
    payload: {customer,cos_amount,lines}
});

export const fetchDetails  =  (number,distributor,salesman,customer)=>dispatch=>{
    agent.InvoicePayment.load(number,distributor,salesman,customer).then(({customer,cos_amount,lines})=>{
        console.log(customer);
        dispatch(changeDetails(customer,cos_amount,lines));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
        dispatch(clearPage());
    })
}

export const clearPage = ()=>({
    type: PAYMENT_CONFIRM_CLEAR_PAGE
})

export const save = (customer,payment,balance,pType,lines,c_no,c_bank,c_branch,c_date)=>dispatch=>{
    agent.InvoicePayment.save(customer,payment,balance,pType,lines,c_no,c_bank,c_branch,c_date).then(({success,message})=>{
        if(success){
            dispatch(alertDialog(message,"success"));
            dispatch(clearPage());
        } else {
            dispatch(alertDialog(message,"error"));
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
};
