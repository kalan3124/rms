import React, { Component } from "react";
import { connect } from 'react-redux';
import PropTypes from "prop-types";
import ReactHTMLTableToExcel from 'react-html-table-to-excel';
import moment from 'moment';

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

import AjaxDropdown from "../../../CrudPage/Input/AjaxDropdown";
import DatePicker from "../../../CrudPage/Input/DatePicker";
import TableHeader from "./TableHeader";
import KiloTableHeader from "./KiloTableHeader";
import { fetchTypes, changeValue, fetchData } from "../../../../actions/Medical/ExpenceStatement";
import Layout from "../../../App/Layout";
import Fab from "@material-ui/core/Fab";
import TableIcon from "@material-ui/icons/TableChart";
import agent from "../../../../agent";
import { APP_URL } from "../../../../constants/config";

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
        fontWeight: 'bold',
        display: 'none'
    },
    newCell: {
        background: theme.palette.grey[300],
        color: theme.palette.common.black,
        border: '1px solid ' + theme.palette.grey[500]
    },
    totalCell: {
        background: '#9ce1ff',
        color: theme.palette.common.black,
        border: '1px solid ' + theme.palette.grey[500]
    }
})

const mapStateToProps = state => ({
    ...state.ExpenceStatement,
    ...state.App
});

const mapDispatchToProps = dispatch => ({
    onLoadTypes: () => dispatch(fetchTypes()),
    onChangeValue: (name, value) => dispatch(changeValue(name, value)),
    onSubmit: (values) => dispatch(fetchData(values))
})

class ExpenceStatement extends Component {

    constructor(props) {
        super(props);
        this.state = {
            mrName: '',
            teamName: '',
            sDate: '',
            eDate: ''
        };
        props.onLoadTypes();
        this.handleSubmitForm = this.handleSubmitForm.bind(this);
        this.handleClickDownloadXLSX = this.handleClickDownloadXLSX.bind(this);
    }

    handleClickDownloadXLSX() {
        const { values } = this.props;

        agent.Report.saveAsFile('xlsx', 'expenses_statement', { values }).then(({ file }) => {
            window.open(APP_URL + 'storage/xlsx/' + file);
        })
    }

    render() {
        const { classes, values } = this.props;

        return (
            <Layout sidebar>
                <Paper className={classes.padding} >
                    <Typography variant="h5" align="center">Expense Statement Reports</Typography>
                    <Divider />
                    <Grid container>
                        <Grid className={classes.padding} item md={4}>
                            <AjaxDropdown where={{ divi_id: '{divi_id}' }} otherValues={{ divi_id: values.division }} value={values.team} label="Team" onChange={this.changeFormValue('team')} link="team" name="tm_id" />
                        </Grid>
                        <Grid className={classes.padding} item md={4}>
                            <AjaxDropdown value={values.division} label="Division" onChange={this.changeFormValue('division')} name="division" link="division" />
                        </Grid>
                        <Grid className={classes.padding} item md={4}>
                            <AjaxDropdown where={{ tm_id: '{tm_id}', divi_id: '{divi_id}' }} otherValues={{ tm_id: values.team, divi_id: values.division }} value={values.user} label="PS/MR And FM" onChange={this.changeFormValue('user')} link="user" name="u_id" />
                        </Grid>
                        <Grid className={classes.padding} item md={6}>
                            <DatePicker value={values.s_date} label="From" onChange={this.changeFormValue('s_date')} name="s_date" />
                        </Grid>
                        <Grid className={classes.padding} item md={6}>
                            <DatePicker value={values.e_date} label="To" onChange={this.changeFormValue('e_date')} name="e_date" />
                        </Grid>
                        <Grid item md={10}>
                            <Divider />
                            <Button onClick={this.handleSubmitForm} className={classes.button} variant="contained" color="primary" >Search</Button>
                        </Grid>
                    </Grid>
                </Paper>
                {this.renderTable()}
            </Layout>
        );
    }

    handleSubmitForm() {
        const { onSubmit, values } = this.props;
        this.setState({ mrName: values.user ? values.user.label : '' });
        this.setState({ teamName: values.team ? values.team.label : '' });
        this.setState({ sDate: values.s_date });
        this.setState({ eDate: values.e_date });
        onSubmit(values);
    }

    changeFormValue(name) {
        const { onChangeValue } = this.props;
        return value => {
            onChangeValue(name, value);
        }
    }

    renderTable() {
        const { searched, classes, types, bataCategories, user } = this.props;
        var new_color_headers = {
            color: 'white',
            background: '#7e8387',
            border: '1px solid #fff'
        }

        var align_new = {
            textAlign: 'center',
            background: 'grey'
        }

        if (!searched)
            return null;

        return (
            <div>
                <Table id="table-to-xls">
                    <TableBody>
                        <TableRow>
                            <TableCell colSpan="1">
                                <Typography variant="h2" style={align_new} className={classes.excelTitle}>Sunshine Health Care Lanka Ltd</Typography>
                            </TableCell>
                        </TableRow>
                        <TableRow className={classes.excelRow}>
                            <TableCell>
                                <Typography variant="h5" style={align_new} className={classes.excelTitle}>Monthly Expense Statement</Typography>
                            </TableCell>
                        </TableRow>
                        <TableRow className={classes.excelRow}>
                            <TableCell style={align_new} className={classes.excelTitle}>
                                Mrs & FMs Name : {this.state.mrName}
                            </TableCell>
                        </TableRow>
                        <TableRow className={classes.excelRow}>
                            <TableCell style={align_new} className={classes.excelTitle}>
                                Team / Div : {this.state.teamName}
                            </TableCell>
                        </TableRow>
                        <TableRow className={classes.excelRow}>
                            <TableCell style={align_new} className={classes.excelTitle}>
                                Expense Cycle : {this.state.sDate} to {this.state.eDate}
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell>
                                <div styles={{ width: "20px" }}></div>
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell>
                                <Table className={classes.table} padding="dense">
                                    <TableHeader bataCategories={bataCategories} types={types} style={new_color_headers} />
                                    <TableBody>
                                        {this.renderValues()}
                                    </TableBody>
                                </Table>
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell>
                                <div styles={{ width: "20px" }}></div>
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell>
                                <Table className={classes.summaryTable}>
                                    <KiloTableHeader style={new_color_headers} />
                                    {this.renderKMTable()}
                                </Table>
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell>
                                <div styles={{ width: "20px" }}></div>
                            </TableCell>
                        </TableRow>
                        <TableRow className={classes.excelRow}>
                            <TableCell>
                                <Table>
                                    <TableBody>
                                        <TableRow>
                                            <TableCell>
                                                Request By :- {user.name}
                                            </TableCell>
                                        </TableRow>
                                        <TableRow>
                                            <TableCell>
                                                Request On :- {moment().format("YYYY-MM-DD HH:mm:ss")}
                                            </TableCell>
                                        </TableRow>
                                        <TableRow>
                                            <TableCell>
                                                Approved By  .............................................................
                                            </TableCell>
                                        </TableRow>
                                        <TableRow>
                                            <TableCell>
                                                &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;Signature & Date
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <Fab
                    variant="extended"
                    size="small"
                    color="primary"
                    onClick={this.handleClickDownloadXLSX}
                >
                    <TableIcon fontSize="small" className={classes.icon} />
                    <Typography className={classes.typography} variant="caption">
                        Download As XLSX
                    </Typography>
                </Fab>
            </div>
        )
    }

    renderValues() {
        const { rowData, classes, day_mileage_limit } = this.props;

        var new_color = "";
        let limit = 0;
        let per = 0;
        return rowData.map((row, i) => (
            // limit = parseFloat(day_mileage_limit) + parseFloat(row[9]),
            per = parseFloat(day_mileage_limit) * parseFloat(row[9]) / 100,
            limit = per + parseFloat(row[9]),
            // console.log(limit),
            row[1] == "Grand Total" ? new_color = classes.newCell : new_color = classes.newCell,
            <TableRow key={i} style={{ background: limit < row[13] ? '#fab2ac' : null }}>
                {row.map((cell, j) => (
                    (j == 0 || j == 1 || j == 8 || j == 14 || j == 19) && row[1] != "Grand Total" ?
                        new_color = {
                            color: 'black',
                            background: '#e4e8f0',
                            border: '1px solid #fff'
                        }
                        :
                        row[1] == "Grand Total" ?
                            new_color = {
                                color: 'black',
                                background: '#add0f0',
                                border: '1px solid #e8e8e8'
                            }
                            :
                            j == 20 ?
                                new_color = {
                                    color: 'black',
                                    background: '#bcc0c4',
                                    border: '1px solid #e8e8e8'
                                }
                                : new_color = {
                                    color: 'black',
                                    background: 'fffefa',
                                    border: '1px solid #e8e8e8'
                                },
                    <TableCell style={new_color} key={j}>
                        {cell}
                    </TableCell>
                ))}
            </TableRow>
        ))
    }

    renderKMTable() {
        var offcial = 0;
        var add = 0;
        var tot = 0;
        var pvt = 0;

        var new_color_headers = {
            color: 'white',
            background: '#7e8387',
            border: '1px solid #fff'
        }

        const { rowData, classes, loadAddKm, loadPrivateKm } = this.props;
        rowData.forEach(element => {

            offcial = element[9];
            add = element[10];
            pvt = element[12];

            tot = offcial + loadPrivateKm + loadAddKm;
        })
        return (
            <TableBody>
                <TableRow className={classes.padding}>
                    <TableCell className={classes.lightCell}>
                        Offcial
                        </TableCell>
                    <TableCell className={classes.lightCell}>
                        {offcial}
                    </TableCell>
                </TableRow>
                <TableRow>
                    <TableCell className={classes.lightCell}>
                        Additional
                        </TableCell>
                    <TableCell className={classes.lightCell}>
                        {loadAddKm}
                    </TableCell>
                </TableRow>
                <TableRow>
                    <TableCell className={classes.lightCell}>
                        Private
                        </TableCell>
                    <TableCell className={classes.lightCell}>
                        {loadPrivateKm}
                    </TableCell>
                </TableRow>
                <TableRow>
                    <TableCell style={new_color_headers}>
                        Total
                        </TableCell>
                    <TableCell style={new_color_headers}>
                        {tot}
                    </TableCell>
                </TableRow>
            </TableBody>
        )
    }


}

ExpenceStatement.propTypes = {
    classes: PropTypes.shape({
        padding: PropTypes.string,
        button: PropTypes.string,
        darkCell: PropTypes.string
    }),
    types: PropTypes.object,
    onLoadTypes: PropTypes.func,

    rowData: PropTypes.array,
    searched: PropTypes.bool,


    values: PropTypes.object,
    onChangeValue: PropTypes.func,

    resultCount: PropTypes.oneOfType([
        PropTypes.string, PropTypes.number
    ]),

    loadAddKm: PropTypes.oneOfType([
        PropTypes.string, PropTypes.number
    ]),

    loadPrivateKm: PropTypes.oneOfType([
        PropTypes.string, PropTypes.number
    ]),

    bataTypes: PropTypes.array
}

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(ExpenceStatement));