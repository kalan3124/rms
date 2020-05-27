import { FM_LEVEL_LOAD_VALUES, FM_LEVEL_LOAD_RESULT } from "../../constants/actionTypes";

const initialState = {
    values: {},
    searched: false,
    rowData: [],
    jfw: []
}

export default (state = initialState, { payload, type }) => {
    switch (type) {
        case FM_LEVEL_LOAD_VALUES:
            return {
                ...state,
                values: {
                    ...state.values,
                    [payload.name]: payload.value
                }
            };
        case FM_LEVEL_LOAD_RESULT:
            return {
                ...state,
                rowData: payload.results,
                searched: true,
                resultCount: payload.count,
                jfw: payload.jfw
            };
        default:
            return state;
    }
}