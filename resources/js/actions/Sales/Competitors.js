import {
    COMPETITOR_CHANGE_FROM_DATE,
    COMPETITOR_CHANGE_TO_DATE,
    COMPETITOR_LOAD_DATA,
    COMPETITOR_EDIT_DATA,
    COMPETITOR_EDIT_LOAD_DATA,
    COMPETITOR_CHANGE_EDIT_FROM_DATE,
    COMPETITOR_CHANGE_EDIT_TO_DATE
} from "../../constants/actionTypes";
import agent from "../../agent";
import {
    alertDialog
} from "../Dialogs";

export const changeFrom = from => ({
    type: COMPETITOR_CHANGE_FROM_DATE,
    payload: {
        from
    }
});

export const changeTo = to => ({
    type: COMPETITOR_CHANGE_TO_DATE,
    payload: {
        to
    }
});

export const changeFromEdit = fromEdit => ({
    type: COMPETITOR_CHANGE_EDIT_FROM_DATE,
    payload: {
        fromEdit
    }
});

export const changeToEdit = toEdit => ({
    type: COMPETITOR_CHANGE_EDIT_TO_DATE,
    payload: {
        toEdit
    }
});

export const searchedData = results => ({
    type: COMPETITOR_LOAD_DATA,
    payload: {
        results
    }
});

export const searchedEditData = (data,comp) => ({
    type: COMPETITOR_EDIT_LOAD_DATA,
    payload: {
        data,
        comp
    }
});

export const changedData = (lastId, date) => ({
    type: COMPETITOR_EDIT_DATA,
    payload: {
        lastId,
        date
    }
});

export const fetchData = (from, to) => dispatch => {
    agent.Competitor.load(from, to).then(({
        results
    }) => {
        if (results.length > 0)
            dispatch(searchedData(results));
        else
            dispatch(alertDialog('Could not find any data', 'error'));
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}

export const fetchEditData = (id) => dispatch => {
    agent.Competitor.loadEdit(id).then(({
        data,
        comp
    }) => {
        if (data.length > 0)
            dispatch(searchedEditData(data,comp));
        else
            dispatch(alertDialog('Could not find any data', 'error'));
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}

export const EditData = (id,from,to) => dispatch => {
    agent.Competitor.Edit(id,from,to).then(({
        status,
        msg
    }) => {
        if (status)
            dispatch(alertDialog(msg, 'success'));
        else
            dispatch(alertDialog('Could not find any data', 'error'));
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}
