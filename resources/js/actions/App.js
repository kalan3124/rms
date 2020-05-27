import {
    USER_LOADING,
    USER_ACCESS,
    GUEST_ACCESS
} from '../constants/actionTypes';

import agent from '../agent';

export const loadingUser = ()=>({
    type:USER_LOADING
});

export const userAccess = user =>({
    type:USER_ACCESS,
    payload:{user,time:user.time}
})

export const guestAccess = () =>({
    type:GUEST_ACCESS
})

export const loadUser=token=>{
    axios.defaults.headers.common['Authorization'] = 'Bearer '+token;
    return dispatch=>{
        dispatch(loadingUser());
        agent.Auth.check().then(data=>{
            dispatch(userAccess(data));
        }).catch(err=>{
            delete axios.defaults.headers.common['Authorization'];
            localStorage.removeItem('userToken');
            dispatch(guestAccess())
        })
    }
}