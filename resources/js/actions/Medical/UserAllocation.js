import { USER_ALLOCATIONS_USER_CHANGE } from "../../constants/actionTypes";

export const changeUser = (user,path)=>({
    type: USER_ALLOCATIONS_USER_CHANGE,
    payload:{user,path}
})