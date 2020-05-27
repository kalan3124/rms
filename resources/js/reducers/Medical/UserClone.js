import { USER_CLONE_CHANGE_USER, USER_CLONE_CHANGE_SELECTED_TYPES, USER_CLONE_LOAD_SECTIONS, USER_CLONE_CHANGE_VALUES, USER_CLONE_RESET } from "../../constants/actionTypes";

const initialState = {
    user:undefined,
    sectionIds:[],
    sections:{},
    values:{},
    display:false
};

export default (state=initialState,{payload,type})=>{
    switch (type) {
        case USER_CLONE_CHANGE_USER:
            return {
                ...state,
                user:payload.user,
                display:!!payload.user,
                values:{},
                sectionIds:[]
            };
        case USER_CLONE_CHANGE_SELECTED_TYPES:

            let length = state.sectionIds.length;

            let modedSelections = state.sectionIds.filter(id=>{
                return id!=payload.id;
            });

            if(modedSelections.length==length){
                modedSelections.push(payload.id);
            }

            return {
                ...state,
                sectionIds:modedSelections
            };
        case USER_CLONE_LOAD_SECTIONS:
            return {
                ...state,
                sections:payload.sections
            };
        case USER_CLONE_CHANGE_VALUES:
            return {
                ...state,
                values:{
                    ...state.values,
                    [payload.name]:payload.value
                }
            };
        case USER_CLONE_RESET:
            return {
                ...state,
                display:false,
                user:null,
                sectionIds:[],
                values:{}
            }
        default:
            return state;
    }
}