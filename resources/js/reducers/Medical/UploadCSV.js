import { UPLOAD_CSV_CHANGE_TYPE, UPLOAD_CSV_CHANGE_INFO, UPLOAD_CSV_CHANGE_FILE, UPLOAD_CSV_SUBMITED, UPLOAD_CSV_CHANGE_STATUS, UPLOAD_CSV_CLEAR_PAGE } from "../../constants/actionTypes";

const initialState = {
    name: undefined,
    formName:undefined,
    title:"",
    submited:false,
    uploadedFile:"",
    message:"Please wait!",
    totalLines:0,
    currentLine:0,
    status:"",
    timeout:undefined,
    tips:[]
};

export default (state=initialState,{type,payload})=>{
    switch (type) {
    case UPLOAD_CSV_CHANGE_TYPE:
        return {
            ...state,
            name:payload.name,
            formName: payload.formName,
            submited:false,
            uploadedFile:"",
            message:"",
            totalLines:0,
            currentLine:0,
            status:""
        };
    case UPLOAD_CSV_CHANGE_INFO:
        return {
            ...state,
            title: payload.title,
            tips:payload.tips
        };
    case UPLOAD_CSV_CHANGE_FILE:
        return {
            ...state,
            uploadedFile:payload.uploadedFile
        }
    case UPLOAD_CSV_SUBMITED:
        return {
            ...state,
            message:payload.message,
            totalLines:payload.totalLines,
            submited:true,
            currentLine:0
        }
    case UPLOAD_CSV_CHANGE_STATUS:
        return {
            ...state,
            message:payload.message,
            currentLine:payload.currentLine,
            status:payload.status,
            timeout:payload.timeout
        }
    case UPLOAD_CSV_CLEAR_PAGE:
        return {
            ...state,
            submited:false,
            uploadedFile:"",
            message:"",
            totalLines:0,
            currentLine:0,
            status:""
        };
    default:
        return state;
    }
}