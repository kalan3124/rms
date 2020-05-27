import {
    SALES_TARGET_CHANGE_DATE,
    SALES_TARGET_DATA_LOADED,
    SALES_TARGET_CHANGE_ITEM_TARGET,
    SALES_TARGET_CLEAR_PAGE
} from "../../constants/actionTypes";
import moment from 'moment';

const initialState = {
    type:"product",
    products:{},
    month:moment().format('YYYY-MM-DD'),
    uploadMenuRef:undefined
}

export default (state=initialState,{payload,type})=>{
    switch (type) {
        case SALES_TARGET_CHANGE_DATE:
            return {
                ...state,
                month:payload.month
            };
        case SALES_TARGET_DATA_LOADED:
            return {
                ...state,
                products:payload.products
            }
        case SALES_TARGET_CHANGE_ITEM_TARGET:
                let calculatedValue = 0;
                let otherTag = payload.type=='value'?'qty':'value';         

                if(payload.itemType=='product'){
                    if(payload.price!==0&&payload.price){
                        if(payload.type=='value'){
                            calculatedValue = (payload.value/payload.price).toFixed(2);
                        } else {
                            calculatedValue = (payload.value*payload.price).toFixed(2);
                        }
                    }
                }
            return {
                ...state,
                [payload.itemType+"s"]:{
                    ...state[payload.itemType+"s"],
                    [payload.itemId]:{
                        ...state[payload.itemType+"s"][payload.itemId],
                        [payload.type+"Target"]:payload.value,
                        [otherTag+"Target"]:calculatedValue,
                    }
                }
            }
        case SALES_TARGET_CLEAR_PAGE: 
            return initialState;
        default:
            return state;
    }
}