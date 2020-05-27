import {
    LOGIN_ERROR,
    LOGIN_FORM_LOADING,
    LOGIN_PASSWORD_CHANGE,
    LOGIN_REMEMBER_CHANGE,
    LOGIN_USERNAME_CHANGE,
    LOGIN_SUCCESS,
    LOGIN_PASSWORD_EXPIRED,
    LOGIN_NEW_PASSWORD_CHANGE
} from '../constants/actionTypes';

const initialState = {
    error: false,
    success: false,
    message: '',
    password: '',
    newPassword:'',
    username: '',
    passwordError: undefined,
    usernameError: undefined,
    remember: false,
    loading: false,
    status:false
}

export default (state = initialState, action) => {
    switch (action.type) {
        case LOGIN_FORM_LOADING:
            return {
                ...state,
                loading: true,
                message: 'Please Wait A While.. Proccessing your login request..'
            }
        case LOGIN_ERROR:
            return {
                ...state,
                loading: false,
                error: true,
                success: false,
                passwordError: (typeof action.payload.errors != 'undefined' && typeof action.payload.errors.password != 'undefined') ? action.payload.errors.password[0] : undefined,
                usernameError: (typeof action.payload.errors != 'undefined' && typeof action.payload.errors.username != 'undefined') ? action.payload.errors.username[0] : undefined,
                message: action.payload.message
            }
        case LOGIN_SUCCESS:
            return {
                ...state,
                loading: false,
                error: false,
                success: true,
                message: action.payload.message,
                passwordError: undefined,
                usernameError: undefined
            }

        case LOGIN_USERNAME_CHANGE:
            return {
                ...state,
                loading: false,
                error: false,
                success: false,
                message: '',
                usernameError: undefined,
                username:action.payload.username
            }
        case LOGIN_PASSWORD_CHANGE:
            return {
                ...state,
                loading: false,
                error: false,
                success: false,
                message: '',
                passwordError: undefined,
                password:action.payload.password
            }
        case LOGIN_NEW_PASSWORD_CHANGE:
            return {
                ...state,
                loading: false,
                error: false,
                success: false,
                message: '',
                passwordError: undefined,
                newPassword:action.payload.newPassword
            }
        case LOGIN_REMEMBER_CHANGE:
            return {
                ...state,
                loading: false,
                error: false,
                success: false,
                message: '',
                remember:action.payload.remember
            }
        case LOGIN_PASSWORD_EXPIRED:
            return {
                ...state,
                status:action.payload.status
            }
        default:
            return state;
    }
}