import {
    GRN_CONFIRM_CHANGE_GRN_NO,
    GRN_CONFIRM_LOAD_PRODUCTS,
    GRN_CONFIRM_CLEAR_PAGE,
    GRN_CONFIRM_CHANGE_QTY
} from '../../constants/actionTypes';
import agent from '../../agent';
import { alertDialog } from '../../actions/Dialogs';

export const changeNumber = number=>({
    type: GRN_CONFIRM_CHANGE_GRN_NO,
    payload: {number}
});

export const changeQty = (id,qty)=>({
    type: GRN_CONFIRM_CHANGE_QTY,
    payload: {id,qty}
});

export const loadedProducts = (grnId,products) => ({
    type: GRN_CONFIRM_LOAD_PRODUCTS,
    payload: {grnId,products}
});

export const clearPage = ()=> ({
    type: GRN_CONFIRM_CLEAR_PAGE
});

export const fetchProducts = (grnNumber)=>dispatch=>{
    agent.GRNConfirm.fetchProducts(grnNumber).then(({products,grnId})=>{
        dispatch(loadedProducts(grnId,products))
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}

export const save = (id,lines)=>dispatch=>{
    agent.GRNConfirm.save(id,lines).then(({success,message})=>{
        if(success){
            dispatch(alertDialog(message,'success'));
            dispatch(clearPage())
        }
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    });
}