import { PERMISSION_TYPE_LOADED, PERMISSION_EXPAND_PANEL, PERMISSION_CHANGE_VALUES, PERMISSION_CHANGE_TAB, PERMISSION_CHANGE_KEYWORD, PERMISSION_RESULTS_LOADED, PERMISSION_SELECT_ITEMS, PERMISSION_CLEAR_PAGE } from "../constants/actionTypes";
import agent from "../agent";
import { SEARCHING_RECORDS } from "../constants/debounceTypes";
import { alertDialog } from "./Dialogs";

export const loadedPermissions = permissions=>({
    type:PERMISSION_TYPE_LOADED,
    payload:{permissions}
});

export const fetchPermissions =()=> dispatch=>{
    agent.Permission.load().then(permissions=>{
        dispatch(loadedPermissions(permissions));
    })
};

export const expandPanel = expanded=>({
    type:PERMISSION_EXPAND_PANEL,
    payload:{expanded}
});

export const changeValues = permissionValues=>({
    type:PERMISSION_CHANGE_VALUES,
    payload:{permissionValues}
});

export const changeTab = tab=>({
    type:PERMISSION_CHANGE_TAB,
    payload:{tab}
});

export const changeKeyword = keyword=>({
    type:PERMISSION_CHANGE_KEYWORD,
    payload:{keyword}
});

export const resultsLoaded = results=>({
    type:PERMISSION_RESULTS_LOADED,
    payload:{results}
})

export const changeSelectedUsers = (users,permissionGroups)=>({
    type:PERMISSION_SELECT_ITEMS,
    payload: {permissionGroups,users}
})

export const clearPage = ()=>({
    type:PERMISSION_CLEAR_PAGE
})

export const fetchResults= (type,keyword,debounce)=>{
    let thunk = dispatch=>{
        agent.Crud.dropdown(type,keyword).then(results=>{
            dispatch(resultsLoaded(results));
        })
    };

    if(debounce)thunk.meta = {
        debounce: {
            time: 300,
            key:SEARCHING_RECORDS
        }
    }

    return thunk;
}

export const save = (users,permissionGroups,permissionValues)=>dispatch=>{
    agent.Permission.save(users,permissionGroups,permissionValues).then(data=>{
        if(data.success){
            dispatch(clearPage());
            dispatch(alertDialog(data.message,"success"));
        }
    }).catch(({response})=>{
        dispatch(alertDialog(response.data.message,'error'));
    })
}

export const loadByUser = (user,type)=>dispatch=>{
    agent.Permission.loadByUser(user,type).then(permissions=>dispatch(changeValues(permissions)))
}