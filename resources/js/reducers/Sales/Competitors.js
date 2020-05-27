import {
    COMPETITOR_CHANGE_FROM_DATE,
    COMPETITOR_CHANGE_TO_DATE,
    COMPETITOR_LOAD_DATA,
    COMPETITOR_EDIT_DATA,
    COMPETITOR_EDIT_LOAD_DATA,
    COMPETITOR_CHANGE_EDIT_FROM_DATE,
    COMPETITOR_CHANGE_EDIT_TO_DATE
} from "../../constants/actionTypes";

function getToday() {
    var d = new Date(),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2)
        month = '0' + month;
    if (day.length < 2)
        day = '0' + day;

    return [year, month, day].join('-');
}

const initialState = {
    from: getToday(),
    to: getToday(),
    comp:{},
    rowData: {},
    data:[],
    lastId: -1,
    searched:false,
    searchedData:false,
    fromEdit: getToday(),
    toEdit: getToday(),
}


export default (state = initialState, {
    payload,
    type
}) => {
    switch (type) {
        case COMPETITOR_CHANGE_FROM_DATE:
            return {
                ...state,
                from: payload.from
            };
        case COMPETITOR_CHANGE_TO_DATE:
            return {
                ...state,
                to: payload.to
            };
        case COMPETITOR_CHANGE_EDIT_FROM_DATE:
            return {
                ...state,
                fromEdit: payload.fromEdit
            };
        case COMPETITOR_CHANGE_EDIT_TO_DATE:
            return {
                ...state,
                toEdit: payload.toEdit
            };
        case COMPETITOR_LOAD_DATA:
            return {
                ...state,
                rowData: payload.results,
                searched:true
            };
        case COMPETITOR_EDIT_LOAD_DATA:
            return {
                ...state,
                data: payload.data,
                comp:payload.comp,
                searchedData:true
            };
        case COMPETITOR_EDIT_DATA:
            return {
                ...state,
                rowData: {
                    ...state.rowData,
                    [payload.lastId]: {
                        date: payload.date,
                    }
                },
            };
        default:
            return state;
    }
}
