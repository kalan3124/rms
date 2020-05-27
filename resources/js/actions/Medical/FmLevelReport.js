import { FM_LEVEL_LOAD_VALUES, FM_LEVEL_LOAD_RESULT } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";

export const changeValue = (name, value) => ({
    type: FM_LEVEL_LOAD_VALUES,
    payload: {
        name,
        value
    }
});

export const loadedData = (results, count, jfw) => ({
    type: FM_LEVEL_LOAD_RESULT,
    payload: { results, count, jfw }
});

export const fetchData = (values) => dispatch => {
    agent.FmLevelReport.search(values).then(({ results, count, jfw }) => {
        dispatch(loadedData(results, count, jfw));
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}