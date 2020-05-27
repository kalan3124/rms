import {
    ITINERARY_FM_CHANGE,
    ITINERARY_MR_CHANGE,
    ITINERARY_YEAR_MONTH_CHANGE, 
    ITINERARY_LOADING, 
    ITINERARY_LOADED, 
    ITINERARY_NOT_SET, 
    ITINERARY_DATE_CHANGE, 
    ITINERARY_DAY_TYPES_LOADED,
    ITINERARY_DATE_SELECTED,
    ITINERARY_DAYS_LOADED,
    ITINERARY_DATE_CANCEL, 
    ITINERARY_CHANGE_ADDITIONAL_VALUES,
    ITINERARY_CONFIRM_ADDITIONAL_VALUES,
    ITINERARY_CLEAR_DATE,
    ITINERARY_CHANGE_JOIN_FIELD_WORKER,
    ITINERARY_CHANGE_OTHER_DAY,
    ITINERARY_CLEAR
} from "../../constants/actionTypes";

import moment from 'moment';


const intialState = {
    yearMonth:moment().format('YYYY-MM'),
    mr:undefined,
    fm:undefined,
    dates:{},
    loading:false,
    notFound:false,
    updatingDate:false,
    dayTypes:undefined,
    watchedTerritories:undefined,
    days:[],
    additionalValues:{},
    updatingMode:undefined,
    approved:undefined
};

export default (state=intialState,{payload,type})=>{
    switch (type) {
        case ITINERARY_FM_CHANGE:
            return {
                ...state,
                fm:payload.fm,
                days:[],
                dates:{}
            }
        case ITINERARY_MR_CHANGE:
            return {
                ...state,
                mr:payload.mr,
                days:[],
                dates:{}
            }
        case ITINERARY_YEAR_MONTH_CHANGE:
            return {
                ...state,
                yearMonth:payload.yearMonth
            }
        case ITINERARY_LOADING:
            return {
                ...state,
                loading:true
            }
        case ITINERARY_LOADED:
            return {
                ...state,
                dates:payload.dates,
                approved: payload.approved,
                notFound:false
            }
        case ITINERARY_NOT_SET:
            return {
                ...state,
                dates:{},
                notFound:true
            }
        case ITINERARY_DATE_CHANGE:
            let modDates = {...state.dates};
            modDates[payload.values.date] = payload.values;

            let usedDayIds = [];

            Object.values(modDates).forEach(date=>{
                if(date.description){
                    usedDayIds.push(date.description.value);
                }
            });

            return {
                ...state,
                dates:modDates
            }
        case ITINERARY_DAY_TYPES_LOADED:
            return {
                ...state,
                dayTypes:payload.dayTypes.mapToObject('value'),
                loading:false
            }
        case ITINERARY_DATE_SELECTED:
            return {
                ...state,
                updatingDate:payload.dateDetails,
                updatingMode:payload.mode
            }
        case ITINERARY_DAYS_LOADED:
            return {
                ...state,
                days:payload.days
            }
        case ITINERARY_DATE_CANCEL:
            return {
                ...state,
                updatingDate:false,
                updatingMode:undefined
            };
        case ITINERARY_CHANGE_ADDITIONAL_VALUES:
            return {
                ...state,
                additionalValues:{...payload.additionalValues}
            };
        case ITINERARY_CLEAR:
            let clearedDates = Object.values(state.dates).filter(date=>{
                return !!(date.special||date.forbidden);
            })

            clearedDates = clearedDates.map(details=>{
                if(details.forbidden)
                    return details;

                return {
                    date:details.date,
                    special:details.special,
                    types:[]
                }
            })

            return {
                ...state,
                dates:clearedDates.mapToObject('date')
            }
        case ITINERARY_CONFIRM_ADDITIONAL_VALUES:
            return {
                ...state,
                additionalValues:{},
                updatingMode:undefined,
                updatingDate:undefined,
                dates:{
                    ...state.dates,
                    [payload.date]:{
                        ...{
                            description:{},
                            types:[],
                            ...state.dates[payload.date],
                        },
                        additionalValues:payload.additionalValues
                    }
                }
            };
        case ITINERARY_CLEAR_DATE:
            let modedDates = {...state.dates};
            delete modedDates[payload.date];
            return {
                ...state,
                dates:modedDates
            };
        case ITINERARY_CHANGE_JOIN_FIELD_WORKER:
            return {
                ...state,
                updatingMode:undefined,
                updatingDate:undefined,
                dates:{
                    ...state.dates,
                    [payload.date]:{
                        ...{
                            description:{},
                            types:[],
                            ...state.dates[payload.date],
                        },
                        joinFieldWorker:payload.value
                    }
                }
            };
        case ITINERARY_CHANGE_OTHER_DAY:
            return {
                ...state,
                updatingMode:undefined,
                updatingDate:undefined,
                dates:{
                    ...state.dates,
                    [payload.date]:{
                        ...{
                            description:{},
                            types:[],
                            ...state.dates[payload.date]
                        },
                        otherDay:payload.value
                    }
                }
            }
        default:
            return state;
    }
}