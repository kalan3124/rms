import {
    CREATE_RETURN_CHANGE_DISTRIBUTOR,
    CREATE_RETURN_CHANGE_SALESMAN,
    CREATE_RETURN_CHANGE_CUSTOMER,
    CREATE_RETURN_CHANGE_CHANGE_INVOICE_NUMBER,
    CREATE_RETURN_ADD_LINE,
    CREATE_RETURN_REMOVE_LINE,
    CREATE_RETURN_CHANGE_DISCOUNT,
    CREATE_RETURN_CHANGE_PRODUCT,
    CREATE_RETURN_LOAD_LINE_INFO,
    CREATE_RETURN_CHANGE_QTY,
    CREATE_RETURN_CLEAR_PAGE,
    CREATE_RETURN_LOAD_BONUS_DATA,
    CREATE_RETURN_CHANGE_BONUS_QTY,
    CREATE_RETURN_CHANGE_SALABLE,
    CREATE_RETURN_CHANGE_REASON,
    CREATE_RETURN_CHANGE_BATCH,
    CREATE_RETURN_CHANGE_BONUS_BATCH,
} from '../../constants/actionTypes';
import agent from '../../agent';
import { alertDialog } from '../Dialogs';
import { BONUS_FETCH } from '../../constants/debounceTypes';

export const changeDistributor = distributor=>({
    type: CREATE_RETURN_CHANGE_DISTRIBUTOR,
    payload: {
        distributor
    }
});

export const changeSalesman = salesman => ({
    type: CREATE_RETURN_CHANGE_SALESMAN,
    payload: {
        salesman
    }
});

export const changeCustomer = customer => ({
    type: CREATE_RETURN_CHANGE_CUSTOMER,
    payload: {
        customer
    }
});

export const changeInvoiceNumber = number => ({
    type: CREATE_RETURN_CHANGE_CHANGE_INVOICE_NUMBER,
    payload: {
        number
    }
});

export const addLine = () => ({
    type: CREATE_RETURN_ADD_LINE,
});

export const removeLine = (id)=> ({
    type: CREATE_RETURN_REMOVE_LINE,
    payload: {
        id
    }
});

export const changeDiscount = (id,discount) => ({
    type: CREATE_RETURN_CHANGE_DISCOUNT,
    payload:{
        id,
        discount
    }
});

export const changeProduct = (id, product) => ({
    type: CREATE_RETURN_CHANGE_PRODUCT,
    payload: {
        id,
        product
    }
});

export const loadedLineInfo = (id, stock, price) => ({
    type: CREATE_RETURN_LOAD_LINE_INFO,
    payload: {
        id,
        stock,
        price
    }
});

export const changeQty = (id, qty) => ({
    type: CREATE_RETURN_CHANGE_QTY,
    payload: {
        id,
        qty
    }
});

export const changeSalable = (id, salable) => ({
    type: CREATE_RETURN_CHANGE_SALABLE,
    payload: {
        id,
        salable
    }
});

export const clearPage = ()=>({
    type: CREATE_RETURN_CLEAR_PAGE
})

export const fetchLineInfo = (id,distributor,product)=>dispatch=>{
    agent.CreateReturn.loadLineInfo(distributor,product).then(({success,price,stock})=>{
        dispatch(loadedLineInfo(id,stock,price));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const save = (distributor,salesman,customer,lines,bonusLines)=>dispatch=>{
    agent.CreateReturn.save(distributor,salesman,customer,lines,bonusLines).then(({success,message})=>{
        dispatch(alertDialog(message,'success'));
        dispatch(clearPage());
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const fetchNextInvoiceNumber = (distributor,salesman) => dispatch => {
    agent.CreateReturn.loadNumber(distributor,salesman).then(({success,number})=>{
        dispatch(changeInvoiceNumber(number));
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const loadedBonus = (bonusLines)=>({
    type: CREATE_RETURN_LOAD_BONUS_DATA,
    payload:{bonusLines}
});

export const fetchBonus = (disId,lines)=>{
    const thunk = dispatch=>{
        agent.CreateReturn.loadBonus(disId,lines).then(({lines})=>{
            dispatch(loadedBonus(lines));
        });
    }

    thunk.meta = {
        debounce: {
            time: 300,
            key:BONUS_FETCH
        }
    }

    return thunk;
}

export const changeBonusQty = (id,productId,qty) => ({
    type: CREATE_RETURN_CHANGE_BONUS_QTY,
    payload: {id,productId,qty}
})

export const changeReason = (id,reason) => ({
    type: CREATE_RETURN_CHANGE_REASON,
    payload: {id,reason}
})

export const changeBatch = (id,batch) => ({
    type: CREATE_RETURN_CHANGE_BATCH,
    payload: {id,batch}
})

export const changeBonusBatch = (id,productId,batch) => ({
    type: CREATE_RETURN_CHANGE_BONUS_BATCH,
    payload: {id,productId,batch}
})
