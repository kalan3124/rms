import {HEADER_USER_MENU_TOGGLE, SIDEBAR_MOBILE_TOGGLE} from '../constants/actionTypes';

export const toggleUserMenu = (element)=>({
    type:HEADER_USER_MENU_TOGGLE,
    payload:{element}
})

export const toggleSideBar = ()=>({
    type:SIDEBAR_MOBILE_TOGGLE
})
