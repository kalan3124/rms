import {
    DC_ALLOCATION_CHANGE_DC_USER_NAME,
    DC_ALLOCATION_CHANGE_DC_CHEMIST_NAME,
    DC_ALLOCATION_USER_LOADED,
    DC_ALLOCATION_CHEMIST_LOADED,
    DC_ALLOCATION_CHANGE_CHECKED_USER,
    DC_ALLOCATION_CHANGE_CHECKED_CHEMIST,
    DC_ALLOCATION_APPEND_CHECKED_CHEMIST,
    DC_ALLOCATION_CLEAR_PAGE
} from "../../constants/actionTypes";
import agent from "../../agent";
import {
    SEARCHING_RECORDS
} from "../../constants/debounceTypes";
import {
    alertDialog
} from "../Dialogs";

export const changeDCUser = dcUser => ({
    type: DC_ALLOCATION_CHANGE_DC_USER_NAME,
    payload: {
        dcUser
    }
});

export const changeDCChemist = dcChemist => ({
    type: DC_ALLOCATION_CHANGE_DC_CHEMIST_NAME,
    payload: {
        dcChemist
    }
});

export const loadedUser = userResults => ({
    type: DC_ALLOCATION_USER_LOADED,
    payload: {
        userResults
    }
});

export const loadedChemist = chemistResults => ({
    type: DC_ALLOCATION_CHEMIST_LOADED,
    payload: {
        chemistResults
    }
});

export const fetchUser = (dsr, delay = true) => {
    let thunk = dispatch => {
        agent.Crud.dropdown('user', dsr, {
            'u_tp_id': 18
        }).then(results => {
            dispatch(loadedUser(results))
        });
    }

    if (delay) thunk.meta = {
        debounce: {
            time: 300,
            key: SEARCHING_RECORDS
        }
    }

    return thunk;
}

export const fetchChemist = (chemist, delay = true) => {
    let thunk = dispatch => {
        agent.Crud.dropdown('chemist',chemist).then(results => {
            dispatch(loadedChemist(results))
        });
    }

    if (delay) thunk.meta = {
        debounce: {
            time: 300,
            key: SEARCHING_RECORDS
        }
    }

    return thunk;
}

export const changeCheckedUser = userChecked => ({
    type: DC_ALLOCATION_CHANGE_CHECKED_USER,
    payload: {
        userChecked
    }
});

export const changeCheckedChemist = chemistChecked => ({
    type: DC_ALLOCATION_CHANGE_CHECKED_CHEMIST,
    payload: {
        chemistChecked
    }
});

export const load = (user) => dispatch => {
    agent.DCAllocation.load(user).then(({
        chemist
    }) => {
        dispatch(appendCheckedChemist(chemist))
    })
}

export const appendCheckedChemist = (chemist) => ({
    type: DC_ALLOCATION_APPEND_CHECKED_CHEMIST,
    payload: {
        chemist
    }
})

export const save = (user, chemists) => dispatch => {
    agent.DCAllocation.save(user, chemists).then(({
        success,
        message
    }) => {
        if (success) {
            dispatch(alertDialog(message, 'success'));
            dispatch(clearPage())
        }
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}

export const clearPage = () => ({
    type: DC_ALLOCATION_CLEAR_PAGE
})
