import {
    TEAM_WISE_SALES_REPORT_LOAD_VALUES,
    TEAM_WISE_SALES_REPORT_LOAD_RESULT
} from "../../constants/actionTypes";
import agent from "../../agent";
import {
    alertDialog
} from "../Dialogs";

export const changeValue = (name, value) => ({
    type: TEAM_WISE_SALES_REPORT_LOAD_VALUES,
    payload: {
        name,
        value
    }
})

export const loadedData = (results1,results2,results3, count) => ({
    type: TEAM_WISE_SALES_REPORT_LOAD_RESULT,
    payload: {
        results1,
        results2,
        results3,
        count
    }
});

export const fetchData = (values) => dispatch => {
    agent.TownWiseSales.search(values).then(({
        results1,
        results2,
        results3,
        count
    }) => {
        dispatch(loadedData(results1, results2,results3, count));
    }).catch(err => {
        dispatch(alertDialog(err.response.data.message, 'error'));
    })
}
