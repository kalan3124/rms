import React from 'react';
import PropTypes from 'prop-types';
import  withStyles from '@material-ui/core/styles/withStyles';
import  Toolbar from '@material-ui/core/Toolbar';
import  TablePagination from '@material-ui/core/TablePagination';
import  Fab from '@material-ui/core/Fab';
import  Typography from '@material-ui/core/Typography';
import WebIcon from '@material-ui/icons/Web';
import FileIcon from '@material-ui/icons/InsertDriveFile';
import UploadIcon from '@material-ui/icons/CloudUpload';
import TableChartIcon from '@material-ui/icons/TableChart';
import ReactHTMLTableToExcel from 'react-html-table-to-excel';
import { connect } from 'react-redux';

const styles = theme => ({
    pagination: {

    },
    middle: {
        flexGrow: 1
    },
    margin: {
        margin: theme.spacing.unit
    },
    icon:{
        fontSize:'1em',
        marginRight:theme.spacing.unit
    },
    typo:{
        fontSize:'.7em',
        color:theme.palette.common.white
    }
})

const LabeledButton = ({ Icon, label, classes,onClick,link }) => (

    <Fab
        variant="extended"
        size="small"
        color="primary"
        aria-label={label}
        className={classes.fab}
        onClick={onClick}
    >
        <Icon fontSize="small" className={classes.icon} />
        <Typography className={classes.typography} variant="caption">
            {label}
        </Typography>
    </Fab>
)

const mapStateToProps = state =>({
    ...state.App
});

const BottomPanel = ({disableUpload, classes, page, resultCount, perPage, onChangePage, onChangeRowCount,onDownloadCSV,onDownloadPDF,onUploadCSV,user,onDownloadXLSX }) => {

    const labeledButtonClasses = {
        icon:classes.icon,
        fab:classes.margin,
        typography:classes.typo
    }

    return (
        <Toolbar>
            <LabeledButton onClick={onDownloadCSV} Icon={WebIcon} label="Save As CSV" classes={labeledButtonClasses} />
            <LabeledButton onClick={onDownloadPDF} Icon={FileIcon} label="Save As PDF" classes={labeledButtonClasses} />
            <LabeledButton onClick={onDownloadXLSX} Icon={TableChartIcon} label="Save As XLSX" classes={labeledButtonClasses} />
            {disableUpload?null:<LabeledButton onClick={onUploadCSV} Icon={UploadIcon} label="Upload CSV" classes={labeledButtonClasses} />}
            <div className={classes.middle} />
            <TablePagination
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
                labelDisplayedRows={({ from, to, count })=>{
                    return ( Math.floor(from/perPage)+1)+' of '+ Math.ceil(count/perPage)
                }}
                onChangeRowsPerPage={onChangeRowCount}
            />
        </Toolbar>
    )
}

const numberPropType = PropTypes.oneOfType([
    PropTypes.string,
    PropTypes.number
])

BottomPanel.propTypes = {
    perPage: numberPropType,
    page: numberPropType,
    resultCount: numberPropType,
    disableUpload:PropTypes.bool,
    onChangePage: PropTypes.func,
    onChangeRowCount: PropTypes.func
};


export default connect(mapStateToProps) (withStyles(styles)(BottomPanel));