import { DIALOG_ALERT, DIALOG_CONFIRMATION, DIALOG_CLOSE_SNACK } from "../constants/actionTypes";

export const alertDialog = (message,type)=>({
    type:DIALOG_ALERT,
    payload:{message,type}
})

export const confirmDialog = (message,onConfirm,onCancel)=>({
    type:DIALOG_CONFIRMATION,
    payload:{message,onConfirm,onCancel}
})

export const closeDialog = (index)=>({
    type:DIALOG_CLOSE_SNACK,
    payload:{index}
})