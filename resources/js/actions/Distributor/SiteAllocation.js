import {
    SITE_ALLOCATION_CHANGE_CHECKED_DSR,
    SITE_ALLOCATION_CHANGE_CHECKED_SITE,
    SITE_ALLOCATION_DSR_LOADED,
    SITE_ALLOCATION_SITE_LOADED,
    SITE_ALLOCATION_CHANGE_DSR_NAME,
    SITE_ALLOCATION_CHANGE_SITE_NAME,
    SITE_ALLOCATION_CLEAR_PAGE,
    SITE_ALLOCATION_APPEND_CHECKED_SITE
} from "../../constants/actionTypes";
import agent from "../../agent";
import {
    SEARCHING_RECORDS
} from "../../constants/debounceTypes";
import {
    alertDialog
} from "../Dialogs";

export const changeCheckedDsr = dsrChecked => ({
    type: SITE_ALLOCATION_CHANGE_CHECKED_DSR,
    payload: {
        dsrChecked
    }
});

export const changeCheckedSite = siteChecked => ({
    type: SITE_ALLOCATION_CHANGE_CHECKED_SITE,
    payload: {
     siteChecked
    }
});

export const loadedDsr = dsrResults => ({
    type: SITE_ALLOCATION_DSR_LOADED,
    payload: {
        dsrResults
    }
});

export const loadedSite = siteResults => ({
    type: SITE_ALLOCATION_SITE_LOADED,
    payload: {
     siteResults
    }
});

export const changeDsrName = dsrName => ({
    type: SITE_ALLOCATION_CHANGE_DSR_NAME,
    payload: {
        dsrName
    }
});

export const changeSiteName = siteName => ({
    type: SITE_ALLOCATION_CHANGE_SITE_NAME,
    payload: {
        siteName
    }
});

export const appendCheckedDsr = (dsr) => ({
    type: SITE_ALLOCATION_APPEND_CHECKED_SITE,
    payload: {
        dsr
    }
})

export const clearPage = () => ({
    type: SITE_ALLOCATION_CLEAR_PAGE
})

export const fetchDsr = (dsr, delay = true) => {
    let thunk = dispatch => {
        agent.Crud.dropdown('user', dsr, {
            'u_tp_id': 14
        }).then(results => {
            dispatch(loadedDsr(results))
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

export const fetchSite = (site, delay = true) => {
    let thunk = dispatch => {
     //    agent.Crud.dropdown('sites', sr, {
        agent.SiteAllocation.loadSite(site).then(results => {
            dispatch(loadedSite(results))
        })
    }

    if (delay) thunk.meta = {
        debounce: {
            time: 300,
            key: SEARCHING_RECORDS
        }
    }

    return thunk;
}

export const save = (site, dsr) => dispatch => {
    agent.SiteAllocation.save(site, dsr).then(({
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

export const load = (site) => dispatch => {
    agent.SiteAllocation.load(site).then(({
        dsr
    }) => {
        dispatch(appendCheckedDsr(dsr))
    })
}
