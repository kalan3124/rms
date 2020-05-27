import React, { Component } from "react";
import { connect } from 'react-redux';
import PropTypes from "prop-types";
// import ReactHTMLTableToExcel from 'react-html-table-to-excel';
// import moment from 'moment';

import Paper from "@material-ui/core/Paper";
import Typography from "@material-ui/core/Typography";
import withStyles from "@material-ui/core/styles/withStyles";
import Divider from "@material-ui/core/Divider";
import Grid from "@material-ui/core/Grid";
import Button from "@material-ui/core/Button";
import Table from "@material-ui/core/Table";
import TableBody from "@material-ui/core/TableBody";
import TableRow from "@material-ui/core/TableRow";
import TableCell from "@material-ui/core/TableCell";

import Layout from "../../../App/Layout";
import AjaxDropdown from "../../../CrudPage/Input/AjaxDropdown";
import { fetchTypes, changeValue, fetchData } from "../../../../actions/Medical/YtdSalesSheetReport";
import YtdSalesReport from "./YtdSalesReport";
import TableHeader from "./YtdMonthlyTableHeader";
import { MEDICAL_FIELD_MANAGER_TYPE } from '../../../../constants/config';


const styles = theme => ({
    padding: {
        padding: theme.spacing.unit
    },
    button: {
        marginTop: theme.spacing.unit,
        float: "right"
    },
    darkCell: {
        background: theme.palette.grey[600],
        color: theme.palette.common.white,
        border: '1px solid #fff'
    },
    table: {
        marginTop: theme.spacing.unit
    },
    lightCell: {
        border: '1px solid ' + theme.palette.grey[500]
    },
    excelRow: {
        display: 'none'
    },
    summaryTable: {
        width: '150px'
    },
    excelTitle: {
        fontWeight: 'bold'
    },
    pagination: {

    }
})


const mapStateToProps = state => ({
    ...state.YtdSalesSheetReport,
    ...state.App
});

const mapDispatchToProps = dispatch => ({
    onChangeValue: (name, value) => dispatch(changeValue(name, value)),
    onSubmit: (values) => dispatch(fetchData(values))
})

class YtdSalesSheetReport extends Component {

    constructor(props) {
        super(props);
        this.handleSubmitForm = this.handleSubmitForm.bind(this);
    }

    handleSubmitForm() {
        const { onSubmit, values } = this.props;
        onSubmit(values);
    }

    changeFormValue(name) {
        const { onChangeValue } = this.props;
        return value => {
            onChangeValue(name, value);
        }
    }

    render() {
        const { classes, values, resultCount, page, perPage, onChangePage, onChangeRowCount } = this.props;

        return (
            <Layout sidebar>
                <Paper className={classes.padding} >
                    <Typography variant="h5" align="center">YTD Monthly Sales Report</Typography>
                    <Divider />
                    <Grid container>
                        <Grid className={classes.padding} item md={5}>
                            <AjaxDropdown value={values.team} where={{ fm_id: '{fm_id}' }} otherValues={{ fm_id: values.fm_id }} label="Team" onChange={this.changeFormValue('team')} link="team" name="tm_id" />
                        </Grid>
                        <Grid className={classes.padding} item md={5}>
                            <AjaxDropdown value={values.fm_id} where={{ u_tp_id: MEDICAL_FIELD_MANAGER_TYPE }} onChange={this.changeFormValue('fm_id')} label="Field Manager" link="User" name="fm_id" />
                        </Grid>
                        <Grid item md={10}>
                            <Divider />
                            <Button onClick={this.handleSubmitForm} className={classes.button} variant="contained" color="primary" >Search</Button>
                        </Grid>
                    </Grid>
                </Paper>

                <Table className={classes.table}>
                    <TableHeader className={classes.darkCell} />
                    <TableBody>
                        {this.renderMonthlySalesTableValue()}
                    </TableBody>
                </Table>
                <br />
                <Table className={classes.table}>
                    <YtdSalesReport className={classes.darkCell} />
                    <TableBody>
                        {this.renderYtdSalesTableValue()}
                    </TableBody>
                </Table>
                {/* <TablePagination
                    className={classes.pagination}
                    rowsPerPageOptions={[5, 10, 25, 1]}
                    component="div"
                    count={resultCount}
                    rowsPerPage={perPage}
                    page={page}
                    backIconButtonProps={{
                        'aria-label': 'Previous Page',
                    }}
                    nextIconButtonProps={{
                        'aria-label': 'Next Page',
                    }}
                    onChangePage={onChangePage}
                    onChangeRowsPerPage={onChangeRowCount}
                  /> */}
            </Layout>
        );
    }

    renderMonthlySalesTableValue() {
        const { searched, rowData, classes } = this.props;

        if (!searched)
            return null;

        return rowData.map((row, i) => (
            <TableRow key={i} >
                <TableCell className={classes.lightCell}>
                    {row.pro_id}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.target_qty}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.achiev_qty}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.target_val}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.ach_val}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.ach_}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.defict_qty}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.value}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.last_yearSameMonth}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.growth_}
                </TableCell>
            </TableRow>
        ))
    }

    renderYtdSalesTableValue() {
        const { searched, rowDataNew, classes } = this.props;

        if (!searched)
            return null;

        return rowDataNew.map((row, i) => (
            <TableRow key={i} >
                <TableCell className={classes.lightCell}>
                    {row.pro_id}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.target_qty}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.achiev_qty}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.target_val}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.ach_val}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.ach_}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.defict_qty}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.value}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.last_yearSameMonth}
                </TableCell>
                <TableCell className={classes.lightCell}>
                    {row.growth_}
                </TableCell>
            </TableRow>
        ))
    }
}

const numberPropType = PropTypes.oneOfType([
    PropTypes.string,
    PropTypes.number
])

YtdSalesSheetReport.propTypes = {
    classes: PropTypes.shape({
        padding: PropTypes.string,
        button: PropTypes.string,
        darkCell: PropTypes.string
    }),

    rowData: PropTypes.array,
    searched: PropTypes.bool,

    values: PropTypes.object,
    onChangeValue: PropTypes.func,
    rowDataNew: PropTypes.array,

    perPage: numberPropType,
    page: numberPropType,
    resultCount: numberPropType,
    onChangePage: PropTypes.func,
    onChangeRowCount: PropTypes.func
}

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(YtdSalesSheetReport));