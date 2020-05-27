import {
    SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_REP,
    SALES_WEEKLY_TARGET_ALLOCATION_LOAD_QTY_VALUE,
    SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_MONTH,
    SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_PREASANTAGE,
    SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_TARGET,
    SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_PLUS,
    SALES_WEEKLY_TARGET_ALLOCATION_PREASANTAGE_CAL,
    SALES_WEEKLY_TARGET_ALLOCATION_CHECK_MONTHLY_TARGET_EXCEEDED,
    SALES_WEEKLY_TARGET_ALLOCATION_CHECK_ERROR,
    SALES_WEEKLY_TARGET_ALLOCATION_CHECK_TYPE,
    SALES_WEEKLY_TARGET_ALLOCATION_DROP_WEEK,
    SALES_WEEKLY_TARGET_ALLOCATION_PAGE_CLEAR
} from "../../constants/actionTypes";
import moment from 'moment';

const initialState = {
    rep: undefined,
    totQty: 0,
    totValue: 0.00,
    totCurrent: 0.00,
    presantage: 0,
    targets: {},
    lastId: -1,
    start_week: 0,
    end_week: 0,
    value: 0,
    week_presantage: 0,
    calculation: 0,
    target: 0,
    error: false,
    month: moment().format('YYYY-MM-DD'),
    ifCheckWeekly: [],
    type: false,
    drop: 0,
    month_end:0
};

export default (state = initialState, {
    payload,
    type
}) => {
    switch (type) {
        case SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_REP:
            return {
                ...state,
                rep: payload.rep
            };
        case SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_MONTH:
            return {
                ...state,
                month: payload.month
            };
        case SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_PREASANTAGE:
            return {
                ...state,
                presantage: payload.presantage
            };
        case SALES_WEEKLY_TARGET_ALLOCATION_LOAD_QTY_VALUE:
            return {
                ...state,
                totValue: payload.totValue,
                    totQty: payload.totQty,
                    totCurrent: payload.totCurrent,
                    targets: payload.ifCheckWeekly ? payload.ifCheckWeekly : state.targets
            };
        case SALES_WEEKLY_TARGET_ALLOCATION_PREASANTAGE_CAL:
            return {
                ...state,
                calculation: payload.calculation,
            };
        case SALES_WEEKLY_TARGET_ALLOCATION_CHECK_MONTHLY_TARGET_EXCEEDED:
            return {
                ...state,
                target: payload.target,
            };
        case SALES_WEEKLY_TARGET_ALLOCATION_CHECK_ERROR:
            return {
                ...state,
                error: payload.error,
            };
        case SALES_WEEKLY_TARGET_ALLOCATION_CHECK_TYPE:
            return {
                ...state,
                type: payload.type,
                month_end:payload.month_end
            };
        case SALES_WEEKLY_TARGET_ALLOCATION_DROP_WEEK:
            let dropTarget = {...state.targets};
            delete dropTarget[payload.lastId];
                return {
                    ...state,
                    targets:dropTarget
                };
        case SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_PLUS:
            return {
                ...state,
                targets: {
                        ...state.targets,
                        [state.lastId + 1]: {
                            start_week: state.lastId+1 == 0 ? 1 : payload.start_week,
                            end_week: payload.end_week,
                            week_presantage: payload.week_presantage,
                            value: payload.value,
                            lastId: state.lastId + 1
                        }
                    },
                    lastId: state.lastId + 1
            };
        case SALES_WEEKLY_TARGET_ALLOCATION_CHANGE_TARGET:
            return {
                ...state,
                targets: {
                    ...state.targets,
                    [payload.lastId]: {
                        start_week: payload.start_week,
                        end_week: payload.end_week,
                        week_presantage: payload.week_presantage,
                        value: payload.value,
                        lastId: payload.lastId
                    }
                },
            };
        case SALES_WEEKLY_TARGET_ALLOCATION_PAGE_CLEAR:
            return initialState;
        default:
            return state;
    }
}
