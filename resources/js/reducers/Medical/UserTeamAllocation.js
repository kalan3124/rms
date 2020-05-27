import { USER_TEAM_CHANGE_TEAM_KEYWORD, USER_TEAM_CHANGE_USER_KEYWORD, USER_TEAM_ADD_TEAMS, USER_TEAM_ADD_USERS, USER_TEAM_LOADED_USERS, USER_TEAM_LOADED_TEAMS, USER_TEAM_REMOVE_TEAMS, USER_TEAM_REMOVE_USERS, USER_TEAM_CLEAR_PAGE, USER_TEAM_LOADED_CHECKED_TEAMS } from "../../constants/actionTypes";

const initialState = {  
    teamKeyword: "",
    userKeyword: "",
    teams:[],
    users:[],
    selectedTeams:[],
    selectedUsers:[],
    area:[]
}

export default (state=initialState,{payload,type})=>{
    switch (type) {
        case USER_TEAM_CHANGE_TEAM_KEYWORD:
            return {
                ...state,
                teamKeyword: payload.keyword
            };
        case USER_TEAM_CHANGE_USER_KEYWORD:
            return {
                ...state,
                userKeyword: payload.keyword
            };
        case USER_TEAM_LOADED_TEAMS:
            return {
                ...state,
                teams:payload.teams
            };
        case USER_TEAM_LOADED_USERS:
            return {
                ...state,
                users:payload.users 
            };
        case USER_TEAM_ADD_TEAMS:
            return {
                ...state,
                selectedTeams: [
                    ...state.selectedTeams,
                    payload.team
                ]
            };
        case USER_TEAM_ADD_USERS:
            return {
                ...state,
                selectedUsers: [
                    ...state.selectedUsers,
                    payload.user
                ]
            };
        case USER_TEAM_REMOVE_USERS:
            return {
                ...state,
                selectedUsers: state.selectedUsers.filter(user=>user.value!=payload.user.value)
            };
        case USER_TEAM_REMOVE_TEAMS:
            return {
                ...state,
                selectedTeams: state.selectedTeams.filter(team=>team.value!=payload.team.value)
            };
        case USER_TEAM_CLEAR_PAGE:
            return {
                ...state,
                selectedTeams:[],
                selectedUsers:[]
            };
        case USER_TEAM_LOADED_CHECKED_TEAMS:
            return {
                ...state,
                selectedTeams:[...payload.teams]
            };
        default:
            return state;
    }
}