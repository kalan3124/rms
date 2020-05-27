import React, { Component } from "react";
import { connect } from "react-redux";
import PropTypes from "prop-types";
import Typography from "@material-ui/core/Typography";

import Layout from "../../App/Layout";
import Divider from "@material-ui/core/Divider";
import Grid from "@material-ui/core/Grid";
import withStyles from "@material-ui/core/styles/withStyles";
import Toolbar from "@material-ui/core/Toolbar";
import Button from "@material-ui/core/Button";
import Paper from "@material-ui/core/Paper";
import TextField from "@material-ui/core/TextField";
import DatePicker from "../../CrudPage/Input/DatePicker";
import TableCell from "@material-ui/core/TableCell";
import TableRow from "@material-ui/core/TableRow";
import TableBody from "@material-ui/core/TableBody";
import Save from "@material-ui/icons/Save";
import CancelIcon from "@material-ui/icons/Cancel";
import Checkbox from "@material-ui/core/Checkbox";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import TableHead from "@material-ui/core/TableHead";
import Table from "@material-ui/core/Table";

import {
    SALES_REP_TYPE,
    AREA_SALES_MANAGER_TYPE
} from "../../../constants/config";

import {
    changeFrom,
    changeTo,
    fetchData,
    fetchEditData,
    changeFromEdit,
    changeToEdit,
    EditData
} from "../../../actions/Sales/Competitors";
import { log } from "util";

const styles = theme => ({
    paper: {
        padding: theme.spacing.unit,
        margin: theme.spacing.unit
    },
    padding: {
        padding: theme.spacing.unit
    },
    darkGrey: {
        background: theme.palette.grey[600],
        color: theme.palette.common.white,
        border: "1px solid #fff"
    },
    button: {
        margin: theme.spacing.unit
    },
    lightCell: {
        border: "1px solid " + theme.palette.grey[500]
    },
    backdrop: {
        right: 24,
        background: "unset"
    },
    modal: {
        backgroundColor: "rgba(0, 0, 0, 0.5)",
        paddingBottom: 40,
        overflow: "auto"
    },
    paperModal: {
        width: "40vw",
        minWidth: "400px",
        marginLeft: "30vw",
        marginTop: "40px",
        padding: theme.spacing.unit * 2
    }
});

const mapStateToProps = state => ({
    ...state.Competitors
});

const mapDispatchToProps = dispatch => ({
    onChangeFrom: from => dispatch(changeFrom(from)),
    onChangeTo: to => dispatch(changeTo(to)),
    onfetchData: (from, to) => dispatch(fetchData(from, to)),
    onfetchEditData: data => dispatch(fetchEditData(data)),
    onChangeFromEdit: fromEdit => dispatch(changeFromEdit(fromEdit)),
    onChangeToEdit: toEdit => dispatch(changeToEdit(toEdit)),
    onEditData: (id,from,to) => dispatch(EditData(id,from,to))
});

class Competitors extends Component {
    constructor(props) {
        super(props);

        this.handleChangeFrom = this.handleChangeFrom.bind(this);
        this.handleChangeTo = this.handleChangeTo.bind(this);

        this.handleChangeFromEdit = this.handleChangeFromEdit.bind(this);
        this.handleChangeToEdit = this.handleChangeToEdit.bind(this);
    }

    handleChangeFrom(e) {
        const { onChangeFrom } = this.props;
        onChangeFrom(e);
    }

    handleChangeTo(e) {
        const { onChangeTo } = this.props;
        onChangeTo(e);
    }

    onSearch() {
        const { from, to, onfetchData } = this.props;
        onfetchData(from, to);
    }

    onView(id) {
        const { onfetchEditData } = this.props;
        onfetchEditData(id);
    }

    handleChangeFromEdit(e) {
        const { onChangeFromEdit } = this.props;
        onChangeFromEdit(e);
    }

    handleChangeToEdit(e) {
        const { onChangeToEdit } = this.props;
        onChangeToEdit(e);
    }

    onSave(id){
        const { onEditData,fromEdit,toEdit } = this.props;
        onEditData(id,fromEdit,toEdit)
    }

    render() {
        const {
            classes,
            from,
            to,
            rowData,
            searched,
            searchedData
        } = this.props;

        return (
            <Layout sidebar>
                <Paper className={classes.padding}>
                    <Typography variant="h5" align="center">
                        Competitors Questionnaire
                    </Typography>

                    <Divider />
                    <Grid container>
                        <Grid className={classes.padding} md={3} item>
                            <DatePicker
                                value={from}
                                onChange={this.handleChangeFrom}
                                label="From"
                            />
                        </Grid>
                        <Grid className={classes.padding} md={3} item>
                            <DatePicker
                                value={to}
                                onChange={this.handleChangeTo}
                                label="To"
                            />
                        </Grid>
                        <Grid className={classes.padding} md={3} item>
                            <Button
                                className={classes.button}
                                variant="contained"
                                color="primary"
                                onClick={this.onSearch.bind(this)}
                            >
                                Search
                            </Button>
                        </Grid>
                    </Grid>
                    {searched ? this.renderTable() : null}
                    <br></br>
                    {searchedData ? this.renderEditTable() : null}
                    <br></br>
                    {searchedData ? this.renderCompetitorsTable() : null}
                </Paper>
            </Layout>
        );
    }

    renderTable() {
        const { classes, rowData } = this.props;

        return (
            <Table>
                <TableHead key={"newHeader"}>
                    <TableRow key={"newRow"}>
                        <TableCell
                            align="center"
                            padding="dense"
                            className={classes.darkGrey}
                            style={{ width: 300 }}
                        >
                            Chemist Name
                        </TableCell>
                        <TableCell
                            align="center"
                            padding="dense"
                            className={classes.darkGrey}
                            style={{ width: 300 }}
                        >
                            Contact 1
                        </TableCell>
                        <TableCell
                            align="center"
                            padding="dense"
                            className={classes.darkGrey}
                            style={{ width: 300 }}
                        >
                            Contact 2
                        </TableCell>
                        <TableCell
                            align="center"
                            padding="dense"
                            className={classes.darkGrey}
                            style={{ width: 300 }}
                        >
                            Email
                        </TableCell>
                        <TableCell
                            align="center"
                            padding="dense"
                            className={classes.darkGrey}
                            style={{ width: 300 }}
                        >
                            Owner Name
                        </TableCell>
                        <TableCell
                            align="center"
                            padding="dense"
                            className={classes.darkGrey}
                            style={{ width: 300 }}
                        >
                            Remark
                        </TableCell>
                        <TableCell
                            align="center"
                            padding="dense"
                            className={classes.darkGrey}
                            style={{ width: 300 }}
                        ></TableCell>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {Object.values(rowData).map((row, index) => {
                        return [
                            <TableRow key={index}>
                                <TableCell className={classes.lightCell}>
                                    {row.chemist_name}
                                </TableCell>
                                <TableCell className={classes.lightCell}>
                                    {row.con_1}
                                </TableCell>
                                <TableCell className={classes.lightCell}>
                                    {row.con_2}
                                </TableCell>
                                <TableCell className={classes.lightCell}>
                                    {row.email}
                                </TableCell>
                                <TableCell className={classes.lightCell}>
                                    {row.owner_name}
                                </TableCell>
                                <TableCell className={classes.lightCell}>
                                    {row.remark}
                                </TableCell>
                                <TableCell className={classes.lightCell}>
                                    <Button
                                        className={classes.button}
                                        variant="contained"
                                        color="secondary"
                                        onClick={this.onView.bind(this, row.id)}
                                    >
                                        View
                                    </Button>
                                </TableCell>
                            </TableRow>
                        ];
                    })}
                </TableBody>
            </Table>
        );
    }

    renderEditTable() {
        const { classes, data } = this.props;

        return (
            <Table>
                <TableHead key={"newHeader"}>
                    <TableRow key={"newRow"}>
                        {/* <TableCell
                            align="center"
                            padding="dense"
                            className={classes.darkGrey}
                            style={{ width: 300 }}
                        >
                            Iteam No
                        </TableCell> */}
                        <TableCell
                            align="center"
                            padding="dense"
                            className={classes.darkGrey}
                            style={{ width: 300 }}
                        >
                            Description
                        </TableCell>
                        <TableCell
                            align="center"
                            padding="dense"
                            className={classes.darkGrey}
                            style={{ width: 300 }}
                        >
                            Value
                        </TableCell>
                    </TableRow>
                </TableHead>
                <TableBody>
                    <TableRow key={9}>
                        {/* <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            1
                        </TableCell> */}
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            Chemist Name
                        </TableCell>
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            <TextField
                                type="text"
                                value={data[0] ? data[0].chemist_name : null}
                                // onChange={this.onHanleChangeDate(index)}
                                style={{ width: 500 }}
                                InputProps={{
                                    readOnly: true
                                }}
                            />
                        </TableCell>
                    </TableRow>
                    <TableRow key={1}>
                        {/* <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            2
                        </TableCell> */}
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            Owner / Manger's Name
                        </TableCell>
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            <TextField
                                type="text"
                                value={data[0] ? data[0].owner_name : null}
                                // onChange={this.onHanleChangeDate(index)}
                                style={{ width: 300 }}
                                InputProps={{
                                    readOnly: true
                                }}
                            />
                        </TableCell>
                    </TableRow>
                    <TableRow key={2}>
                        {/* <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            3
                        </TableCell> */}
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            Contact Person
                        </TableCell>
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            <TextField
                                type="text"
                                value={data[0] ? data[0].contact_person : null}
                                // onChange={this.onHanleChangeDate(index)}
                                style={{ width: 300 }}
                                InputProps={{
                                    readOnly: true
                                }}
                            />
                        </TableCell>
                    </TableRow>
                    <TableRow key={10}>
                        {/* <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            4
                        </TableCell> */}
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            Contact 1
                        </TableCell>
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            <TextField
                                type="text"
                                value={data[0] ? data[0].con_1 : null}
                                // onChange={this.onHanleChangeDate(index)}
                                style={{ width: 300 }}
                                InputProps={{
                                    readOnly: true
                                }}
                            />
                        </TableCell>
                    </TableRow>
                    <TableRow key={11}>
                        {/* <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            5
                        </TableCell> */}
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            Contact 1
                        </TableCell>
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            <TextField
                                type="text"
                                value={data[0] ? data[0].con_2 : null}
                                // onChange={this.onHanleChangeDate(index)}
                                style={{ width: 300 }}
                                InputProps={{
                                    readOnly: true
                                }}
                            />
                        </TableCell>
                    </TableRow>
                    <TableRow key={3}>
                        {/* <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            6
                        </TableCell> */}
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            Email Address
                        </TableCell>
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            <TextField
                                type="text"
                                value={data[0] ? data[0].email : null}
                                // onChange={this.onHanleChangeDate(index)}
                                style={{ width: 300 }}
                                InputProps={{
                                    readOnly: true
                                }}
                            />
                        </TableCell>
                    </TableRow>
                    <TableRow key={4}>
                        {/* <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            7
                        </TableCell> */}
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            Number Of Stuff
                        </TableCell>
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            <TextField
                                type="text"
                                value={data[0] ? data[0].noOfstuff : null}
                                // onChange={this.onHanleChangeDate(index)}
                                style={{ width: 300 }}
                                InputProps={{
                                    readOnly: true
                                }}
                            />
                        </TableCell>
                    </TableRow>
                    <TableRow key={5}>
                        {/* <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            8
                        </TableCell> */}
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            Total Purchases of the Pharmacy Per Month
                        </TableCell>
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            <TextField
                                type="text"
                                value={data[0] ? data[0].tot_pur_month : null}
                                // onChange={this.onHanleChangeDate(index)}
                                style={{ width: 300 }}
                                InputProps={{
                                    readOnly: true
                                }}
                            />
                        </TableCell>
                    </TableRow>
                    <TableRow key={6}>
                        {/* <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            9
                        </TableCell> */}
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            Purchases of Pharmaceuitical Product of the Pharmacy
                            Per Month
                        </TableCell>
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            <TextField
                                type="text"
                                value={
                                    data[0] ? data[0].pharmacy_pur_month : null
                                }
                                // onChange={this.onHanleChangeDate(index)}
                                style={{ width: 300 }}
                                InputProps={{
                                    readOnly: true
                                }}
                            />
                        </TableCell>
                    </TableRow>
                    <TableRow key={7}>
                        {/* <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            10
                        </TableCell> */}
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            value of SHL Product Brought (Purchased) from 3rd
                            party Distributors
                        </TableCell>
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            <TextField
                                type="text"
                                value={
                                    data[0]
                                        ? data[0].val_shl_pro_thirdPartyDis
                                        : null
                                }
                                // onChange={this.onHanleChangeDate(index)}
                                style={{ width: 300 }}
                                InputProps={{
                                    readOnly: true
                                }}
                            />
                        </TableCell>
                    </TableRow>
                    <TableRow key={8}>
                        {/* <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            11
                        </TableCell> */}
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            value of Total Pharmaceuitical Products
                            Redistributed
                        </TableCell>
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            <TextField
                                type="text"
                                value={
                                    data[0]
                                        ? data[0].val_tot_pro_Redistributed
                                        : null
                                }
                                // onChange={this.onHanleChangeDate(index)}
                                style={{ width: 300 }}
                                InputProps={{
                                    readOnly: true
                                }}
                            />
                        </TableCell>
                    </TableRow>
                    <TableRow key={12}>
                        {/* <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            12
                        </TableCell> */}
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            value of SHL Products Redistributed by the Pharmacy
                        </TableCell>
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            <TextField
                                type="text"
                                value={
                                    data[0]
                                        ? data[0].val_shl_pro_Redistributed
                                        : null
                                }
                                // onChange={this.onHanleChangeDate(index)}
                                style={{ width: 300 }}
                                InputProps={{
                                    readOnly: true
                                }}
                            />
                        </TableCell>
                    </TableRow>
                    <TableRow key={13}>
                        {/* <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            13
                        </TableCell> */}
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            Pharmacy Sales (Day)
                        </TableCell>
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            <TextField
                                type="text"
                                value={
                                    data[0] ? data[0].pharmacy_sales_day : null
                                }
                                // onChange={this.onHanleChangeDate(index)}
                                style={{ width: 300 }}
                                InputProps={{
                                    readOnly: true
                                }}
                            />
                        </TableCell>
                    </TableRow>
                    <TableRow key={14}>
                        {/* <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            14
                        </TableCell> */}
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            Pharmacy Sales (Month)
                        </TableCell>
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            <TextField
                                type="text"
                                value={
                                    data[0]
                                        ? data[0].pharmacy_sales_month
                                        : null
                                }
                                // onChange={this.onHanleChangeDate(index)}
                                style={{ width: 300 }}
                                InputProps={{
                                    readOnly: true
                                }}
                            />
                        </TableCell>
                    </TableRow>
                    <TableRow key={14}>
                        {/* <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            14
                        </TableCell> */}
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            Survey Time
                        </TableCell>
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            <TextField
                                type="text"
                                value={data[0] ? data[0].survey_time : "--"}
                                // onChange={this.onHanleChangeDate(index)}
                                style={{ width: 300 }}
                                InputProps={{
                                    readOnly: true
                                }}
                            />
                        </TableCell>
                    </TableRow>
                    <TableRow key={14}>
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 100 }}
                        >
                            Valid Date
                        </TableCell>
                        <TableCell
                            className={classes.lightCell}
                            style={{ width: 300 }}
                        >
                            From {data[0] ? data[0].from : null} To{" "}
                            {data[0] ? data[0].to : "--"}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        );
    }

    renderCompetitorsTable() {
        const { classes, comp,fromEdit,toEdit,data } = this.props;

        return (
            <Table>
                <TableHead key={"newHeader"}>
                    <TableRow key={"newRow"}>
                        <TableCell
                            align="center"
                            padding="dense"
                            className={classes.darkGrey}
                            style={{ width: 100 }}
                        >
                            Competitor Name
                        </TableCell>
                        <TableCell
                            align="center"
                            padding="dense"
                            className={classes.darkGrey}
                            style={{ width: 100 }}
                        >
                            Total Purchase Value
                        </TableCell>
                        <TableCell
                            align="center"
                            padding="dense"
                            className={classes.darkGrey}
                            style={{ width: 100 }}
                        >
                            Visit Frequency
                        </TableCell>
                        <TableCell
                            align="center"
                            padding="dense"
                            className={classes.darkGrey}
                            style={{ width: 100 }}
                        >
                            Visit Day of the Week
                        </TableCell>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {Object.values(comp).map((row, index) => {
                        return [
                            <TableRow key={index}>
                                <TableCell className={classes.lightCell}>
                                    {row.cmp_name}
                                </TableCell>
                                <TableCell className={classes.lightCell}>
                                    {row.total_purchase_value}
                                </TableCell>
                                <TableCell className={classes.lightCell}>
                                    {row.visit_frequency}
                                </TableCell>
                                <TableCell className={classes.lightCell}>
                                    {row.visit_day_Of_week}
                                </TableCell>
                            </TableRow>
                        ];
                    })}
                </TableBody><br></br>
                <Grid container>
                Change Valid Date
                    <Grid className={classes.padding} md={3} item>
                        <DatePicker
                            value={fromEdit}
                            onChange={this.handleChangeFromEdit}
                            label="From"
                        />
                    </Grid>
                    <Grid className={classes.padding} md={3} item>
                        <DatePicker
                            value={toEdit}
                            onChange={this.handleChangeToEdit}
                            label="To"
                        />
                    </Grid>
                    <Grid className={classes.padding} md={3} item>
                        <Button
                            className={classes.button}
                            variant="contained"
                            color="primary"
                            onClick={this.onSave.bind(this,data[0] ? data[0].id : null)}
                        >
                            Save
                        </Button>
                    </Grid>
                </Grid>
            </Table>
        );
    }
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(withStyles(styles)(Competitors));
