import {
    CRUD_PAGE_LOADING,
    CRUD_PAGE_LOADED,
    CRUD_PAGE_FORM_VALUES_CHANGE,
    CRUD_PAGE_SEARCH_RESULTS_LOADING,
    CRUD_PAGE_SEARCH_RESULTS_LOADED,
    CRUD_PAGE_ORDER_BY_CHANGE,
    CRUD_FORM_CLEAR,
    CRUD_CHOOSE_TO_UPDATE,
    CRUD_CREATE_CLICK,
    CRUD_SEARCH_CLICK,
    CRUD_MODAL_CLOSE,
    CRUD_CHANGE_PAGE_NUMBER,
    CRUD_CHANGE_ROWS_COUNT,
    CRUD_CHOOSE_TO_DELETE,
    CRUD_CLEAR_DELETE,
    CRUD_CHOOSE_TO_RESTORE,
    CRUD_CLEAR_RESTORE
} from "../constants/actionTypes";
import agent from "../agent";
import { SEARCHING_RECORDS } from "../constants/debounceTypes";
import { alertDialog, confirmDialog } from "./Dialogs";
import { APP_URL } from "../constants/config";

export const loading = () => ({
    type: CRUD_PAGE_LOADING
});

export const loaded = (info) => ({
    type: CRUD_PAGE_LOADED,
    payload: info
})

export const formValuesChange = (values) => ({
    type: CRUD_PAGE_FORM_VALUES_CHANGE,
    payload: {
        values
    }
})

export const searching = () => ({
    type: CRUD_PAGE_SEARCH_RESULTS_LOADING
})

export const searched = (results, count) => ({
    type: CRUD_PAGE_SEARCH_RESULTS_LOADED,
    payload: {
        results,
        count
    }
})

export const changeSort = (form, values,sortBy,sortMode,...args) => (
    dispatch => {
        dispatch({
            type: CRUD_PAGE_ORDER_BY_CHANGE,
            payload: {
                column:sortBy,
                mode:sortMode
            }
        });
        dispatch(fetchResults(form, values,sortBy,sortMode,...args));
    }
)

export const clearForm = () => ({
    type: CRUD_FORM_CLEAR
})

export const clearDelete= ()=>({
    type:CRUD_CLEAR_DELETE
});

export const clearRestore = ()=>({
    type: CRUD_CLEAR_RESTORE
})

export const chooseToUpdate = id=>({
    type:CRUD_CHOOSE_TO_UPDATE,
    payload:{id}
})

export const chooseToDelete = id=>({
    type:CRUD_CHOOSE_TO_DELETE,
    payload:{id}
})

export const chooseToRestore = id=>({
    type: CRUD_CHOOSE_TO_RESTORE,
    payload: {id}
})

export const createRecord = (form,values,...args)=>(
    dispatch=>{
        agent.Crud.create(form,values).then(data=>{
            dispatch(clearForm())
            dispatch(fetchResults(form,{},...args))
            dispatch(alertDialog("Successfully created your record!","success"));
        }).catch(err=>{
            dispatch(alertDialog(err.response.data.message,'error'));
        })
    }
)

export const clickSearch = ()=>({
    type:CRUD_SEARCH_CLICK
})

export const clickCreate = ()=>({
    type:CRUD_CREATE_CLICK
})

export const clickDelete = (id,onConfirm,onCancel)=>(
    dispatch=>{
        dispatch(chooseToDelete(id));
        dispatch(confirmDialog("Are you want to delete this record?",onConfirm,onCancel))
    }
)

export const clickRestore = (id,onConfirm,onCancel)=>dispatch=>{
    dispatch(chooseToRestore(id));
    dispatch(confirmDialog("Are you want to restore this record?",onConfirm, onCancel));
}

export const confirmDelete=(form,id,...args) =>(
    dispatch=>{
        agent.Crud.delete(form,id).then(data=>{
            dispatch(clearDelete());
            dispatch(fetchResults(form,...args));
            dispatch(alertDialog("Successfully deleted your record!","success"));
        }).catch(({response})=>{
            dispatch(alertDialog(response.data.message,'error'));
        })
    }
)


export const confirmRestore=(form,id,...args) =>(
    dispatch=>{
        agent.Crud.restore(form,id).then(data=>{
            dispatch(clearDelete());
            dispatch(fetchResults(form,...args));
            dispatch(alertDialog("Successfully restored your record!","success"));
        }).catch(({response})=>{
            dispatch(alertDialog(response.data.message,'error'));
        })
    }
)

export const closeModal = ()=>({
    type:CRUD_MODAL_CLOSE
})

export const updateRecord = (form,id,values,...args)=>(
    dispatch=>{
        agent.Crud.update(form,values,id).then(data=>{
            dispatch(clearForm())
            dispatch(fetchResults(form,{},...args))
            dispatch(alertDialog("Successfully updated your record!","success"));
        }).catch(err=>{
            dispatch(alertDialog(err.response.data.message,'error'));
        })
    }
)

export const fetchResults = (form, values,sortBy,sortMode,page,perPage,debounce=false) => {
    const thunk = dispatch => {
        dispatch(searching());
        agent.Crud.search(form, values,sortBy,sortMode,page,perPage).then(data => {
            dispatch(searched(data.results, data.count));
        })
    }

    if(debounce)thunk.meta = {
        debounce: {
            time: 300,
            key:SEARCHING_RECORDS
        }
    }

    return thunk;
}

export const download = (format,...args) =>(
    dispatch=>{
        agent.Crud[format](...args).then(({file})=>{
            window.open(APP_URL+'storage/'+format+'/'+file);
        }).catch(err=>{
            dispatch(alertDialog(err.response.data.message,'error'));
        })
    }
)

export const fetchInformation = (form) => (
    dispatch => {
        dispatch(loading());
        agent.Crud.info(form).then(info => {
            dispatch(loaded(info));
            dispatch(fetchResults(form))
        })
    }
)

export const changePageNumber = (form,values,sortBy,sortMode,page,perPage)=>(
    dispatch=>{
        dispatch({
            type:CRUD_CHANGE_PAGE_NUMBER,
            payload:{page}
        });
        dispatch(fetchResults(form,values,sortBy,sortMode,page,perPage));
    }
)

export const changeRowCount= (form,values,sortBy,sortMode,page,perPage)=>(
    dispatch=>{
        dispatch({
            type:CRUD_CHANGE_ROWS_COUNT,
            payload:{perPage}
        });
        dispatch(fetchResults(form,values,sortBy,sortMode,page,perPage));
    }
)
