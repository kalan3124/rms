import { TARGET_CHANGE_TYPE, TARGET_CHANGE_REP, TARGET_CHANGE_MAIN_VALUE, TARGET_CHANGE_MAIN_QTY, TARGET_DATA_LOADED, TARGET_CHANGE_ITEM_TARGET, TARGET_CLEAR_PAGE, TARGET_CHANGE_DATE, TARGET_MENU_CLOSE, TARGET_MENU_OPEN } from "../../constants/actionTypes";
import moment from 'moment';

const initialState = {
    type:"product",
    mainValue:0.00,
    mainQty:0,
    products:{},
    brands:{},
    principals:{},
    month:moment().format('YYYY-MM-DD'),
    uploadMenuRef:undefined
};

export default (state=initialState,{payload,type})=>{
    switch (type) {
        case TARGET_CHANGE_TYPE:
            return {
                ...state,
                type:payload.type
            };
        case TARGET_CHANGE_MAIN_VALUE:
            return {
                ...state,
                mainValue: payload.value,
            };
        case TARGET_CHANGE_MAIN_QTY:
            return {
                ...state,
                mainQty: payload.qty
            };
        case TARGET_DATA_LOADED:
            return {
                ...state,
                mainQty:payload.qty,
                mainValue:payload.value,
                products:payload.products,
                brands:payload.brands,
                principals:payload.principals
            }
        case TARGET_CHANGE_ITEM_TARGET:
            let calculatedValue = 0;
            let otherTag = payload.type=='value'?'qty':'value';         

            if(payload.itemType=='product'){
                if(payload.price!==0&&payload.price){
                    if(payload.type=='value'){
                        calculatedValue = Math.round(payload.value/payload.price);
                    } else {
                        calculatedValue = payload.value*payload.price;
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
        case TARGET_CLEAR_PAGE:
            return initialState;
        case TARGET_CHANGE_DATE:
            return {
                ...state,
                month:payload.month
            };
        case TARGET_MENU_CLOSE:
            return {
                ...state,
                uploadMenuRef:undefined
            };
        case TARGET_MENU_OPEN:
            return {
                ...state,
                uploadMenuRef:payload.ref
            };
        default:
            return state;
    }
}