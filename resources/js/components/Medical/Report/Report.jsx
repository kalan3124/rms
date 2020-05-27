import React, { Component } from 'react';
import withRouter from 'react-router-dom/withRouter';
import { connect } from 'react-redux';


import Grid from '@material-ui/core/Grid';
import Table from '@material-ui/core/Table';
import TableRow from '@material-ui/core/TableRow';
import TableHead from '@material-ui/core/TableHead';
import TableCell from '@material-ui/core/TableCell';
import Tooltip from '@material-ui/core/Tooltip';
import TableSortLabel from '@material-ui/core/TableSortLabel';
import TableBody from '@material-ui/core/TableBody';
import withStyles from '@material-ui/core/styles/withStyles';
import Typography from '@material-ui/core/Typography';
import blue from '@material-ui/core/colors/blue';
import Paper from '@material-ui/core/Paper';

import Layout from '../../App/Layout';
import Form from './Form';
import Cell from '../../CrudPage/Cell/Cell';
import BottomPanel from '../../CrudPage/BottomPanel';
import { changeSearchTerms, changeReport, dialog } from '../../../actions/Medical/Report';
import agent from '../../../agent';
import { APP_URL } from '../../../constants/config';

const styles = theme => ({
    darkCell: {
        background: theme.palette.grey[600],
        color: theme.palette.common.white,
        border: 'solid 1px ' + theme.palette.common.white
    },
    fab: {
        width: theme.spacing.unit * 3,
        height: theme.spacing.unit * 3,
        lineHeight: theme.spacing.unit * 2,
        color: '#fff',
        margin: 4
    },
    actionCell: {
        padding: 0,
        borderLeft: 'solid 2px ' + theme.palette.grey[100],
        borderRight: 'solid 2px ' + theme.palette.grey[200],
        width: theme.spacing.unit * 6,
        textAlign: 'center'
    },
    normalCell: {
        paddingLeft: 4,
        paddingRight: 0,
        borderLeft: 'solid 2px ' + theme.palette.grey[100],
        borderRight: 'solid 2px ' + theme.palette.grey[200]
    },
    table: {
        marginTop: theme.spacing.unit * 2
    },
    specialRow: {
        background: blue[200]
    },
    hide:{
        display:'none'
    },
    paper:{
        padding: theme.spacing.unit*2
    }
})

const mapStateToProps = state => ({
    ...state,
    ...state.Report,
    ...state.App
})

const mapDispatchToProps = dispatch => ({
    onChangeSearchTerm: (report, searchTerms) =>
        dispatch(changeSearchTerms(report, searchTerms)),
    onReportChange: report =>
        dispatch(changeReport(report)),
    onDialog: (message,type) => dispatch(dialog(message,type))
})

class Report extends Component {

    constructor(props) {
        super(props);

        this.handleChangePage = this.handleChangePage.bind(this);
        this.handleChangeRowCount = this.handleChangeRowCount.bind(this);
        this.handleFormSubmit = this.handleFormSubmit.bind(this);
        this.handlePDFDownload = this.handlePDFDownload.bind(this);
        this.handleCSVDownload = this.handleCSVDownload.bind(this);
        this.handleXlsxDownload = this.handleXlsxDownload.bind(this);

    }

    componentDidMount() {
        const { match } = this.props;

        this.handleChangeReport(match.params.report)
    }

    componentWillReceiveProps(nextProps) {
        const { match } = this.props;

        if (match.params.report != nextProps.match.params.report) {
            this.handleChangeReport(nextProps.match.params.report)
        }
    }

    getSearchTerms(newTerm) {
        const { values, page, perPage, sortBy, sortMode } = this.props;
        let oldTerm = { values, page, perPage, sortBy, sortMode };

        let mergedTerm = { ...oldTerm, ...newTerm };

        if (typeof newTerm.values != 'undefined')
            mergedTerm.values = { ...newTerm.values }

        return mergedTerm;
    }

    handleChangeReport(report) {
        this.props.onReportChange(report)
    }

    handleChangePage(e, page) {
        const { report, onChangeSearchTerm } = this.props;

        if (!Boolean(e) || !Boolean(e.target)) return;

        const searchTerms = this.getSearchTerms({ page: page + 1 });

        onChangeSearchTerm(report, searchTerms);
    }

    handleFormSubmit(values) {
        const { report, onChangeSearchTerm } = this.props;

        const searchTerms = this.getSearchTerms({ values: { ...values }, page:1, perPage: 25  });

        onChangeSearchTerm(report, searchTerms);
    }

    handleColumnSortClick(name) {
        const { sortBy, sortMode, report, onChangeSearchTerm } = this.props;

        let modedSortMode = sortBy == name ? (sortMode == 'desc' ? 'asc' : 'desc') : sortMode;

        let modedSortBy = sortBy != name ? name : sortBy;

        const searchTerms = this.getSearchTerms({ sortBy: modedSortBy, sortMode: modedSortMode });

        onChangeSearchTerm(report, searchTerms);
    }

    handleChangeRowCount({ target }) {
        const { report, onChangeSearchTerm } = this.props;

        const searchTerms = this.getSearchTerms({ perPage: target.value });
        onChangeSearchTerm(report, searchTerms);
    }

    renderRow(row, index) {

        const { columns, classes } = this.props;

        return (
            <TableRow style={{backgroundColor: row.special?'#90caf9':undefined }} className={row.special ? classes.specialRow : undefined} hover key={index}>
                {Object.keys(columns).map((name, i) => this.renderCell(row[name], columns[name].type, i, columns[name], row[name + '_rowspan'],row[name + '_style']))}
            </TableRow>
        )
    }

    renderCell(value, type, index, details, rowspan = undefined,style=undefined) {
        const { classes,onDialog } = this.props;

        let rowSpan = 1;

        if (typeof rowspan !== 'undefined') {
            if (rowspan == 0 ) {
                return null;
            } else {
                rowSpan = parseInt(rowspan);
            }
        }
        
        return (
            <TableCell style={style} rowSpan={rowSpan} align="left" className={classes.normalCell} key={index}>
                <Cell onDialog={onDialog} value={value} type={type} {...details} />
            </TableCell>
        )
    }

    renderHeaderCell(name, column, index) {

        const { sortBy, sortMode, classes } = this.props;

        return (
            <TableCell
                key={index}
                align='left'
                padding='dense'
                className={classes.darkCell}
                style={{backgroundColor:'#757575'}}
            >
                <Tooltip
                    title="Sort"
                    placement="bottom-start"
                    enterDelay={300}
                >
                    <TableSortLabel
                        active={column.searchable && name == sortBy}
                        direction={sortMode}
                        onClick={e => this.handleColumnSortClick(name)}
                    >
                        {column.label}
                    </TableSortLabel>
                </Tooltip>
            </TableCell>
        );
    }

    renderTable() {

        const {
            results,
            resultCount,
            columns,
            page,
            perPage,
            classes,
            searched,
            title,
            user
        } = this.props;

        if (!searched) return false;

        return (

            <Grid className={classes.table} container>
                <Grid item sm={12}>
                    <table id="exportMe" >
                        <thead style={{color:'white'}}>
                            <tr>
                                <td style={{ paddingBottom: 10 }} colSpan={3}>
                                    <Typography variant="h3" className={classes.hide} align="center">{title }</Typography>
                                </td>
                            </tr>
                            <tr>
                                <td style={{ paddingBottom: 10 }} colSpan={3}>
                                    <Typography variant="h5" className={classes.hide} align="center">{user.name+' ['+user.code+']' }</Typography>
                                </td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colSpan={3} >
                                    <Table>
                                        <TableHead>
                                            {this.renderAdditionalHeaders()}
                                            <TableRow>
                                                {Object.keys(columns).map((name, i) => this.renderHeaderCell(name, columns[name], i))}
                                            </TableRow>
                                        </TableHead>
                                        <TableBody>
                                            {results.map((row, r) => this.renderRow(row, r))}
                                        </TableBody>
                                    </Table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <BottomPanel
                        onChangePage={this.handleChangePage}
                        onChangeRowCount={this.handleChangeRowCount}
                        page={page - 1}
                        perPage={perPage}
                        resultCount={resultCount}
                        onDownloadCSV={()=>this.handleCSVDownload()}
                        onDownloadPDF={()=>this.handlePDFDownload()}
                        onDownloadXLSX={()=>this.handleXlsxDownload()}
                        disableUpload
                    />
                </Grid>
            </Grid>
        )
    }

    renderAdditionalHeaders() {
        const { additionalHeaders, classes } = this.props;

        return additionalHeaders.map((additionalHeader, key) => (
            <TableRow key={key}>
                {additionalHeader.map(({ colSpan, rowSpan, title }, index) => (
                    <TableCell
                        key={index}
                        align='left'
                        padding='dense'
                        className={classes.darkCell}
                        colSpan={colSpan}
                        rowSpan={rowSpan}
                        style={{backgroundColor:'#757575'}}
                    >
                        {title}
                    </TableCell>
                ))}
            </TableRow>
        ))
    }

    handlePDFDownload(values=undefined) {
        const { report } = this.props;

        let newTerms = {};
        if(values){
            newTerms = {values}
        }
        const searchParams = this.getSearchTerms(newTerms);

        agent.Report.saveAsFile('pdf', report, searchParams).then(({ file }) => {
            window.open(APP_URL + 'storage/pdf/' + file);
        })
    }

    handleCSVDownload(values=undefined) {
        const { report } = this.props;

        let newTerms = {};
        if(values){
            newTerms = {values}
        }
        const searchParams = this.getSearchTerms(newTerms);

        agent.Report.saveAsFile('csv', report, searchParams).then(({ file }) => {

            window.open(APP_URL + 'storage/csv/' + file);
        })
    }

    handleXlsxDownload(values=undefined){
        const { report } = this.props;

        let newTerms = {};
        if(values){
            newTerms = {values}
        }
        const searchParams = this.getSearchTerms(newTerms);
        
        agent.Report.saveAsFile('xlsx', report, searchParams).then(({ file }) => {
            window.open(APP_URL + 'storage/xlsx/' + file);
        })
    }

    render() {

        const {
            title,
            inputs,
            inputsStructure,
            report,
            classes
        } = this.props;

        let formWidth = 6;

        let inputCount = Object.keys(inputs).length;

        if (inputCount > 3) formWidth = 8;
        if (inputCount > 5) formWidth = 12;

        return (
            <Layout sidebar>
                <Grid
                    alignItems="center"
                    justify="center"
                    container
                >
                    <Grid item md={formWidth}>
                        <Paper className={classes.paper} >
                            <Form
                                title={title}
                                inputs={inputs}
                                inputsStructure={inputsStructure}
                                onSubmit={this.handleFormSubmit}
                                onDownloadCSV={this.handleCSVDownload}
                                onDownloadXLSX={this.handleXlsxDownload}
                                onDownloadPDF={this.handlePDFDownload}
                                key={report}
                            />
                        </Paper>
                    </Grid>
                </Grid>
                {this.renderTable()}
            </Layout>
        );
    }
}

export default withStyles(styles)(connect(mapStateToProps, mapDispatchToProps)(withRouter(Report)));