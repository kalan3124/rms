import { ROUTE_CHEMIST_LOADED_ROUTES, ROUTE_CHEMIST_LOADED_CHEMISTS, ROUTE_CHEMIST_CHANGE_CHEMIST_KEYWORD, ROUTE_CHEMIST_CHANGE_ROUTE_KEYWORD, ROUTE_CHEMIST_ADD_CHEMISTS, ROUTE_CHEMIST_ADD_ROUTES, ROUTE_CHEMIST_REMOVE_CHEMISTS, ROUTE_CHEMIST_REMOVE_ROUTES, ROUTE_CHEMIST_CLEAR_PAGE, ROUTE_CHEMIST_LOADED_CHECKED_CHEMISTS, ROUTE_CHEMIST_AREA_CHANGE, ROUTE_CHEMIST_TYPE_CHANGE } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const addRoute = (route) =>({ 
    type: ROUTE_CHEMIST_ADD_ROUTES,
    payload:{route}
})

export const addChemist = chemist=>({
    type: ROUTE_CHEMIST_ADD_CHEMISTS,
    payload:{chemist}
})

export const removeRoute = (route) =>({
    type: ROUTE_CHEMIST_REMOVE_ROUTES,
    payload:{route}
})

export const removeChemist = chemist=>({
    type: ROUTE_CHEMIST_REMOVE_CHEMISTS,
    payload:{chemist}
})

export const loadedRoutes = routes=>({
    type:ROUTE_CHEMIST_LOADED_ROUTES,
    payload:{routes}
})

export const changeArea = area=>({
    type: ROUTE_CHEMIST_AREA_CHANGE,
    payload:{area}
})

export const loadedChemists = chemists =>({
    type: ROUTE_CHEMIST_LOADED_CHEMISTS,
    payload: {chemists}
})

export const changeChemistKeyword = keyword =>({
    type:ROUTE_CHEMIST_CHANGE_CHEMIST_KEYWORD,
    payload:{keyword}
})

export const changeRouteKeyword = keyword =>({
    type: ROUTE_CHEMIST_CHANGE_ROUTE_KEYWORD,
    payload:{keyword}
});

export const clearPage = ()=>({
    type:ROUTE_CHEMIST_CLEAR_PAGE
})

export const fetchAreas = (type,area)=>dispatch=>{
    dispatch(changeArea(area));

    agent.RouteChemist.loadRoutesByArea(type,area).then((data)=>{
        dispatch(loadedRoutes(data));
    });

    agent.RouteChemist.loadChemistsByArea(type,area).then((data)=>{
        dispatch(loadedChemists(data));
    });
}

export const fetchRoutes = (type,area,keyword)=>dispatch=>{
    dispatch(changeRouteKeyword(keyword));

    agent.RouteChemist.loadRoutesByArea(type,area,keyword).then((data)=>{
        dispatch(loadedRoutes(data));
    });
}

export const fetchChemist = (type,area,keyword) =>dispatch=>{
    dispatch(changeChemistKeyword(keyword));

    agent.RouteChemist.loadChemistsByArea(type,area,keyword).then((data)=>{
        dispatch(loadedChemists(data));
    });
}

export const submit = (type,routes,chemists)=>dispatch=>{
    agent.RouteChemist.save(type,routes,chemists).then(({success,message})=>{
        if(success){
            dispatch(alertDialog(message,'success'));
            dispatch(clearPage())
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const loadedCheckedChemists = (chemists)=>({
    type:ROUTE_CHEMIST_LOADED_CHECKED_CHEMISTS,
    payload:{chemists}
})

export const fetchChemistsByRoute = (type,routeId) =>dispatch=>{
    agent.RouteChemist.loadChemist(type,routeId).then(data=>{
        dispatch(loadedCheckedChemists(data))
    })
}

export const changeType = type => ({
    type:ROUTE_CHEMIST_TYPE_CHANGE,
    payload:{type}
})