import React, { Component } from "react";
import { connect } from "react-redux";
import PropTypes from "prop-types";

import Paper from "@material-ui/core/Paper";
import { Link } from "react-router-dom";
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
import ListItemSecondaryAction from "@material-ui/core/ListItemSecondaryAction";
import CloudUploadIcon from "@material-ui/icons/CloudUpload";
import SaveIcon from "@material-ui/icons/Save";
import CloseIcon from "@material-ui/icons/Close";

import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import DatePicker from "../../CrudPage/Input/DatePicker";
import { SALES_REP_TYPE } from "../../../constants/config";
import { fetchData, changeItemTarget, clearPage, saveForm, changeMonth } from "../../../actions/Sales/Target";
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
        height: '60vh',
        overflowY: "auto",
        border: "solid 1px " + theme.palette.grey[400],
        borderTop: "solid 0px"
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
    listTextFieldAmount: {
        margin: 4,
        maxWidth: 150
    },
    listTextFieldQty: {
        margin: 4,
        maxWidth: 80
    },
    whiteFont: {
        color: theme.palette.white
    }
});

const mapStateToProps = state => ({
    ...state.SalesTarget,
    ...state.UserAllocation
});

const mapDispatchToProps = dispatch => ({
    onChangeRep: user => dispatch(changeUser(user, 'user_target')),
    onChangeMonth: month => dispatch(changeMonth(month)),
    onLoadData: (rep, month) => dispatch(fetchData(rep, month)),
    onChangeItemTarget: (itemType, itemId, type, value, price) => dispatch(changeItemTarget(itemType, itemId, type, value, price)),
    onClear: () => dispatch(clearPage()),
    onSave: (rep, products, month) => dispatch(saveForm(rep, products, month))
});

class Target extends Component {

    constructor(props) {
        super(props);

        this.handleChangeRep = this.handleChangeRep.bind(this);
        this.handleSave = this.handleSave.bind(this);
        this.handleChangeMonth = this.handleChangeMonth.bind(this);

        if (props.user && props.user.label) {
            this.handleChangeRep(props.user);
        }
    }

    handleChangeRep(rep) {
        const { onLoadData, onChangeRep, month } = this.props;

        onLoadData(rep, month);
        onChangeRep(rep);
    }

    handleSave() {
        const { user, products, onSave, month } = this.props;

        onSave(user, products, month);
    }

    handleChangeTarget(itemId, targetType, price) {
        const { type, onChangeItemTarget } = this.props;
        return ({ currentTarget }) => {
            onChangeItemTarget(type, itemId, targetType, currentTarget.value, price);
        }
    }

    handleChangeMonth(value) {
        const { onChangeMonth, onLoadData, user } = this.props;

        onChangeMonth(value);

        if (typeof user == 'undefined' || !user)
            return;

        onLoadData(user, value)

    }

    getMainTargets() {
        const { products } = this.props;

        let items = [];
        items = products;
        let value = 0;

        for (const itemId of Object.keys(items)) {
            const { valueTarget, qtyTarget } = items[itemId];

            if (valueTarget) {
                value += parseFloat(valueTarget);
            }
        }

        return { value };
    }

    renderItems() {
        const { classes, products } = this.props;

        let items = [];
        items = products;

        return Object.keys(items).map(itemId => {
            const { label, valueTarget, qtyTarget, price } = items[itemId];

            return (
                <ListItem className={classes.listItem} divider key={itemId}>
                    <ListItemText primary={label} />
                    <ListItemSecondaryAction>
                        <TextField margin="dense" className={classes.listTextFieldAmount} value={price} label="Budget Price" variant="outlined" />
                        <TextField margin="dense" onChange={this.handleChangeTarget(itemId, "qty", price)} className={classes.listTextFieldQty} value={qtyTarget} label="Qty" variant="outlined" />
                        <TextField margin="dense" className={classes.listTextFieldAmount} value={valueTarget} label="Value" variant="outlined" readOnly />
                    </ListItemSecondaryAction>
                </ListItem>
            )
        })
    }

    render() {
        const {
            classes,
            user,
            onClear,
            month,
            valueTarget
        } = this.props;

        const target = this.getMainTargets();

        return (
            <Layout sidebar>
                <Grid container>
                    <Grid item md={12}>
                        <Paper className={classes.paper}>
                            <Typography variant="h5" align="center">Target allocations</Typography>
                            <Divider />
                            <Grid container>
                                <Grid className={classes.padding} md={6} item>
                                    <AjaxDropdown
                                        value={user}
                                        onChange={this.handleChangeRep}
                                        link="user"
                                        label="Sales Representative"
                                        where={{ u_tp_id: SALES_REP_TYPE }} />
                                </Grid>
                                <Grid className={classes.padding} md={6} item>
                                    <DatePicker
                                        value={month}
                                        onChange={this.handleChangeMonth}
                                        label="Month" />
                                </Grid>
                                <Grid className={classes.padding} md={6} item>
                                    <TextField style={{ backgroundColor: 'white' }} margin="dense" value={target.value} label="Total Target" variant="outlined" InputProps={{
                                        readOnly: true,
                                    }} />
                                </Grid>
                            </Grid>
                            <AppBar position="static" color="primary" >
                                <Toolbar variant="dense">
                                    
                                </Toolbar>
                            </AppBar>
                            <Grid item md={12}>
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
                                        component={Link}
                                        to="/sales/other/upload_csv/sr_target"
                                    >
                                        <CloudUploadIcon />
                                        Upload
                            </Button>
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
                            </Grid>
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

    onLoadData: PropTypes.func,

    products: PropTypes.object,

    onChangeItemTarget: PropTypes.func
}

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(Target));