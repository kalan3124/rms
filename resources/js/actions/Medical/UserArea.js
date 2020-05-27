import {
    USER_AREA_TAB_CHANGED,
    USER_AREA_TERRITORY_LEVELS_LOADED,
    USER_AREA_TERRITORIES_LOADED,
    USER_AREA_TERRITORY_NAME_CHANGE,
    USER_AREA_USER_NAME_CHANGE,
    USER_AREA_USERS_LOADED,
    USER_AREA_USER_MENU_OPEN,
    USER_AREA_USER_MENU_CLOSE,
    USER_AREA_USER_SELECT,
    USER_AREA_USER_TAB_CHANGED,
    USER_AREA_LOAD_INFORMATION,
    USER_AREA_CHANGE_AREA
} from "../../constants/actionTypes";
import agent from "../../agent";
import {
    USER_AREAS_TERRITORY_SEARCH, USER_AREAS_USER_SEARCH
} from "../../constants/debounceTypes";
import { alertDialog, confirmDialog } from "../Dialogs";
import { changeUser } from "./UserAllocation";

export const changeTab = tab => ({
    type: USER_AREA_TAB_CHANGED,
    payload: {
        tab
    }
});

export const loadedTerritoryLevels = territoryLevels => ({
    type: USER_AREA_TERRITORY_LEVELS_LOADED,
    payload: {
        territoryLevels
    }
});

export const fetchTerritoryLevels = () => dispatch => {
    agent.UserArea.territoryLevels().then(territoryLevels => {
        dispatch(loadedTerritoryLevels(territoryLevels));
        dispatch(fetchTerritories(territoryLevels[0].link))
    })
};

export const loadedTerritories = territories => ({
    type: USER_AREA_TERRITORIES_LOADED,
    payload: {
        territories
    }
});

export const changeTerritoryName = territoryName => ({
    type: USER_AREA_TERRITORY_NAME_CHANGE,
    payload: {
        territoryName
    }
})

export const fetchTerritories = (type, keyword) => dispatch => {
    dispatch(changeTerritoryName(keyword));

    let thunk = dispatch => {
        agent.Crud.dropdown(type, keyword).then(territories => {
            dispatch(loadedTerritories(territories));
        })
    }

    thunk.meta = {
        debounce: {
            time: 500,
            key: USER_AREAS_TERRITORY_SEARCH
        }
    };

    dispatch(thunk)
}

export const changeUserName = userName =>({
    type:USER_AREA_USER_NAME_CHANGE,
    payload:{userName}
});

export const loadedUsers = users=>({
    type:USER_AREA_USERS_LOADED,
    payload:{users}
});

export const fetchUsers = keyword=>dispatch=>{
    dispatch(changeUserName(keyword));

    let thunk = dispatch=>{
        agent.Crud.dropdown('user',keyword).then(users=>{
            dispatch(loadedUsers(users))
        })
    }

    thunk.meta = {
        debounce: {
            time: 500,
            key: USER_AREAS_USER_SEARCH
        }
    }

    dispatch(thunk);
}

export const openUserMenu = userMenuRef=>({
    type:USER_AREA_USER_MENU_OPEN,
    payload:{userMenuRef}
});

export const closeUserMenu = ()=>({
    type:USER_AREA_USER_MENU_CLOSE
})

export const changeUserTab = tab=>({
    type:USER_AREA_USER_TAB_CHANGED,
    payload:{tab}
})

export const loadInformations = (areas,levels)=>({
    type:USER_AREA_LOAD_INFORMATION,
    payload:{areas,levels}
})

export const fetchInformations = user=>dispatch=>{
    dispatch(changeUser(user,'user_area'));
    dispatch(changeUserName(user.label));

    agent.UserArea.userInfo(user).then(({areas,levels})=>{
        dispatch(loadInformations(areas,levels));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const addArea = (user,area)=>dispatch=>{
    if(typeof user=='undefined'){
        dispatch(alertDialog("Please select a user to assign areas.","error"));
        return;
    }
    agent.UserArea.create(user,area).then(({success,message})=>{
        if(success)
            dispatch(fetchInformations(user));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const removeArea = (user,area)=>dispatch=>{
    if(typeof user=='undefined'){
        dispatch(alertDialog("Please select a user to remove areas.","error"));
        return;
    }
    agent.UserArea.remove(user,area).then(({success,message})=>{
        if(success){
            dispatch(fetchInformations(user));
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const removeAll = (user)=>dispatch =>{
    dispatch(confirmDialog("You can not recover again deleted allocations. Are you sure you want to delete all area allocations from the "+user.label+"? ",()=>{
        agent.UserArea.removeAll(user).then(({success,message})=>{
            if(success){
                dispatch(alertDialog(message,"success"));
                dispatch(fetchInformations(user));
            }
        }).catch(err=>{
            dispatch(alertDialog(err.response.data.message,'error'));
        })
    }))
}