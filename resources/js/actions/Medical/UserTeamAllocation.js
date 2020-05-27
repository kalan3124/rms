import { USER_TEAM_LOADED_USERS, USER_TEAM_LOADED_TEAMS, USER_TEAM_CHANGE_TEAM_KEYWORD, USER_TEAM_CHANGE_USER_KEYWORD, USER_TEAM_ADD_TEAMS, USER_TEAM_ADD_USERS, USER_TEAM_REMOVE_TEAMS, USER_TEAM_REMOVE_USERS, USER_TEAM_CLEAR_PAGE, USER_TEAM_LOADED_CHECKED_TEAMS } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";
import { DISTRIBUTOR_SALES_REP_TYPE } from "../../constants/config";

export const addUser = (user) =>({ 
    type: USER_TEAM_ADD_USERS,
    payload:{user}
})

export const addTeam = team=>({
    type: USER_TEAM_ADD_TEAMS,
    payload:{team}
})

export const removeUser = (user) =>({
    type: USER_TEAM_REMOVE_USERS,
    payload:{user}
})

export const removeTeam = team=>({
    type: USER_TEAM_REMOVE_TEAMS,
    payload:{team}
})

export const loadedUsers = users=>({
    type:USER_TEAM_LOADED_USERS,
    payload:{users}
})

export const loadedTeams = teams =>({
    type: USER_TEAM_LOADED_TEAMS,
    payload: {teams}
})

export const changeTeamKeyword = keyword =>({
    type:USER_TEAM_CHANGE_TEAM_KEYWORD,
    payload:{keyword}
})

export const changeUserKeyword = keyword =>({
    type: USER_TEAM_CHANGE_USER_KEYWORD,
    payload:{keyword}
});

export const clearPage = ()=>({
    type:USER_TEAM_CLEAR_PAGE
})


export const fetchUsers = (keyword)=>dispatch=>{
    dispatch(changeUserKeyword(keyword));

    agent.Crud.dropdown('user',keyword).then((data)=>{
        dispatch(loadedUsers(data));
    });
}

export const fetchTeam = (keyword) =>dispatch=>{
    dispatch(changeTeamKeyword(keyword));

    agent.Crud.dropdown('team',keyword).then((data)=>{
        dispatch(loadedTeams(data));
    });
}

export const submit = (users,teams)=>dispatch=>{
    agent.UserTeam.save(users,teams).then(({success,message})=>{
        if(success){
            dispatch(alertDialog(message,'success'));
            dispatch(clearPage())
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const loadedCheckedTeams = (teams)=>({
    type:USER_TEAM_LOADED_CHECKED_TEAMS,
    payload:{teams}
})

export const fetchTeamsByUser = userId =>dispatch=>{
    agent.UserTeam.loadTeam(userId).then(data=>{
        dispatch(loadedCheckedTeams(data))
    })
}