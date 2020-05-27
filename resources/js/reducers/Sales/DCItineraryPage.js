import moment from "moment";
import {
    DC_ITINERARY_DATE_SELECT,
    DC_ITINERARY_DAY_TYPE_SELECT,
    DC_ITINERARY_DAY_TYPE_UNSELECT,
    DC_ITINERARY_LOAD,
    DC_ITINERARY_USER_CHANGE,
    DC_ITINERARY_MODE_CHANGE,
    DC_ITINERARY_CLEAR_DATE,
    DC_ITINERARY_CHANGE_DATE,
    DC_ITINERARY_UPDATING_VALUES_CHANGE,
    DC_ITINERARY_UPDATING_VALUES_CANCEL,
    // DC_ITINERARY_AREA_CHANGE,
    DC_ITINERARY_TYPE_CHANGE
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
        case DC_ITINERARY_CHANGE_DATE:
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
        case DC_ITINERARY_UPDATING_VALUES_CANCEL:
            return {
                ...state,
                updatingMode:0,
                updatingValues:state.dates[parseInt(state.updatingDate.substr(8,2))]?state.dates[parseInt(state.updatingDate.substr(8,2))]:{}
            }
        case DC_ITINERARY_UPDATING_VALUES_CHANGE:
            return {
                ...state,
                updatingValues:payload.values
            };
        case DC_ITINERARY_DATE_SELECT:
            return {
                ...state,
                updatingDate: payload.date,
                updatingValues: state.dates[parseInt(payload.date.substr(8,2))]?state.dates[parseInt(payload.date.substr(8,2))]:{}
            };
        case DC_ITINERARY_DAY_TYPE_SELECT:
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
        case DC_ITINERARY_DAY_TYPE_UNSELECT:
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
        case DC_ITINERARY_CLEAR_DATE:
            return {
                ...state,
                dates: {
                    ...state.dates,
                    [payload.date]: undefined
                }
            }
        case DC_ITINERARY_LOAD:
            return {
                ...state,
                dates: payload.dates.mapToObject("date"),
                approved: payload.approved,
                dayTypes: payload.dayTypes,
                enabledModes: payload.modes
            };
        case DC_ITINERARY_USER_CHANGE:
            return {
                ...state,
                user: payload.user
            };
        case DC_ITINERARY_MODE_CHANGE:
            return {
                ...state,
                updatingMode: payload.mode
            };
        // case DC_ITINERARY_AREA_CHANGE:
        //     return {
        //         ...state,
        //         area: payload.area
        //     };
        case DC_ITINERARY_TYPE_CHANGE:
            return {
                ...state,
                type: payload.type
            }
        default:
            return state;
    }
};
