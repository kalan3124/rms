import React, { Component } from "react";
import { connect } from "react-redux";
import PropTypes from "prop-types";

import Paper from "@material-ui/core/Paper";
import {Link} from "react-router-dom";
import Grid from "@material-ui/core/Grid";
import Typography from "@material-ui/core/Typography";
import Divider from "@material-ui/core/Divider";
import withStyles from "@material-ui/core/styles/withStyles";
import TextField from "@material-ui/core/TextField";
import Toolbar from "@material-ui/core/Toolbar";
import Button from "@material-ui/core/Button";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import AppBar from "@material-ui/core/AppBar";
import Tabs from "@material-ui/core/Tabs";
import Tab from "@material-ui/core/Tab";
import Menu from '@material-ui/core/Menu';
import MenuItem from '@material-ui/core/MenuItem';
import MenuList from '@material-ui/core/MenuList';
import ListItemSecondaryAction from "@material-ui/core/ListItemSecondaryAction";
import CloudUploadIcon from "@material-ui/icons/CloudUpload";
import SaveIcon from "@material-ui/icons/Save";
import CloseIcon from "@material-ui/icons/Close";

import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import DatePicker from "../../CrudPage/Input/DatePicker";
import { MEDICAL_REP_TYPE,PRODUCT_SPECIALIST_TYPE } from "../../../constants/config";
import { changeType, changeRep, changeMainValue, changeMainQty, fetchData, changeItemTarget, clearPage, saveForm, changeMonth, openTargetMenu, closeTargetMenu } from "../../../actions/Medical/Target";
import Layout from "../../App/Layout";
import { changeUser } from "../../../actions/Medical/UserAllocation";

const styles = theme => ({
    paper: {
        padding: theme.spacing.unit,
        margin: theme.spacing.unit
    },
    padding: {
        padding: theme.spacing.unit
    },
    darkGrey: {
        background: theme.palette.grey[400],
        height: '48vh',
        overflowY:"auto",
        border:"solid 1px "+theme.palette.grey[400],
        borderTop:"solid 0px"
    },
    button: {
        margin: theme.spacing.unit
    },
    grow: {
        flexGrow: 1
    },
    listItem: {
        background: theme.palette.common.white
    },
    listTextFieldAmount:{
        margin:4,
        maxWidth:150
    },
    listTextFieldQty:{
        margin:4,
        maxWidth:80
    },
    whiteFont:{
        color:theme.palette.white
    }
});

const mapStateToProps = state => ({
    ...state.Target,
    ...state.UserAllocation
});

const mapDispatchToProps = dispatch => ({
    onChangeType: type => dispatch(changeType(type)),
    onChangeRep: user => dispatch(changeUser(user,'user_target')),
    onChangeMainValue: ({ currentTarget }) => dispatch(changeMainValue(currentTarget.value)),
    onChangeMainQty: ({ currentTarget }) => dispatch(changeMainQty(currentTarget.value)),
    onLoadData: (rep,month) => dispatch(fetchData(rep,month)),
    onChangeItemTarget:(itemType,itemId,type,value,price)=>dispatch(changeItemTarget(itemType,itemId,type,value,price)),
    onClear:()=>dispatch(clearPage()),
    onSave:(rep,mainValue,mainQty,products,brands,principals,month)=>dispatch(saveForm(rep,mainValue,mainQty,products,brands,principals,month)),
    onChangeMonth:month=>dispatch(changeMonth(month)),
    onOpenTargetMenu:(ref)=>dispatch(openTargetMenu(ref)),
    onCloseTargetMenu:()=>dispatch(closeTargetMenu())
});

class Target extends Component {

    constructor(props) {
        super(props);

        this.handleTypeChange = this.handleTypeChange.bind(this);
        this.handleChangeRep = this.handleChangeRep.bind(this);
        this.handleSave = this.handleSave.bind(this);
        this.handleChangeMonth =this.handleChangeMonth.bind(this);

        if(props.user&&props.user.label){
            this.handleChangeRep(props.user);
        }
    }

    componentDidUpdate(prevProps){
        if(JSON.stringify(prevProps.user)!=JSON.stringify(this.props.user)&&prevProps.path!=this.props.path){
            this.handleChangeRep(this.props.user)
        }
    }

    handleTypeChange(e, tab) {
        const { onChangeType } = this.props;

        onChangeType(tab)
    }
    
    handleChangeRep(rep) {
        const { onLoadData, onChangeRep,month } = this.props;

        onLoadData(rep,month);
        onChangeRep(rep);
    }

    handleSave(){
        const {mainValue,mainQty,user,products,brands,principals,onSave,month} = this.props;

        const mainTarget = this.getMainTargets();

        onSave(user,mainTarget.value,mainTarget.qty,products,brands,principals,month);
    }

    handleChangeTarget(itemId,targetType,price){
        const {type,onChangeItemTarget} = this.props;
        return ({currentTarget})=>{
            onChangeItemTarget(type,itemId,targetType,currentTarget.value,price);
        }
    }

    renderItems() {
        const {classes,type,products,brands,principals} = this.props;

        let items = [];

        switch (type) {
            case 'product':
                items = products;
                break;
            case 'brand':
                items = brands;
                break;
            case 'principal':
                items = principals;
                break;
        }

        return Object.keys(items).map(itemId=>{
            const {label,valueTarget,qtyTarget,price} = items[itemId];

            return (
                <ListItem className={classes.listItem} divider key={itemId}>
                    <ListItemText primary={label}/>
                    <ListItemSecondaryAction>
                        <TextField onChange={this.handleChangeTarget(itemId,"value",price)} className={classes.listTextFieldAmount} value={valueTarget} margin="dense" label="Value" variant="outlined" />
                        <TextField onChange={this.handleChangeTarget(itemId,"qty",price)} className={classes.listTextFieldQty} value={qtyTarget} margin="dense" label="Qty" variant="outlined" />
                    </ListItemSecondaryAction>
                </ListItem>
            )

        })
    }

    handleChangeMonth(value){
        const {onChangeMonth,onLoadData,user} = this.props;

        onChangeMonth(value);
        
        if(typeof user=='undefined'||!user)
            return;

        onLoadData(user,value)
    }

    getMainTargets(){
        const {products,brands,principals,type} = this.props;

        let items = [];

        switch (type) {
            case 'product':
                items = products;
                break;
            case 'brand':
                items = brands;
                break;
            case 'principal':
                items = principals;
                break;
        }

        let value = 0;
        let qty = 0;

        for(const itemId of Object.keys(items)){
            const {valueTarget,qtyTarget} = items[itemId];

            if(valueTarget){
                value += parseFloat(valueTarget);
            }

            if(qtyTarget){
                qty += parseFloat(qtyTarget);
            }
        }

        return {value,qty};
    }

    render() {
        const {
            classes,
            type,
            user,
            onClear,
            month,
            uploadMenuRef,
            onCloseTargetMenu,
            onOpenTargetMenu
        } = this.props;

        const target = this.getMainTargets();

        return (
            <Layout sidebar>
                <Grid container>
                    <Grid item md={8}>
                        <Paper className={classes.paper}>
                            <Typography variant="h5" align="center">Target allocations</Typography>
                            <Divider />
                            <Grid container>
                                <Grid className={classes.padding} md={6} item>
                                    <AjaxDropdown value={user} onChange={this.handleChangeRep} link="user" label="Medical Representative/PS" where={{ u_tp_id: MEDICAL_REP_TYPE+'|'+PRODUCT_SPECIALIST_TYPE }} />
                                </Grid>
                                <Grid className={classes.padding} md={6} item>
                                    <DatePicker value={month} onChange={this.handleChangeMonth} label="Month" />
                                </Grid>
                            </Grid>
                            <Divider />
                            <Grid container>
                                <Grid className={classes.padding} item md={6}>
                                    <TextField
                                        label="Total Value"
                                        variant="outlined"
                                        margin="dense"
                                        type="number"
                                        step="0.01"
                                        fullWidth
                                        value={target.value}
                                        readOnly
                                    />
                                </Grid>
                                <Grid className={classes.padding} item md={6}>
                                    <TextField
                                        label="Total Qty"
                                        variant="outlined"
                                        margin="dense"
                                        type="number"
                                        fullWidth
                                        value={target.qty}
                                        readOnly
                                    />
                                </Grid>
                            </Grid>
                            <AppBar position="static" color="primary" >
                                <Toolbar variant="dense">
                                    <Tabs onChange={this.handleTypeChange} value={type}>
                                        <Tab value="product" label="Products" />
                                        <Tab value="brand" label="Brands" />
                                        <Tab value="principal" label="Principal" />
                                    </Tabs>
                                </Toolbar>
                            </AppBar>
                            <div className={classes.darkGrey} >
                                <List >
                                    {this.renderItems()}
                                </List>
                            </div>
                            <Toolbar className={classes.whiteFont} variant="dense">
                                <Button
                                    variant="contained"
                                    color="secondary"
                                    className={classes.button}
                                    onClick={e=>onOpenTargetMenu(e.currentTarget)}
                                >
                                    <CloudUploadIcon />
                                    Upload
                                </Button>
                                <Menu anchorEl={uploadMenuRef} onClose={onCloseTargetMenu} open={!!uploadMenuRef}>
                                    <MenuList>
                                        <MenuItem onClick={onCloseTargetMenu} >
                                            <Link to="/medical/other/upload_csv/mdd_product_target" >MDD Product Target</Link>
                                        </MenuItem>
                                        <MenuItem onClick={onCloseTargetMenu} >
                                            <Link to="/medical/other/upload_csv/mdd_brand_target" >MDD Brand Target</Link>
                                        </MenuItem>
                                        <MenuItem onClick={onCloseTargetMenu} >
                                            <Link to="/medical/other/upload_csv/mdd_principal_target" >MDD Principal Target</Link>
                                        </MenuItem>
                                        <MenuItem onClick={onCloseTargetMenu} >
                                            <Link to="/medical/other/upload_csv/pharma_product_target" >Pharma Product Target</Link>
                                        </MenuItem>
                                    </MenuList>
                                </Menu>
                                <div className={classes.grow} />
                                <Button
                                    variant="contained"
                                    color="primary"
                                    className={classes.button}
                                    onClick={this.handleSave}
                                >
                                    <SaveIcon />
                                    Save
                                </Button>
                                <Button
                                    variant="contained"
                                    color="secondary"
                                    className={classes.button}
                                    onClick={onClear}
                                >
                                    <CloseIcon />
                                    Cancel
                                </Button>
                            </Toolbar>
                        </Paper>
                    </Grid>
                </Grid>
            </Layout>
        );
    }
}

Target.propTypes = {
    classes: PropTypes.shape({
        paper: PropTypes.string,
        padding: PropTypes.string,
        darkGrey: PropTypes.string,
        button: PropTypes.string,
        grow: PropTypes.string,
        listItem: PropTypes.string,
    }),
    onChangeType: PropTypes.func,
    type: PropTypes.string,

    rep: PropTypes.shape({
        value: PropTypes.number,
        label: PropTypes.string
    }),
    onChangeRep: PropTypes.func,

    mainValue: PropTypes.oneOfType([
        PropTypes.string,
        PropTypes.number
    ]),
    onChangeMainValue: PropTypes.func,

    mainQty: PropTypes.oneOfType([
        PropTypes.string,
        PropTypes.number
    ]),
    onChangeMainQty: PropTypes.func,

    onLoadData: PropTypes.func,

    products:PropTypes.object,
    brands: PropTypes.object,

    onChangeItemTarget: PropTypes.func
}

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(Target));