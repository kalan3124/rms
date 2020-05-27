import { 
    USER_CUSTOMERS_LOADED,
    USER_CUSTOMER_LEFT_TAB_CHANGE,
    USER_CUSTOMER_RIGHT_TAB_CHANGE,
    USER_CUSTOMER_USER_NAME_CHANGE,
    USER_CUSTOMER_USER_SELECT,
    USER_CUSTOMER_RESULTS_LOADED,
    USER_CUSTOMER_KEYWORD_CHANGE, 
    USER_CUSTOMER_USERS_LOADED,
    USER_CUSTOMER_USER_MENU_OPEN,
    USER_CUSTOMER_USER_MENU_CLOSE,
    USER_CUSTOMER_SUB_TOWN_CHANGE,
    USER_CUSTOMER_DOCTORS_CHANGE,
    USER_CUSTOMER_CHEMISTS_CHANGE,
    USER_CUSTOMER_STAFFS_CHANGE
} from "../../constants/actionTypes";
import agent from "../../agent";
import { SEARCHING_RECORDS } from "../../constants/debounceTypes";
import { alertDialog, confirmDialog } from "../Dialogs";
import { changeUser } from "./UserAllocation";

export const changeRightTab = tab=>({
    type:USER_CUSTOMER_RIGHT_TAB_CHANGE,
    payload:{tab}
});

export const changeLeftTab = tab=>({
    type:USER_CUSTOMER_LEFT_TAB_CHANGE,
    payload:{tab}
});

export const loadedCustomers = (doctors,chemists,staffs)=>({
    type:USER_CUSTOMERS_LOADED,
    payload:{doctors,chemists,staffs}
});

export const changeUserName = userName=>({
    type:USER_CUSTOMER_USER_NAME_CHANGE,
    payload:{userName}
});

export const loadedResults = results=>({
    type:USER_CUSTOMER_RESULTS_LOADED,
    payload:{results}
});

export const changeKeyword = keyword =>({
    type:USER_CUSTOMER_KEYWORD_CHANGE,
    payload:{keyword}
});

export const loadedUsers = users=>({
    type:USER_CUSTOMER_USERS_LOADED,
    payload:{users}
});

export const changeSubTown = subTown =>({
    type:USER_CUSTOMER_SUB_TOWN_CHANGE,
    payload:{subTown}
});

export const openUserMenu = (userMenuRef)=>({
    type:USER_CUSTOMER_USER_MENU_OPEN,
    payload:{userMenuRef}
});

export const closeUserMenu = ()=>({
    type:USER_CUSTOMER_USER_MENU_CLOSE
});

export const fetchResults = (link,keyword,subTown,delay=true)=>{
    let thunk = dispatch=>{

        let filters = {};
        if(subTown) filters['sub_twn_id'] = subTown.value;
    
        agent.Crud.dropdown(link,keyword,filters).then(results=>{
            dispatch(loadedResults(results));
        })
        
    };

    if(delay){
        thunk.meta={
            debounce: {
                time: 300,
                key:SEARCHING_RECORDS
            }
        }
    }

    return thunk;
}

export const fetchUsers = keyword=>dispatch=>{
    dispatch(changeUserName(keyword));

    let thunk = dispatch=>{
        agent.Crud.dropdown('user',keyword).then(users=>{
            dispatch(loadedUsers(users))
        })
    }

    thunk.meta = {
        debounce: {
            time: 300,
            key: SEARCHING_RECORDS
        }
    }

    dispatch(thunk);
}

export const changeChemists = chemists=>({
    type:USER_CUSTOMER_CHEMISTS_CHANGE,
    payload:{chemists}
})

export const changeDoctors = doctors=>({
    type:USER_CUSTOMER_DOCTORS_CHANGE,
    payload:{doctors}
})

export const changeStaffs = staffs=>({
    type:USER_CUSTOMER_STAFFS_CHANGE,
    payload:{staffs}
})

export const fetchCustomers = user=>dispatch=>{
    dispatch(changeUser(user,'user-customer'));
    dispatch(changeUserName(user.label))
    agent.UserCustomer.loadByUser(user).then(({chemists,doctors,staffs})=>{
        dispatch(loadedCustomers(doctors.mapToObject('value'),chemists.mapToObject('value'),staffs.mapToObject('value')))
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const addItem = (type,item,user,modedItems)=>dispatch=>{
    agent.UserCustomer.create(user,item,type).then(({success})=>{
        if(success){
            if(type=='chemist'){
                dispatch(changeChemists(modedItems));
            } else if(type=="other_hospital_staff") {
                dispatch(changeStaffs(modedItems));
            } else {
                dispatch(changeDoctors(modedItems));
            }

            dispatch(alertDialog("Successfully allocated the customer!","success"));
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const removeItem = (type,item,user,modedItems)=>dispatch=>{
    agent.UserCustomer.remove(user,item,type).then(({success})=>{
        if(success){
            if(type=='chemist'){
                dispatch(changeChemists(modedItems));
            } else if(type=="other_hospital_staff") {
                dispatch(changeStaffs(modedItems));
            } else {
                dispatch(changeDoctors(modedItems));
            }
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const removeAll = (user)=>dispatch=>{

    dispatch(confirmDialog("You can not recover again deleted allocations. Are you sure you want to delete all customer allocations from the "+user.label+"? ",()=>{
        agent.UserCustomer.removeAll(user).then(({success,message})=>{
            if(success){
                dispatch(alertDialog(message,"success"));
                dispatch(changeChemists({}));
                dispatch(changeStaffs({}));
                dispatch(changeDoctors({}));
            }
        }).catch(err=>{
            dispatch(alertDialog(err.response.data.message,'error'));
        })
    }))
}