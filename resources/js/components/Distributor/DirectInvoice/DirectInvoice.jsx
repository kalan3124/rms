import React, { Component } from 'react';
import { connect } from 'react-redux';
import AjaxDropdown from '../../CrudPage/Input/AjaxDropdown';
import Typography from '@material-ui/core/Typography';
import Divider from '@material-ui/core/Divider';
import TextField from '@material-ui/core/TextField';
import Toolbar from '@material-ui/core/Toolbar';
import withStyles from '@material-ui/core/styles/withStyles';
import Table from "@material-ui/core/Table";
import TableHead from "@material-ui/core/TableHead";
import TableRow from "@material-ui/core/TableRow";
import TableCell from "@material-ui/core/TableCell";
import TableBody from "@material-ui/core/TableBody";
import TableFooter from '@material-ui/core/TableFooter';
import Button from '@material-ui/core/Button';
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import ListItemSecondaryAction from "@material-ui/core/ListItemSecondaryAction";
import Paper from "@material-ui/core/Paper";

import {
    changeDistributor,
    changeSalesman,
    changeCustomer,
    addLine,
    removeLine,
    changeDiscount,
    changeProduct,
    changeQty,
    fetchLineInfo,
    save,
    fetchNextInvoiceNumber,
    clearPage,
    fetchBonus,
    changeBonusQty,
    fetchBatchDetails,
    openBatchEditForm,
    changeBatchQty,
    cancelBatchEditForm
} from '../../../actions/Distributor/DirectInvoice'
import { DISTRIBUTOR_TYPE, DISTRIBUTOR_SALES_REP_TYPE } from '../../../constants/config';
import DirectInvoiceLine from './DirectInvoiceLine';
import { alertDialog } from "../../../actions/Dialogs";
import Layout from '../../App/Layout';
import BatchEditForm from '../CreateInvoice/BatchEditForm';

const styler = withStyles(theme => ({
    field: {
        padding: theme.spacing.unit,
        width: 230
    },
    darkCell: {
        background: theme.palette.grey[600],
        color: theme.palette.common.white,
        border: 'solid 1px ' + theme.palette.common.white
    },
    grow: {
        flexGrow: 1
    },
    margin: {
        margin: theme.spacing.unit
    }
}));

const mapStateToProps = state => ({
    ...state.DirectInvoice
});

const mapDispatchToProps = dispatch => ({
    onChangeDistributor: (distributor) => dispatch(changeDistributor(distributor)),
    onChangeSalesman: (salesman) => dispatch(changeSalesman(salesman)),
    onChangeCustomer: (customer) => dispatch(changeCustomer(customer)),
    onAddLine: () => dispatch(addLine()),
    onRemoveLine: (id) => dispatch(removeLine(id)),
    onChangeDiscount: (id, discount) => dispatch(changeDiscount(id, discount)),
    onChangeProduct: (id, product) => dispatch(changeProduct(id, product)),
    onChangeQty: (id, qty) => dispatch(changeQty(id, qty)),
    onFetchLineInfo: (id, distributor, product, customer) => dispatch(fetchLineInfo(id, distributor, product, customer)),
    onSave: (distributor, salesman, customer, lines, bonusLines, requestApproval) => dispatch(save(distributor, salesman, customer, lines, bonusLines, requestApproval)),
    onFetchInvoiceNumber: (distributor, salesman) => dispatch(fetchNextInvoiceNumber(distributor, salesman)),
    onClearPage: () => dispatch(clearPage()),
    onFetchBonus: (id, lines) => dispatch(fetchBonus(id, lines)),
    onChangeBonusQty: (id, productId, qty) => dispatch(changeBonusQty(id, productId, qty)),
    onMessage: (message, type) => dispatch(alertDialog(message, type)),
    onFetchBatchDetails: (id, distributor, product, qty) => dispatch(fetchBatchDetails(id, distributor, product, qty)),
    onOpenBatchEditForm: (line) => dispatch(openBatchEditForm(line)),
    onChangeBatchQty: (batch, qty) => dispatch(changeBatchQty(batch, qty)),
    onCancelBatchEditForm: ()=>dispatch(cancelBatchEditForm())
});

class DirectInvoice extends Component {

    constructor(props) {
        super(props);

        this.handleChangeSalesman = this.handleChangeSalesman.bind(this);
        this.handleChangeProduct = this.handleChangeProduct.bind(this);
        this.handleClickSave = this.handleClickSave.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleRemoveLine = this.handleRemoveLine.bind(this);
        this.handleClickBatchEditButton = this.handleClickBatchEditButton.bind(this);
        this.handleConfirmBatchForm = this.handleConfirmBatchForm.bind(this);
    }

    handleChangeSalesman(salesman) {
        const { onChangeSalesman, distributor, onFetchInvoiceNumber } = this.props;

        onFetchInvoiceNumber(distributor, salesman);
        onChangeSalesman(salesman);
    }

    handleChangeProduct(id, product) {
        const { distributor, onFetchLineInfo, onChangeProduct, lines, onFetchBonus, customer, onMessage } = this.props;

        let products = Object.values(lines).map((line, key) => (
            line.product ? line.product.value : null
        ));

        if (products.includes(product.value)) {
            onMessage("Product Already seleted.", "error");
            onChangeProduct(id, '');
            return;
        }

        onChangeProduct(id, product ? product : undefined);

        if (product) {
            onFetchLineInfo(id, distributor, product, customer);
            onFetchBonus(distributor.value, { ...lines, [id]: { ...lines[id], product } });
        }
    }

    handleClickSave() {
        const { distributor, salesman, customer, lines, onSave, bonusLines } = this.props;

        if (this.wantConfrimation())
            onSave(distributor, salesman, customer, lines, bonusLines, true);
        else
            onSave(distributor, salesman, customer, lines, bonusLines, false);
    }

    handleChangeQty(id, qty) {
        const { distributor, lines, onChangeQty, onFetchBonus, onFetchBatchDetails } = this.props;

        const line = lines[id];

        onChangeQty(id, qty)

        if (line.product && line.product.value && distributor && distributor.value) {
            onFetchBonus(distributor.value, { ...lines, [id]: { ...lines[id], qty } });
            onFetchBatchDetails(id, distributor, line.product, qty);
        }
    }

    handleChangeBonusQty(id, productId) {
        return e => {
            let qty = parseInt(e.target.value);

            if (isNaN(qty) || qty < 0)
                return;

            this.props.onChangeBonusQty(id, productId, qty);
        }
    }

    handleRemoveLine(id) {
        const { lines, distributor, onFetchBonus, onRemoveLine } = this.props;

        let modedLines = { ...lines };

        delete modedLines[id];

        onFetchBonus(distributor.value, modedLines);
        onRemoveLine(id);
    }


    wantConfrimation() {
        const { bonusLines } = this.props;

        let wantConfrimation = false;

        Object.values(bonusLines).forEach((line) => {
            let qty = 0;
            Object.values(line.products).forEach((product) => {
                if (product.qty)
                    qty += product.qty;
            });

            if (qty > line.qty)
                wantConfrimation = true;

        });

        return wantConfrimation;
    }

    handleClickBatchEditButton(id) {
        const { onOpenBatchEditForm } = this.props;

        onOpenBatchEditForm(id);
    }

    handleConfirmBatchForm() {
        const { onOpenBatchEditForm } = this.props;

        onOpenBatchEditForm(undefined);
    }

    render() {
        const {
            classes,
            salesman,
            onChangeDistributor,
            distributor,
            lines,
            onChangeDiscount,
            onAddLine,
            onClearPage,
            invNumber,
            onChangeCustomer,
            customer,
            bonusLines,
            batchEditLine,
            onChangeBatchQty,
            onCancelBatchEditForm
        } = this.props;


        // Get already filled bonus ids
        let existBonusIds = [];

        Object.values(bonusLines).map((line, lineKey) => {
            Object.values(line.products).map((product, productKey) => {
                if (product.qty && !existBonusIds.includes(line.id))
                    existBonusIds.push(line.id)
            });
        });

        const wantConfrimation = this.wantConfrimation();

        return (
            <Layout sidebar={true}>
                <Typography variant="h5" align="center">Direct Invoices</Typography>
                <Divider />
                <Toolbar variant="dense">
                    <div className={classes.field} >
                        <AjaxDropdown
                            label="Distributor"
                            link="user"
                            onChange={onChangeDistributor}
                            value={distributor}
                            where={{
                                u_tp_id: DISTRIBUTOR_TYPE
                            }}
                        />
                    </div>
                    <div className={classes.field} >
                        <AjaxDropdown
                            label="Salesman"
                            link="user"
                            value={salesman}
                            onChange={this.handleChangeSalesman}
                            key={typeof distributor === 'undefined' || !distributor ? 0 : distributor.value}
                            where={{
                                u_tp_id: DISTRIBUTOR_SALES_REP_TYPE,
                                dis_id: typeof distributor === 'undefined' || !distributor ? undefined : distributor.value
                            }}
                        />
                    </div>
                    <div className={classes.field} >
                        <AjaxDropdown
                            label="Customer"
                            link="distributor_customer"
                            value={customer}
                            onChange={onChangeCustomer}
                            key={typeof distributor === 'undefined' || !distributor ? 0 : distributor.value}
                            where={{
                                dis_id: typeof distributor === 'undefined' || !distributor ? undefined : distributor.value
                            }}
                        />
                    </div>
                    <div className={classes.field}>
                        <TextField
                            variant="outlined"
                            margin="dense"
                            label="Invoice Number"
                            fullWidth
                            value={invNumber}
                            disabled={true}
                            readOnly={true}
                        />
                    </div>
                </Toolbar>
                <Divider />
                {distributor && salesman && customer ?
                    <div>
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
                                        style={{ width: 400 }}
                                    >
                                        Product
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
                                        style={{ width: 150 }}
                                    >
                                        Batch(s)
                                </TableCell>
                                    <TableCell
                                        align='left'
                                        padding='dense'
                                        className={classes.darkCell}
                                        style={{ width: 50 }}
                                    >
                                        Stock
                                </TableCell>
                                    <TableCell
                                        align='left'
                                        padding='dense'
                                        className={classes.darkCell}
                                        style={{ width: 180 }}
                                    >
                                        Qty
                                </TableCell>
                                    <TableCell
                                        align='left'
                                        padding='dense'
                                        className={classes.darkCell}
                                        style={{ width: 180 }}
                                    >
                                        Discount %
                                </TableCell>
                                    <TableCell
                                        align='left'
                                        padding='dense'
                                        className={classes.darkCell}
                                    >
                                        Amount
                                </TableCell>
                                    <TableCell
                                        align='left'
                                        padding='dense'
                                        className={classes.darkCell}
                                    >
                                        Actions
                                </TableCell>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {Object.values(lines).map((line, key) => (
                                    <DirectInvoiceLine
                                        key={key}
                                        {...line}
                                        index={key + 1}
                                        onChangeProduct={this.handleChangeProduct}
                                        onChangeQty={this.handleChangeQty}
                                        onChangeDiscount={onChangeDiscount}
                                        onAdd={onAddLine}
                                        onRemove={this.handleRemoveLine}
                                        otherLineCount={Object.keys(lines).length}
                                        salesman={salesman}
                                        onBatchEditClick={this.handleClickBatchEditButton}
                                        distributor={distributor}
                                    />
                                ))}
                            </TableBody>
                            <TableFooter>
                                <TableRow>
                                    <TableCell
                                        align='left'
                                        padding='dense'
                                        style={{ width: 50 }}
                                        colSpan={8}
                                    >
                                        <Toolbar variant="dense" >
                                            <div className={classes.field}>
                                                <TextField
                                                    variant="outlined"
                                                    margin="dense"
                                                    label="Gross Amount"
                                                    value={Object.values(lines).map(line => line.qty * line.price).reduce((a, b) => a + b, 0).toFixed(2)}
                                                    disabled={true}
                                                    readOnly={true}
                                                    fullWidth
                                                />
                                            </div>
                                            <div className={classes.field}>
                                                <TextField
                                                    variant="outlined"
                                                    margin="dense"
                                                    label="Discount"
                                                    value={Object.values(lines).map(line => (line.qty * line.price) * (line.discount / 100)).reduce((a, b) => a + b, 0).toFixed(2)}
                                                    disabled={true}
                                                    readOnly={true}
                                                    fullWidth
                                                />
                                            </div>
                                            <div className={classes.field}>
                                                <TextField
                                                    variant="outlined"
                                                    margin="dense"
                                                    label="Net Amount"
                                                    value={Object.values(lines).map(line => (line.qty * line.price) * ((100 - line.discount) / 100)).reduce((a, b) => a + b, 0).toFixed(2)}
                                                    disabled={true}
                                                    readOnly={true}
                                                    fullWidth
                                                />
                                            </div>
                                            <div className={classes.grow} />
                                            <Button className={classes.margin} onClick={onClearPage} color="secondary" variant="contained" >Cancel</Button>
                                            {wantConfrimation ?
                                                <Button onClick={this.handleClickSave} variant="contained" color="secondary" className={classes.margin}>
                                                    Request Approval
                                            </Button>
                                                :
                                                <Button onClick={this.handleClickSave} className={classes.margin} variant="contained" color="primary">
                                                    Save
                                            </Button>
                                            }
                                        </Toolbar>
                                    </TableCell>
                                </TableRow>
                            </TableFooter>
                        </Table>
                        <Divider />

                        <Paper>
                            <Typography className={classes.darkCell} variant="h5">Bonus</Typography>
                            {Object.values(bonusLines).map((line, lineKey) => (
                                <div className={classes.margin} key={lineKey}>
                                    <Divider />
                                    <Typography variant="h6">{line.label} ({line.qty} Free)</Typography>
                                    <List variant="dense" >
                                        {Object.values(line.products).map((product, key) => (
                                            <ListItem divider={true} button={true} key={key} >
                                                <ListItemText>{product.label}</ListItemText>
                                                <ListItemSecondaryAction>
                                                    <TextField
                                                        disabled={existBonusIds.filter(id => line.excludes.includes(id)).length > 0}
                                                        label="Qty"
                                                        variant="outlined"
                                                        margin="dense"
                                                        type="number"
                                                        value={product.qty}
                                                        onChange={this.handleChangeBonusQty(line.id, product.value)}
                                                    />
                                                </ListItemSecondaryAction>
                                            </ListItem>
                                        ))}
                                    </List>
                                </div>
                            ))}
                        </Paper>
                        <BatchEditForm
                            onClose={onCancelBatchEditForm}
                            onConfirm={this.handleConfirmBatchForm}
                            details={typeof batchEditLine !== 'undefined' ? lines[batchEditLine].availableBatches : {}}
                            open={typeof batchEditLine !== 'undefined'}
                            onChange={ onChangeBatchQty }
                            qty={lines[batchEditLine]?lines[batchEditLine].qty:0}
                        />
                    </div>
                    :
                    <Typography variant="caption" align="center">Please fill all fields.</Typography>
                }
            </Layout>
        );
    }
}


export default connect(mapStateToProps, mapDispatchToProps)(styler(DirectInvoice));