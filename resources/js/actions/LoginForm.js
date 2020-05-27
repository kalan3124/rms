import { LOGIN_PASSWORD_CHANGE, LOGIN_USERNAME_CHANGE, LOGIN_REMEMBER_CHANGE, LOGIN_FORM_LOADING, LOGIN_ERROR, LOGIN_SUCCESS,LOGIN_PASSWORD_EXPIRED,LOGIN_NEW_PASSWORD_CHANGE } from "../constants/actionTypes";
import agent from '../agent';
import { alertDialog } from "../actions/Dialogs";

export const passwordChange = password => ({
    type: LOGIN_PASSWORD_CHANGE,
    payload: { password }
})

export const newPasswordChange = newPassword => ({
    type: LOGIN_NEW_PASSWORD_CHANGE,
    payload: { newPassword }
})

export const usernameChange = username => ({
    type: LOGIN_USERNAME_CHANGE,
    payload: { username }
})

export const rememberChange = remember => ({
    type: LOGIN_REMEMBER_CHANGE,
    payload: { remember }
})

export const loading = () => ({
    type: LOGIN_FORM_LOADING
})

export const loginError = payload => ({
    type: LOGIN_ERROR,
    payload
})

export const passwordExpired = status => ({
    type: LOGIN_PASSWORD_EXPIRED,
    payload: { status }
})

export const loginSuccess = (token, message,status) => {
    window.localStorage.setItem('userToken', token);

    return {
        type: LOGIN_SUCCESS,
        payload: { message,status }
    }
}

export const attemptLogin = (username, password, remember = false,newPassword) => (
    dispatch => {
        dispatch(loading());
        agent.Auth.login(username, password, remember,newPassword).then(data => {
            if (data.success) {
                dispatch(loginSuccess(data.token, data.message));                
            } else {
                dispatch(loginError(data))
                dispatch(passwordExpired(data.status))
            }
        }).catch(err => {
            dispatch(loginError({message: "Can not connect to our servers from you browser" }))
        })
    }
)