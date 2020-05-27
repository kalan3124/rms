import { SR_PRODUCT_LOADED_SRS, SR_PRODUCT_LOADED_PRODUCTS, SR_PRODUCT_CHANGE_PRODUCT_KEYWORD, SR_PRODUCT_CHANGE_SR_KEYWORD, SR_PRODUCT_ADD_PRODUCTS, SR_PRODUCT_ADD_SRS, SR_PRODUCT_REMOVE_PRODUCTS, SR_PRODUCT_REMOVE_SRS, SR_PRODUCT_CLEAR_PAGE, SR_PRODUCT_LOADED_CHECKED_PRODUCTS } from "../../constants/actionTypes";
import agent from "../../agent";
import { alertDialog } from "../Dialogs";
import { DISTRIBUTOR_SALES_REP_TYPE } from "../../constants/config";

export const addSr = (sr) =>({ 
    type: SR_PRODUCT_ADD_SRS,
    payload:{sr}
})

export const addProduct = product=>({
    type: SR_PRODUCT_ADD_PRODUCTS,
    payload:{product}
})

export const removeSr = (sr) =>({
    type: SR_PRODUCT_REMOVE_SRS,
    payload:{sr}
})

export const removeProduct = product=>({
    type: SR_PRODUCT_REMOVE_PRODUCTS,
    payload:{product}
})

export const loadedSrs = srs=>({
    type:SR_PRODUCT_LOADED_SRS,
    payload:{srs}
})

export const loadedProducts = products =>({
    type: SR_PRODUCT_LOADED_PRODUCTS,
    payload: {products}
})

export const changeProductKeyword = keyword =>({
    type:SR_PRODUCT_CHANGE_PRODUCT_KEYWORD,
    payload:{keyword}
})

export const changeSrKeyword = keyword =>({
    type: SR_PRODUCT_CHANGE_SR_KEYWORD,
    payload:{keyword}
});

export const clearPage = ()=>({
    type:SR_PRODUCT_CLEAR_PAGE
})

export const fetchSrs = (keyword)=>dispatch=>{
    dispatch(changeSrKeyword(keyword));

    agent.Crud.dropdown('user',keyword,{u_tp_id:DISTRIBUTOR_SALES_REP_TYPE}).then((data)=>{
        dispatch(loadedSrs(data));
    });
}

export const fetchProduct = (keyword) =>dispatch=>{
    dispatch(changeProductKeyword(keyword));

    agent.Crud.dropdown('product',keyword).then((data)=>{
        dispatch(loadedProducts(data));
    });
}

export const submit = (srs,products)=>dispatch=>{
    agent.SrProduct.save(srs,products).then(({success,message})=>{
        if(success){
            dispatch(alertDialog(message,'success'));
            dispatch(clearPage())
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const loadedCheckedProducts = (products)=>({
    type:SR_PRODUCT_LOADED_CHECKED_PRODUCTS,
    payload:{products}
})

export const fetchProductsBySr = srId =>dispatch=>{
    agent.SrProduct.loadProduct(srId).then(data=>{
        dispatch(loadedCheckedProducts(data))
    })
}