import { SIDEBAR_LOADED, SIDEBAR_LOADING } from "../constants/actionTypes";
import agent from "../agent";

export const sidebarLoading = ()=>({
    type:SIDEBAR_LOADING
})

export const sidebarLoaded = items=>({
    type:SIDEBAR_LOADED,
    payload:{items}
})

export const loadSidebar = ()=>(
    dispatch=>{
        dispatch(sidebarLoading());
        agent.App.sidebar().then(items=>{
            dispatch(sidebarLoaded(items))
            document.getElementById('stage').setAttribute('style','display:none');
        }).catch(err=>{
            delete axios.defaults.headers.common['Authorization'];
            localStorage.removeItem('userToken');
            window.location.reload();
        })
    }
)