import moment from "moment";
import {
    TEAM_WISE_SALES_REPORT_LOAD_VALUES,
    TEAM_WISE_SALES_REPORT_LOAD_RESULT
} from "../../constants/actionTypes";

const initialState = {
    rowData1: [],
    rowData2: [],
    rowData3: [],
    values: {},
    resultCount: 0,
    searched: false,
};

export default (state = initialState, {
    payload,
    type
}) => {
    switch (type) {
        case TEAM_WISE_SALES_REPORT_LOAD_VALUES:
            return {
                ...state,
                values: {
                    ...state.values,
                    [payload.name]: payload.value
                }
            };
        case TEAM_WISE_SALES_REPORT_LOAD_RESULT:
            return {
                ...state,
                rowData1: payload.results1,
                    rowData2: payload.results2,
                    rowData3: payload.results3,
                    resultCount: payload.count,
                    searched: true
            };

        default:
            return state;
    }
}
