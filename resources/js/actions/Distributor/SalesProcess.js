import { SALES_DATA_PROCESS_CHANGE_PERCENTAGE, SALES_DATA_PROCESS_CHANGE_MONTH } from "../../constants/actionTypes";
import agent from "../../agent";

export const changePercentage = (percentage,status,message)=>({
    type: SALES_DATA_PROCESS_CHANGE_PERCENTAGE,
    payload: {percentage,status,message}
});

export const changeMonth = month=>({
    type: SALES_DATA_PROCESS_CHANGE_MONTH,
    payload: {month}
});

export const submit = month => dispatch=>{
    agent.SalesProcess.submit(month);

    dispatch(fetchPercentage());
};

export const fetchPercentage = ()=>dispatch=>{
    window.setTimeout(()=>{
        agent.SalesProcess.fetchPercentage().then(({percentage,status,message})=>{
            dispatch(changePercentage(percentage,status,message));

            if(percentage!=100&&status=='running')
                dispatch(fetchPercentage());
        });
        
    },1500);
}