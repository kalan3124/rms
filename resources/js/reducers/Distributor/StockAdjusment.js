import {
    STOCK_ADJUSMENT_CHANGE_TYPE,
    STOCK_ADJUSMENT_LOAD_NO,
    STOCK_ADJUSMENT_CHANGE_DIS,
    STOCK_ADJUSMENT_LOAD_DATA,
    STOCK_ADJUSMENT_CHANGE_DATA,
    STOCK_ADJUSMENT_AJUST_QTY,
    STOCK_ADJUSMENT_PAGE_CLEAR
} from "../../constants/actionTypes";

const initialState = {
    adjType: "",
    adjNumber: "",
    dis_id: '',
    rowData: {},
    product: 0,
    ava_qty: 0,
    aju_qty: 0,
    aju_new_qty: 0,
    lastId: -1,
    pro_name: '',
    bt_id: 0,
    batch: 0,
    reason: "",
    searched: false,
    total: 0,
    pro_name_rowspan: 0
}

export default (state = initialState, action) => {
    switch (action.type) {
        case STOCK_ADJUSMENT_CHANGE_TYPE:
            return {
                ...state,
                adjType: action.payload.type
            };
        case STOCK_ADJUSMENT_CHANGE_DIS:
            return {
                ...state,
                dis_id: action.payload.dis_id
            };
        case STOCK_ADJUSMENT_LOAD_NO:
            return {
                ...state,
                adjNumber: action.payload.number
            };
        case STOCK_ADJUSMENT_LOAD_DATA:
            return {
                ...state,
                rowData: action.payload.rowData,
                searched: true
            };
        case STOCK_ADJUSMENT_AJUST_QTY:
            return {
                ...state,
                aju_new_qty: action.payload.aju_new_qty
            }
        case STOCK_ADJUSMENT_PAGE_CLEAR:
            return initialState;
        case STOCK_ADJUSMENT_CHANGE_DATA:
            return {
                ...state,
                rowData: {
                    ...state.rowData,
                    [action.payload.lastId]: {
                        pro_id: action.payload.product,
                        pro_name: action.payload.pro_name,
                        ava_qty: action.payload.ava_qty,
                        bt_id: action.payload.bt_id,
                        aju_qty: action.payload.aju_qty,
                        batch: action.payload.batch,
                        reason: action.payload.reason,
                        total: action.payload.total,
                        pro_name_rowspan: action.payload.pro_name_rowspan
                    }
                }
            }
        default:
            return state;
    }
}