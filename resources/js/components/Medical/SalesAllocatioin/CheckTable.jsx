import React, { Component } from "react";
import classNames from "classnames";
import Table from "@material-ui/core/Table";
import TableBody from "@material-ui/core/TableBody";
import TableRow from "@material-ui/core/TableRow";
import TableHead from "@material-ui/core/TableHead";
import TableCell from "@material-ui/core/TableCell";
import Checkbox from "@material-ui/core/Checkbox";
import withStyles from "@material-ui/core/styles/withStyles";
import { TableFooter, TablePagination } from "@material-ui/core";

const styles = theme => ({
    darkCell: {
        background: theme.palette.grey[600],
        color: theme.palette.common.white,
        border: "solid 1px " + theme.palette.common.white
    },
    actionCell: {
        padding: 0,
        borderLeft: "solid 2px " + theme.palette.grey[100],
        borderRight: "solid 2px " + theme.palette.grey[200],
        width: theme.spacing.unit * 6,
        textAlign: "center"
    },
    normalCell: {
        paddingLeft: 4,
        paddingRight: 0,
        borderLeft: "solid 2px " + theme.palette.grey[100],
        borderRight: "solid 2px " + theme.palette.grey[200]
    },
    checkCell: {
        width: 64,
        textAlign: "center"
    },
    table: {
        width: "100%"
    }
});

class CheckTable extends Component {
    constructor(props) {
        super(props);

        this.handleChangeMode = this.handleChangeMode.bind(this);
        this.handleChangePage = this.handleChangePage.bind(this);
    }

    handleChangeMode() {
        const { mode, onChangeMode } = this.props;

        if (onChangeMode) {
            onChangeMode(mode == "include" ? "exclude" : "include");
        }
    }

    handleCheckRow(row) {
        const { onSelect } = this.props;

        return e => {
            if (onSelect) {
                onSelect(row);
            }
        };
    }

    handleChangePage(e, page) {
        const { onChangePage } = this.props;

        onChangePage(page + 1);
    }

    render() {
        const {
            classes,
            columns,
            results,
            mode,
            selected,
            page,
            perPage,
            onChangePerPage,
            count
        } = this.props;

        return (
            <Table className={classes.table}>
                <TableHead>
                    <TableRow>
                        <TableCell
                            padding="dense"
                            className={classNames(
                                classes.darkCell,
                                classes.checkCell
                            )}
                        >
                            <Checkbox
                                onChange={this.handleChangeMode}
                                checked={mode == "exclude"}
                                indeterminate={!!Object.keys(selected).length}
                            />
                        </TableCell>
                        {columns.map((column, key) => (
                            <TableCell
                                key={key}
                                padding="dense"
                                className={classes.darkCell}
                            >
                                {column.label}
                            </TableCell>
                        ))}
                    </TableRow>
                </TableHead>
                <TableBody>
                    {Object.values(results).map((result, key) => (
                        <TableRow key={key}>
                            <TableCell
                                padding="dense"
                                className={classNames(
                                    classes.normalCell,
                                    classes.checkCell
                                )}
                            >
                                <Checkbox
                                    onChange={this.handleCheckRow(result)}
                                    checked={
                                        mode == "exclude"
                                            ? !selected[result.id]
                                            : !!selected[result.id]
                                    }
                                />
                            </TableCell>
                            {columns.map((column, key2) => (
                                <TableCell
                                    key={key2}
                                    padding="dense"
                                    className={classes.normalCell}
                                >
                                    {result[column.name]}
                                </TableCell>
                            ))}
                        </TableRow>
                    ))}
                </TableBody>
                <TableFooter>
                    <TableRow>
                        <TablePagination
                            rowsPerPageOptions={[5, 10, 25]}
                            count={count}
                            rowsPerPage={perPage}
                            page={page - 1}
                            SelectProps={{
                                inputProps: { "aria-label": "rows per page" },
                                native: true
                            }}
                            onChangePage={this.handleChangePage}
                            onChangeRowsPerPage={onChangePerPage}
                            labelDisplayedRows={({ from, to, count })=>{
                                return ( Math.floor(from/perPage)+1)+' of '+ Math.ceil(count/perPage)
                            }}
                        />
                    </TableRow>
                </TableFooter>
            </Table>
        );
    }
}

export default withStyles(styles)(CheckTable);
