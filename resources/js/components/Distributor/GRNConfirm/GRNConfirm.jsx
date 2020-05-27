import React, { Component } from 'react';
import { connect } from 'react-redux';
import Layout from '../../App/Layout';
import Typography from '@material-ui/core/Typography';
import Divider from '@material-ui/core/Divider';
import Toolbar from '@material-ui/core/Toolbar';
import withStyles from '@material-ui/core/styles/withStyles';
import TextField from '@material-ui/core/TextField';
import Button from '@material-ui/core/Button';
import SearchIcon from "@material-ui/icons/Search";
import CloseIcon from "@material-ui/icons/Close";
import SaveIcon from "@material-ui/icons/Save";
import Table from '@material-ui/core/Table';
import TableHead from '@material-ui/core/TableHead';
import TableRow from '@material-ui/core/TableRow';
import TableCell from '@material-ui/core/TableCell';
import TableBody from '@material-ui/core/TableBody';
import TableFooter from '@material-ui/core/TableFooter';

import {
    changeNumber,
    changeQty,
    clearPage,
    fetchProducts,
    save
} from '../../../actions/Distributor/GRNConfirm';
import withRouter from "react-router/withRouter";
import GRNConfirmLine from './GRNConfirmLine';


const styler = withStyles(theme => ({
    grow: {
        flexGrow: 1
    },
    field: {
        width: 260
    },
    margin: {
        margin: theme.spacing.unit
    },
    darkCell: {
        background: theme.palette.grey[600],
        color: theme.palette.common.white,
        border: 'solid 1px ' + theme.palette.common.white
    },
}))

const mapStateToProps = state => ({
    ...state.GRNConfirm
});

const mapDispatchToProps = dispatch => ({
    onChangeNumber: (grnNumber) => dispatch(changeNumber(grnNumber)),
    onChangeQty: (id, qty) => dispatch(changeQty(id, qty)),
    onClearPage: () => dispatch(clearPage()),
    onFetchProducts: (grnNumber) => dispatch(fetchProducts(grnNumber)),
    onSave: (id, lines) => dispatch(save(id, lines)),
});

class GRNConfirm extends Component {

    constructor(props) {
        super(props);

        this.handleChangeGRNNumber = this.handleChangeGRNNumber.bind(this);
        this.handleClickSearch = this.handleClickSearch.bind(this);
        this.handleClearPage = this.handleClearPage.bind(this);
        this.handleClickSave = this.handleClickSave.bind(this);

        if (typeof this.props.match.params.number!=='undefined') {
            this.props.onChangeNumber(this.props.match.params.number.split("_").join("/").toUpperCase());
            this.props.onFetchProducts(this.props.match.params.number.split("_").join("/").toUpperCase());
        }
    }

    componentDidUpdate(prevProps) {
        if (this.props.match.params.number != prevProps.match.params.number && typeof this.props.match.params.number !== 'undefined') {
            this.props.onChangeNumber(this.props.match.params.number.split("_").join("/").toUpperCase());
            this.props.onFetchProducts(this.props.match.params.number.split("_").join("/").toUpperCase());
        }
    }

    handleChangeGRNNumber(e) {
        this.props.onChangeNumber(e.target.value);
    }

    handleClickSearch() {
        const { grnNumber } = this.props;

        this.props.onFetchProducts(grnNumber);
    }

    handleClearPage() {
        this.props.onClearPage();
    }

    handleClickSave() {
        const { grnId, lines } = this.props;

        this.props.onSave(grnId, lines)
    }

    render() {
        const { classes, grnNumber, onChangeQty, lines } = this.props;

        return (
            <Layout sidebar={true}>
                <Toolbar variant="dense">
                    <Typography variant="h5" align="center">GRN Confirm</Typography>
                    <div className={classes.grow} />
                    <div className={classes.field}>
                        <TextField
                            onChange={this.handleChangeGRNNumber}
                            label="GRN Number"
                            fullWidth={true}
                            variant="outlined"
                            margin="dense"
                            value={grnNumber}

                        />
                    </div>
                    <Button onClick={this.handleClickSearch} className={classes.margin} variant="contained" color="primary" >
                        <SearchIcon />
                        Search
                    </Button>
                    <Button onClick={this.handleClearPage} className={classes.margin} variant="contained" color="secondary" >
                        <CloseIcon />
                        Cancel
                    </Button>
                    <Button onClick={this.handleClickSave} className={classes.margin} variant="contained" color="primary" >
                        <SaveIcon />
                        Save
                    </Button>
                </Toolbar>
                <Divider />
                <Table>
                    <TableHead>
                        <TableRow>
                            <TableCell
                                align='left'
                                padding='dense'
                                className={classes.darkCell}
                                style={{ width: 20 }}
                            >
                                #
                            </TableCell>
                            <TableCell
                                align='left'
                                padding='dense'
                                className={classes.darkCell}
                            >
                                Product
                            </TableCell>
                            <TableCell
                                align='left'
                                padding='dense'
                                className={classes.darkCell}
                                style={{ width: 80 }}
                            >
                                Batch
                            </TableCell>
                            <TableCell
                                align='left'
                                padding='dense'
                                className={classes.darkCell}
                                style={{ width: 100 }}
                            >
                                Expired On
                            </TableCell>
                            <TableCell
                                align='left'
                                padding='dense'
                                className={classes.darkCell}
                                style={{ width: 70 }}
                            >
                                Price
                            </TableCell>
                            <TableCell
                                align='left'
                                padding='dense'
                                className={classes.darkCell}
                                style={{ width: 70 }}
                            >
                                Qty
                            </TableCell>
                            <TableCell
                                align='left'
                                padding='dense'
                                className={classes.darkCell}
                            >
                                Amount
                            </TableCell>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {Object.values(lines).map((line, key) => (
                            <GRNConfirmLine
                                key={key}
                                {...line}
                                index={key + 1}
                                onChangeQty={onChangeQty}
                            />
                        ))}
                    </TableBody>
                    <TableFooter>
                        <TableRow>
                            <TableCell
                                align='left'
                                padding='dense'
                                style={{ width: 50 }}
                                colSpan={7}
                            >
                                <Toolbar variant="dense" >
                                    <div className={classes.grow} />
                                    <div className={classes.field}>
                                        <TextField
                                            variant="outlined"
                                            margin="dense"
                                            label="Amount"
                                            value={Object.values(lines).map(line => line.qty * line.price).reduce((a, b) => a + b, 0).toFixed(2)}
                                            disabled={true}
                                            readOnly={true}
                                            fullWidth
                                        />
                                    </div>
                                </Toolbar>
                            </TableCell>
                        </TableRow>
                    </TableFooter>
                </Table>
            </Layout>
        )
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(styler( withRouter (GRNConfirm)));
