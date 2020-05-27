import React, { Component } from 'react'
import  Tooltip from '@material-ui/core/Tooltip';
import  withStyles from '@material-ui/core/styles/withStyles';
import Table from '@material-ui/core/Table';
import TableBody from '@material-ui/core/TableBody';
import TableCell from '@material-ui/core/TableCell';
import TableHead from '@material-ui/core/TableHead';
import TableRow from '@material-ui/core/TableRow';
import TableSortLabel from '@material-ui/core/TableSortLabel';
import Create from '@material-ui/icons/Create';
import HighlightOff from '@material-ui/icons/HighlightOff';
import Refresh from '@material-ui/icons/Refresh';
import Cell from './Cell/Cell';
import green from '@material-ui/core/colors/green';

const styles = theme => ({
    darkCell: {
        background: theme.palette.grey[600],
        color: theme.palette.common.white,
        border: 'solid 1px '+theme.palette.common.white
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
        width:theme.spacing.unit*6,
        textAlign:'center'
    },
    normalCell: {
        paddingLeft: 4,
        paddingRight: 0,
        borderLeft: 'solid 2px ' + theme.palette.grey[100],
        borderRight: 'solid 2px ' + theme.palette.grey[200]
    },
    updateIcon: {
        color: theme.palette.primary.main,
        cursor: 'pointer',
        '&:hover': {
            color: theme.palette.primary.dark,
            fontSize: '2em'
        }
    },
    removeIcon: {
        color: theme.palette.secondary.main,
        cursor: 'pointer',
        '&:hover': {
            color: theme.palette.secondary.dark,
            fontSize: '2em'
        }
    },
    restoreIcon: {
        color: green[400],
        cursor: 'pointer',
        '&:hover': {
            color: green[700],
            fontSize: '2em'
        }
    },
    deletedRow: {
        background: theme.palette.grey[300]
    }
})

class CrudTable extends Component {

    handleColumnSortClick(name) {
        const { sortBy, sortMode } = this.props;

        let nextSortMode;

        if (name == sortBy) {
            nextSortMode = (sortMode == 'desc') ? 'asc' : 'desc';
        } else {
            nextSortMode = 'desc'
        }

        this.props.onSortChange(name, nextSortMode);
    }

    renderUpdateButton(r) {
        const { actions, classes, onUpdate } = this.props;

        if (!actions.includes('update')) return null;

        return (
            <TableCell className={  classes.actionCell}><Create className={classes.updateIcon} onClick={e => onUpdate(r)} /></TableCell>
        )
    }

    renderDeleteButton(r) {
        const { actions, classes, onDelete } = this.props;

        if (!actions.includes('delete')) return null;
        return (
            <TableCell className={classes.actionCell}><HighlightOff className={classes.removeIcon} onClick={e => onDelete(r)} /></TableCell>
        )
    }

    renderRestoreButton(r) {
        const { actions, classes, onRestore } = this.props;

        if (!actions.includes('delete')) return null;

        return (
            <TableCell className={classes.actionCell}><Refresh className={classes.restoreIcon} onClick={e => onRestore(r)} /></TableCell>
        )
    }

    renderUpdateColumnHeader() {
        const { actions, classes } = this.props;

        if (!actions.includes('update')) return null;

        return (
            <TableCell className={classes.darkCell} >Edit</TableCell>
        )
    }

    renderDeleteColumnHeader() {
        const { actions, classes } = this.props;

        if (!actions.includes('delete')) return null;

        return (
            <TableCell className={ classes.darkCell} >Delete</TableCell>
        )
    }

    render() {
        const { columns, sortMode, sortBy, classes, results } = this.props;


        return (
            <Table id="exportMe" >
                <TableHead>
                    <TableRow>
                        {Object.keys(columns).map((name, i) => {
                            let column = columns[name];

                            return (
                                <TableCell
                                    key={i}
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
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
                        })}
                        {this.renderUpdateColumnHeader()}
                        {this.renderDeleteColumnHeader()}
                    </TableRow>
                </TableHead>
                <TableBody>
                    {results.map((result, r) => (
                        <TableRow className={result.deleted? classes.deletedRow: undefined} hover key={r}>
                            {Object.keys(columns).map((name, i) => {
                                return (
                                    <TableCell align="left" className={classes.normalCell} key={i}>
                                        <Cell value={result[name]} type={columns[name].type} {...columns[name]} />
                                    </TableCell>
                                )
                            })}
                            {this.renderUpdateButton(r)}
                            {result.deleted?this.renderRestoreButton(r):this.renderDeleteButton(r)}
                        </TableRow>
                    ))}
                </TableBody>
            </Table>

        )
    }
}

export default withStyles(styles)(CrudTable);
