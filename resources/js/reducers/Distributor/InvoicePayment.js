import {
    PAYMENT_INVOICE_NUMBER_CHANGE,
    PAYMENT_INVOICE_CHANGE_DISTRIBUTOR,
    PAYMENT_INVOICE_CHANGE_SALESMAN,
    PAYMENT_INVOICE_CHANGE_CUSTOMER,
    PAYMENT_CONFIRM_CLEAR_PAGE,
    PAYMENT_INVOICE_LOAD_DATA,
    PAYMENT_CHECKED_INVOICE,
    PAYMENT_INVOICE_CHANGE_DATA,
    PAYMENT_INVOICE_PAYMENT_AMOUNT_CHANGE,
    PAYMENT_INVOICE_CHANGE_PAYMENT_TYPE,
    PAYMENT_INVOICE_CHANGE_CHEQUE_DATE,
    PAYMENT_INVOICE_CHANGE_CHEQUE_NO,
    PAYMENT_INVOICE_CHANGE_CHEQUE_BANK,
    PAYMENT_INVOICE_CHANGE_CHEQUE_BRANCH,
} from "../../constants/actionTypes";

const initialState = {
    invoiceChecked:[],
    number: '',
    distributor: '',
    salesman: '',
    customer: '',
    pType:'',
    payment:0.00,
    balance:0.00,
    cos_amount:0.00,
    c_no: '',
    c_bank:'',
    c_branch:'',
    c_date:'',
    lines: {
        0:{
            id: 0,
            in_id:null,
            date:null,
            code: null,
            in_amount:0.00,
            os_amount:0.00,
            payment_amount:0.00,
            balance_amount:0.00,
            status:false,
        }
    },
    searched: false,
    saveStatus: true,
    ifCheque: false,
}

export default (state=initialState,action)=>{
    switch (action.type) {
        case PAYMENT_INVOICE_NUMBER_CHANGE:
            return {
                ...state,
                number: action.payload.number
            };
        case PAYMENT_INVOICE_PAYMENT_AMOUNT_CHANGE:
            return {
                ...state,
                payment: action.payload.payment,
                balance: action.payload.payment
            };
        case PAYMENT_INVOICE_CHANGE_PAYMENT_TYPE:
            console.log((action.payload.pType.value==2?true:false));
            return {
                ...state,
                pType: action.payload.pType,
                ifCheque: (action.payload.pType.value==2?true:false),
            };
        case PAYMENT_INVOICE_CHANGE_DISTRIBUTOR:
            return {
                ...state,
                distributor: action.payload.distributor,
                salesman: action.payload.distributor?state.salesman:null,
            };
        case PAYMENT_INVOICE_CHANGE_SALESMAN:
            return {
                ...state,
                salesman: action.payload.salesman
            };
        case PAYMENT_INVOICE_CHANGE_CUSTOMER:
            return {
                ...state,
                customer: action.payload.customer
            };
        case PAYMENT_INVOICE_LOAD_DATA:
            return {
                ...state,
                customer:action.payload.customer,
                cos_amount:action.payload.cos_amount,
                lines: action.payload.lines.mapToObject('id'),
                searched: true
            }
        case PAYMENT_CHECKED_INVOICE:
            return {
                ...state,
                invoiceChecked: action.payload.invoiceChecked
            };
        case PAYMENT_INVOICE_CHANGE_DATA:
            let _balance=0.0;
            let _invoice_balance=0.0;
            let _invoice_payment=0.0;

            if(!action.payload.status){
                _balance = action.payload.balance+action.payload.payment_amount;
                _invoice_balance = _invoice_balance;
            }else{
                if(parseFloat(action.payload.balance)>=parseFloat(action.payload.os_amount)){
                    _balance = (parseFloat(action.payload.balance)-parseFloat(action.payload.os_amount));
                    _invoice_payment = parseFloat(action.payload.os_amount);
                }else if(parseFloat(action.payload.balance)<parseFloat(action.payload.os_amount)){
                    console.log(action.payload);
                    _invoice_balance = (parseFloat(action.payload.os_amount) - parseFloat(action.payload.balance));
                    _invoice_payment = parseFloat(action.payload.balance);
                }
            }

            return {
                ...state,
                balance: _balance,
                lines: {
                    ...state.lines,
                    [action.payload.id]:{
                        id:action.payload.id,
                        in_id:action.payload.in_id,
                        date:action.payload.date,
                        code:action.payload.code,
                        in_amount:action.payload.in_amount,
                        os_amount:action.payload.os_amount,
                        payment_amount:_invoice_payment,
                        balance_amount:_invoice_balance,
                        status:action.payload.status
                    }
                }
            };
        case PAYMENT_INVOICE_CHANGE_CHEQUE_DATE:
            return {
                ...state,
                c_date: action.payload.c_date
            }
        case PAYMENT_INVOICE_CHANGE_CHEQUE_NO:
            return {
                ...state,
                c_no: action.payload.c_no
            }
        case PAYMENT_INVOICE_CHANGE_CHEQUE_BANK:
            return {
                ...state,
                c_bank: action.payload.c_bank
            }
        case PAYMENT_INVOICE_CHANGE_CHEQUE_BRANCH:
            return {
                ...state,
                c_branch: action.payload.c_branch
            }
        case PAYMENT_CONFIRM_CLEAR_PAGE:
            return initialState;
        default:
            return state;
    }
}
