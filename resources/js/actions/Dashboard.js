import { DASHBOARD_LOADED } from "../constants/actionTypes";
import agent from "../agent";

export const dashboardLoad =()=>(
    dispatch=>{
        agent.App.dashboard().then(items=>{
            dispatch({
                type:DASHBOARD_LOADED,
                payload:{items}
            })
        })
    }
)