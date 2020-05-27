import { TEAM_PRODUCT_CHANGE_CHECKED_PRODUCTS, TEAM_PRODUCT_CHANGE_CHECKED_TEAMS, TEAM_PRODUCT_TEAMS_LOADED, TEAM_PRODUCT_PRODUCTS_LOADED, TEAM_PRODUCT_CHANGE_PRODUCT_NAME, TEAM_PRODUCT_CHANGE_TEAM_NAME, TEAM_PRODUCT_CLEAR_PAGE, TEAM_PRODUCT_APPEND_CHECKED_PRODUCTS,TEAM_PRODUCT_CHANGE_PRINCIPAL } from "../../constants/actionTypes";
import agent from "../../agent";
import { SEARCHING_RECORDS } from "../../constants/debounceTypes";
import { alertDialog } from "../Dialogs";

export const changeCheckedProducts = productChecked=>({
    type:TEAM_PRODUCT_CHANGE_CHECKED_PRODUCTS,
    payload:{productChecked}
});

export const changeCheckedTeams = teamChecked=>({
    type:TEAM_PRODUCT_CHANGE_CHECKED_TEAMS,
    payload:{teamChecked}
});

export const loadedTeams = teamResults=>({
    type:TEAM_PRODUCT_TEAMS_LOADED,
    payload:{teamResults}
});

export const loadedProducts = productResults=>({
    type:TEAM_PRODUCT_PRODUCTS_LOADED,
    payload:{productResults}
});

export const loadedPrincipal = principal=>({
    type:TEAM_PRODUCT_CHANGE_PRINCIPAL,
    payload:{principal}
});

export const changeProductName = productName=>({
    type:TEAM_PRODUCT_CHANGE_PRODUCT_NAME,
    payload:{productName}
});

export const changeTeamName = teamName=>({
    type:TEAM_PRODUCT_CHANGE_TEAM_NAME,
    payload:{teamName}
});

export const appendCheckedProducts = (products)=>({
    type: TEAM_PRODUCT_APPEND_CHECKED_PRODUCTS,
    payload:{products}
})

export const fetchProducts = (productName,delay=true)=>{
    let thunk = dispatch=>{
        agent.Crud.dropdown('product',productName).then(results=>{
            dispatch(loadedProducts(results))
        });
    }

    if(delay)thunk.meta = {
        debounce: {
            time: 300,
            key:SEARCHING_RECORDS
        }
    }

    return thunk;
}

export const fetchPrincipals = (principal)=>dispatch=>{
    dispatch(loadedPrincipal(principal));

    agent.TeamProduct.loadPrincipal(principal).then((data)=>{
        dispatch(loadedProducts(data));
    });
}

export const fetchTeams = (teamName,delay=true)=>{
    let thunk = dispatch=>{
        agent.Crud.dropdown('team',teamName).then(results=>{
            dispatch(loadedTeams(results))
        })
    }

    if(delay)thunk.meta = {
        debounce: {
            time: 300,
            key:SEARCHING_RECORDS
        }
    }

    return thunk;
}

export const clearPage = ()=>({
    type:TEAM_PRODUCT_CLEAR_PAGE
})

export const save = (teams,products)=>dispatch=>{
    agent.TeamProduct.save(teams,products).then(({success,message})=>{
        if(success){
            dispatch(alertDialog(message,'success'));
            dispatch(clearPage())
        }
    }).catch(err=>{
        dispatch(alertDialog(err.response.data.message,'error'));
    })
}

export const load = (team)=>dispatch=>{
    agent.TeamProduct.load(team).then(({products})=>{
        dispatch(appendCheckedProducts(products))
    })
}