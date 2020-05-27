import { USER_CHANGE_PASS_LOAD_DATA,USER_CHANGE_PASS_CHANGE_PASSWORD,USER_CHANGE_PASS_LOAD_OTHER,USER_CHANGE_PASS_CHANGE_ATTEMPTS,USER_CHANGE_PASS_CHANGE_LOCK_TIME, } from "../../constants/actionTypes";
import moment from 'moment';

const initialState = {
    password:undefined,
    name:'',
    code:'',
    roll:'',
    lock_time:0,
    attempts:0,
};

export default (state=initialState,{payload,type})=>{
    switch (type) {
        case USER_CHANGE_PASS_CHANGE_PASSWORD:
            return {
                ...state,
                password:payload.password
            };
        case USER_CHANGE_PASS_CHANGE_LOCK_TIME:
            return {
                ...state,
                lock_time:payload.lock_time
            };
        case USER_CHANGE_PASS_CHANGE_ATTEMPTS:
            return {
                ...state,
                attempts:payload.attempts
            };
        case USER_CHANGE_PASS_LOAD_DATA:
            return {
                ...state,
                name: payload.name,
                code: payload.code,
                roll: payload.roll,
            };
        case USER_CHANGE_PASS_LOAD_OTHER:
                return {
                    ...state,
                    lock_time: payload.lock_time,
                    attempts: payload.attempts
                };
        default:
            return state;
    }
}
