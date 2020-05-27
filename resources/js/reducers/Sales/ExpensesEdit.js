import {
    SALES_EXPENSES_EDIT_CHANGE_REP,
    SALES_EXPENSES_EDIT_CHANGE_MONTH,
    SALES_EXPENSES_EDIT_CHANGE_DATA,
    SALES_EXPENSES_EDIT_LOAD_DATA,
    SALES_EXPENSES_EDIT_PLUS_DATA,
    SALES_EXPENSES_OPEN_MODAL,
    SALES_EXPENSES_CHANGE_VALUE,
    SALES_EXPENSES_ASM_ROLL
} from "../../constants/actionTypes";
import moment from 'moment';

const initialState = {
    open:false,
    rep: undefined,
    month: moment().format('YYYY-MM-DD'),
    rowData: {},
    lastId: -1,
    date: moment().format('YYYY-mm-dd'),
    bataType: undefined,
    stationery: 0,
    parking: 0,
    user: undefined,
    remark: undefined,
    app: undefined,
    mileage:0,
    exp_id:0,
    status:false,
    searched:false,
    def_actual_mileage:0,
    actual_mileage:0,
    mileage_amount:0,
    vht_rate:0,
    values:{},
    roll:""
};

export default (state = initialState, {
    payload,
    type
}) => {
    switch (type) {
        case SALES_EXPENSES_OPEN_MODAL:
            return {
                ...state,
                open: payload.open
            };
        case SALES_EXPENSES_ASM_ROLL:
            return {
                ...state,
                roll: payload.roll
            };
        case SALES_EXPENSES_CHANGE_VALUE:
            return {
                ...state,
                values:{
                    ...state.values,
                    [payload.name]:payload.value
                }
            }
        case SALES_EXPENSES_EDIT_CHANGE_REP:
            return {
                ...state,
                rep: payload.rep
            };
        case SALES_EXPENSES_EDIT_CHANGE_MONTH:
            return {
                ...state,
                month: payload.month
            };
        case SALES_EXPENSES_EDIT_LOAD_DATA:
            return {
                ...state,
                rowData: payload.results,
                searched:true
            };
        case SALES_EXPENSES_EDIT_PLUS_DATA:
            return {
                ...state,
                rowData: {
                        ...state.rowData,
                        [state.lastId]: {
                            lastId: state.lastId,
                            date: payload.date,
                            bataType: payload.bataType,
                            stationery: payload.stationery,
                            parking: payload.parking,
                            user: payload.user,
                            remark: payload.remark,
                            app: payload.app,
                            mileage:payload.mileage,
                            lastId: state.lastId
                        }
                    },
                    lastId: state.lastId
            };
        case SALES_EXPENSES_EDIT_CHANGE_DATA:
            return {
                ...state,
                rowData: {
                    ...state.rowData,
                    [payload.lastId]: {
                        date: payload.date,
                        bataType: payload.bataType,
                        stationery: payload.stationery,
                        parking: payload.parking,
                        user: payload.user,
                        remark: payload.remark,
                        app: payload.app,
                        mileage:payload.mileage,
                        exp_id:payload.exp_id,
                        lastId: payload.lastId,
                        status:payload.status,
                        def_actual_mileage:payload.def_actual_mileage,
                        actual_mileage:payload.actual_mileage,
                        mileage_amount:payload.mileage_amount,
                        vht_rate:payload.vht_rate
                    }
                },
            };
        default:
            return state;
    }
}
