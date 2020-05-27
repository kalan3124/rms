import { UPLOAD_CSV_CHANGE_TYPE, UPLOAD_CSV_CHANGE_INFO, UPLOAD_CSV_CHANGE_FILE, UPLOAD_CSV_SUBMITED, UPLOAD_CSV_CLEAR_PAGE, UPLOAD_CSV_CHANGE_STATUS } from "../constants/actionTypes";
import agent from "../agent";
import { APP_URL } from "../constants/config";

export const changeType=(name,formName)=>({
    type:UPLOAD_CSV_CHANGE_TYPE,
    payload:{name,formName}
});

export const changeInfo = (title,tips)=>({
    type:UPLOAD_CSV_CHANGE_INFO,
    payload:{title,tips}
});

export const getParams = (name,formName)=>({name:typeof name=='undefined'?formName:name,type:typeof name=='undefined'?1:2});

export const fetchInfo = (name,formName)=>dispatch=>{
    dispatch(changeType(name,formName));

    agent.UploadCSV.info(getParams(name,formName)).then(({success,title,tips})=>{
        if(success)
            dispatch(changeInfo(title,tips))
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'))
    });
};

export const downloadFormat = (name,formName)=>{
    const params = getParams(name,formName);
    agent.UploadCSV.generateFormat(params).then(({success})=>{
        if(success){
            window.open(APP_URL+"/storage/csv_formats/"+params.name+".csv","_blank");
        }
    });
};

export const changeUploadedFile = uploadedFile=>({
    type:UPLOAD_CSV_CHANGE_FILE,
    payload:{uploadedFile}
})

export const submited = (totalLines,message)=>({
    type:UPLOAD_CSV_SUBMITED,
    payload:{totalLines,message}
})

export const clearPage = ()=>({
    type:UPLOAD_CSV_CLEAR_PAGE
})

export const submitFile = (uploadedFile,name,formName)=>dispatch=>{
    dispatch(changeUploadedFile(uploadedFile));
    const params = getParams(name,formName);

    agent.UploadCSV.submit(params.type,params.name,uploadedFile).then(({success,message,lines})=>{
        if(success){
            dispatch(submited(lines,message));
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const changeStatus = (message,currentLine,status,timeout)=>({
    type:UPLOAD_CSV_CHANGE_STATUS,
    payload:{message,currentLine,status,timeout}
})

export const fetchStatus = (timeout)=>dispatch=>{
    agent.UploadCSV.status().then(({status,message,currentLine})=>{
        dispatch(changeStatus(message,currentLine,status,timeout))
    })
}