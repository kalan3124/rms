import React, { Component } from "react";
import { connect } from "react-redux";
import PropTypes from "prop-types";
import {Link} from "react-router-dom";
import Layout from "../../App/Layout";
import SearchAndCheckPanel from "./SearchAndCheckPanel";
import { fetchPrincipals,changeProductName, changeTeamName, fetchProducts, fetchTeams, changeCheckedTeams, changeCheckedProducts, clearPage, save, load } from "../../../actions/Medical/TeamProduct";

import Grid from "@material-ui/core/Grid";
import withStyles from "@material-ui/core/styles/withStyles";
import ShoppingBasketIcon from "@material-ui/icons/ShoppingBasket";
import SupervisorAccountIcon from "@material-ui/icons/SupervisorAccount";
import Typography from "@material-ui/core/Typography";
import Divider from "@material-ui/core/Divider";
import Toolbar from "@material-ui/core/Toolbar";
import Button from "@material-ui/core/Button";
import SaveIcon from "@material-ui/icons/Save";
import CloseIcon from "@material-ui/icons/Close";
import CloudUploadIcon from "@material-ui/icons/CloudUpload";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";

const styles = theme => ({
    grow:{
        flexGrow:1
    },
    button:{
        marginLeft:theme.spacing.unit,
        color:theme.palette.common.white+" !important"
    },
    zIndex: {
        zIndex: 1200
    }
})

const mapStateToProps = state=>({
    ...state.TeamProduct
});

const mapDispatchToProps = dispatch=>({
    onChangeProductName:productName=>dispatch(changeProductName(productName)),
    onChangeTeamName:teamName=>dispatch(changeTeamName(teamName)),
    onSearchProducts:(keyword,delay)=>dispatch(fetchProducts(keyword,delay)),
    onSearchTeams:(keyword,delay)=>dispatch(fetchTeams(keyword,delay)),
    onChangeCheckedTeams:teams=>dispatch(changeCheckedTeams(teams)),
    onChangeCheckedProducts:products=>dispatch(changeCheckedProducts(products)),
    onClearPage:()=>dispatch(clearPage()),
    onSave:(teams,products)=>dispatch(save(teams,products)),
    onLoad:(team)=>dispatch(load(team)),
    onChangePrincipal: principal=>dispatch(fetchPrincipals(principal))
})

class TeamProduct extends Component {

    constructor(props){
        super(props);

        this.state = {
            error:''
        }

        props.onSearchProducts("",false);
        props.onSearchTeams("",false);
        this.handleProductNameChange = this.handleProductNameChange.bind(this);
        this.handleTeamNameChange = this.handleTeamNameChange.bind(this);
        this.handleTeamChecked = this.handleTeamChecked.bind(this);
        this.handleProductChecked = this.handleProductChecked.bind(this);
        this.handleSaveButtonClick  = this.handleSaveButtonClick.bind(this);
        this.handleChangePrincipal = this.handleChangePrincipal.bind(this);
    }

    handleChangePrincipal(principal){
        const {onChangePrincipal} = this.props;
        this.state.error = "";
        onChangePrincipal(principal);
    }

    handleTeamNameChange(value){
        const {onChangeTeamName,onSearchTeams} = this.props;

        onChangeTeamName(value);
        onSearchTeams(value)
    }

    handleTeamChecked(item,checked){
        const {teamChecked,onChangeCheckedTeams,onLoad,principal} = this.props;

        let modedTeamChecked = teamChecked.filter(({value})=>value!=item.value);
        
        if(principal.value == undefined){
            this.state.error = "Principal field is required";
        } else {
            if(checked){
                onLoad(item);
                modedTeamChecked = [...modedTeamChecked,item];
            }
            this.state.error = "";
        }

        onChangeCheckedTeams(modedTeamChecked);
    }

    handleProductChecked(item,checked){
        const {productChecked,onChangeCheckedProducts} = this.props;

        let modedProductChecked = productChecked.filter(({value})=>value!=item.value);

        if(checked){
            modedProductChecked = [...modedProductChecked,item];
        }

        onChangeCheckedProducts(modedProductChecked);
    }

    handleProductNameChange(value){
        const {onChangeProductName,onSearchProducts,principal} = this.props;

        onChangeProductName(value);
        onSearchProducts(value);
    }

    handleSaveButtonClick(){
        const {teamChecked,productChecked,onSave} = this.props;

        onSave(teamChecked,productChecked);
    }

    render() {

        const {classes,principal,productName,teamName,productResults,teamResults,teamChecked,productChecked ,onClearPage} = this.props;
        const error_msg = {error:!!this.state.error};

        return (
            <Layout sidebar >
                <Toolbar className={classes.zIndex}>
                    <Typography className={classes.grow} variant="h5" align="center">Team Products Allocations</Typography>
                    <AjaxDropdown label="Principal" value={principal} onChange={this.handleChangePrincipal} link="principal" name="principal" helperText={this.state.error} {...error_msg}/>
                    <Button component={Link} to="/medical/other/upload_csv/team_product" margin="dense" className={classes.button} variant="contained" color="secondary">
                        <CloudUploadIcon/>
                        Upload
                    </Button>
                    <Button onClick={this.handleSaveButtonClick} margin="dense" className={classes.button} variant="contained" color="primary">
                        <SaveIcon/>
                        Save
                    </ Button>
                    <Button onClick={onClearPage} margin="dense" className={classes.button} variant="contained" color="secondary">
                        <CloseIcon/>
                        Cancel
                    </Button>
                </Toolbar>
                <Divider />
                <Grid container>
                    <Grid item md={7}>
                        <SearchAndCheckPanel 
                            icon={
                                <SupervisorAccountIcon/>
                            }
                            label="teams"
                            keyword={teamName}
                            onSearch={this.handleTeamNameChange}
                            results={teamResults}
                            onCheck={this.handleTeamChecked}
                            checked={teamChecked}
                        />
                    </Grid>
                    <Grid item md={5}>
                        <SearchAndCheckPanel
                            icon={
                                <ShoppingBasketIcon/>
                            }
                            label="products"
                            keyword={productName}
                            onSearch={this.handleProductNameChange}
                            results={productResults}
                            onCheck={this.handleProductChecked}
                            checked={productChecked}
                        />
                    </Grid>
                </Grid>
            </Layout>
        )
    }
}

const itemPropType =  PropTypes.arrayOf(PropTypes.shape({
    value:PropTypes.oneOfType([PropTypes.number,PropTypes.string]),
    label: PropTypes.string
}));

TeamProduct.propTypes = {
    classes: PropTypes.shape({
    }),

    onChangeProductName: PropTypes.func,
    productName: PropTypes.string,

    onChangeTeamName: PropTypes.func,
    teamName: PropTypes.string,

    onSearchProducts: PropTypes.func,
    onSearchTeams: PropTypes.func,
    onChangePrincipal: PropTypes.func,

    teamResults:itemPropType,
    productResults:itemPropType,
    teamChecked:itemPropType,
    productChecked:itemPropType
}

export default connect(mapStateToProps,mapDispatchToProps)( withStyles(styles)(TeamProduct));