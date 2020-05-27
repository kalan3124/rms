import moment from "moment";
import {
    SALES_ITINERARY_DATE_SELECT,
    SALES_ITINERARY_DAY_TYPE_SELECT,
    SALES_ITINERARY_DAY_TYPE_UNSELECT,
    SALES_ITINERARY_LOAD,
    SALES_ITINERARY_USER_CHANGE,
    SALES_ITINERARY_MODE_CHANGE,
    SALES_ITINERARY_CLEAR_DATE,
    SALES_ITINERARY_CHANGE_DATE,
    SALES_ITINERARY_UPDATING_VALUES_CHANGE,
    SALES_ITINERARY_UPDATING_VALUES_CANCEL,
    SALES_ITINERARY_AREA_CHANGE,
    SALES_ITINERARY_TYPE_CHANGE
} from "../../constants/actionTypes";

const now = moment();

const initialState = {
    enabledModes: [],
    dates: {},
    dayTypes: [],
    updatingDate: now.format('YYYY-MM-DD'),
    updatingMode: 0,
    approved: false,
    user:undefined,
    updatingValues:{},
    area:undefined
};

export default (state = initialState, { payload, type }) => {
    switch (type) {
        case SALES_ITINERARY_CHANGE_DATE:
            return {
                ...state,
                dates: {
                    ...state.dates,
                    [parseInt(state.updatingDate.substr(8,2))]: {
                        ...state.dates[parseInt(state.updatingDate.substr(8,2))],
                        ...state.updatingValues,
                        date:parseInt(state.updatingDate.substr(8,2))
                    }
                },
                updatingMode:0
            };
        case SALES_ITINERARY_UPDATING_VALUES_CANCEL:
            return {
                ...state,
                updatingMode:0,
                updatingValues:state.dates[parseInt(state.updatingDate.substr(8,2))]?state.dates[parseInt(state.updatingDate.substr(8,2))]:{}
            }
        case SALES_ITINERARY_UPDATING_VALUES_CHANGE:
            return {
                ...state,
                updatingValues:payload.values
            };
        case SALES_ITINERARY_DATE_SELECT:
            return {
                ...state,
                updatingDate: payload.date,
                updatingValues: state.dates[parseInt(payload.date.substr(8,2))]?state.dates[parseInt(payload.date.substr(8,2))]:{}
            };
        case SALES_ITINERARY_DAY_TYPE_SELECT:
            return {
                ...state,
                dates: {
                    ...state.dates,
                    [payload.date]: {
                        ...state.dates[payload.date],
                        dayTypes: [
                            ...state.dates[payload.date]?state.dates[payload.date].dayTypes:[],
                            payload.dayType.value
                        ]
                    }
                }
            };
        case SALES_ITINERARY_DAY_TYPE_UNSELECT:
            return {
                ...state,
                dates: {
                    ...state.dates,
                    [payload.date]: {
                        ...state.dates[payload.date],
                        dayTypes: state.dates[payload.date]?state.dates[payload.date].dayTypes.filter(
                            dayTypeId => dayTypeId != payload.dayType.value
                        ):[]
                    }
                }
            };
        case SALES_ITINERARY_CLEAR_DATE:
            return {
                ...state,
                dates: {
                    ...state.dates,
                    [payload.date]: undefined
                }
            }
        case SALES_ITINERARY_LOAD:
            return {
                ...state,
                dates: payload.dates.mapToObject("date"),
                approved: payload.approved,
                dayTypes: payload.dayTypes,
                enabledModes: payload.modes
            };
        case SALES_ITINERARY_USER_CHANGE:
            return {
                ...state,
                user: payload.user
            };
        case SALES_ITINERARY_MODE_CHANGE:
            return {
                ...state,
                updatingMode: payload.mode
            };
        case SALES_ITINERARY_AREA_CHANGE:
            return {
                ...state,
                area: payload.area
            };
        case SALES_ITINERARY_TYPE_CHANGE:
            return {
                ...state,
                type: payload.type
            }
        default:
            return state;
    }
};
