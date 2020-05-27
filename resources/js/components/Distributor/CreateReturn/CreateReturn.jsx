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
    changeSalable,
    changeReason,
    changeBatch,
    changeBonusBatch,
    alertDialogs
} from '../../../actions/Distributor/CreateReturn'
import { alertDialog } from "../../../actions/Dialogs";
import { DISTRIBUTOR_TYPE, DISTRIBUTOR_SALES_REP_TYPE } from '../../../constants/config';
import CreateReturnLine from './CreateReturnLine';
import Layout from '../../App/Layout';


const styler = withStyles(theme => ({
    field: {
        padding: theme.spacing.unit,
        width: 260
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
    },
    secondaryAction: {
        transform: "unset",
        marginTop: -30
    }
}));

const mapStateToProps = state => ({
    ...state.CreateReturn
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
    onFetchLineInfo: (id, distributor, product) => dispatch(fetchLineInfo(id, distributor, product)),
    onSave: (distributor, salesman, customer, lines, bonusLines) => dispatch(save(distributor, salesman, customer, lines, bonusLines)),
    onFetchInvoiceNumber: (distributor, salesman) => dispatch(fetchNextInvoiceNumber(distributor, salesman)),
    onClearPage: () => dispatch(clearPage()),
    onFetchBonus: (id, lines) => dispatch(fetchBonus(id, lines)),
    onChangeBonusQty: (id, productId, qty) => dispatch(changeBonusQty(id, productId, qty)),
    onChangeSalable: (id, salable) => dispatch(changeSalable(id, salable)),
    onChangeReason: (id, reason) => dispatch(changeReason(id, reason)),
    onChangeBatch: (id, batch) => dispatch(changeBatch(id, batch)),
    onChangeBonusBatch: (id, productId, batch) => dispatch(changeBonusBatch(id, productId, batch)),
    onMessage: (message,type) => dispatch(alertDialog(message,type)),

})

class CreateReturn extends Component {

    constructor(props) {
        super(props);

        this.handleChangeSalesman = this.handleChangeSalesman.bind(this);
        this.handleChangeProduct = this.handleChangeProduct.bind(this);
        this.handleClickSave = this.handleClickSave.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleRemoveLine = this.handleRemoveLine.bind(this);
    }

    handleChangeSalesman(salesman) {
        const { onChangeSalesman, distributor, onFetchInvoiceNumber } = this.props;

        onFetchInvoiceNumber(distributor, salesman);
        onChangeSalesman(salesman);
    }

    handleChangeProduct(id, product) {
        const { distributor, onFetchLineInfo, onChangeProduct, lines, onFetchBonus, onMessage } = this.props;

        let products = Object.values(lines).map((line, key) => (
            line.product ? line.product.value : null
        ));

        if (products.includes(product.value)) {
            onMessage("Product Already seleted.","error");
            onChangeProduct(id,'');
            return;
        }

        onChangeProduct(id, product ? product : undefined);

        if (product) {
            onFetchLineInfo(id, distributor, product);
            onFetchBonus(distributor.value, { ...lines, [id]: { ...lines[id], product } });
        }
    }

    handleClickSave() {
        const { distributor, salesman, customer, lines, onSave, bonusLines } = this.props;

        onSave(distributor, salesman, customer, lines, bonusLines)
    }

    handleChangeQty(id, qty) {
        const { distributor, lines, onChangeQty, onFetchBonus } = this.props;

        const line = lines[id];

        onChangeQty(id, qty)

        if (line.product && line.product.value && distributor && distributor.value)
            onFetchBonus(distributor.value, { ...lines, [id]: { ...lines[id], qty } });
    }

    handleChangeBonusQty(id, productId) {
        return e => {
            const { bonusLines } = this.props;
            let qty = parseInt(e.target.value);

            if (isNaN(qty) || qty < 0)
                return;

            const maxQty = bonusLines[id].qty;
            const allQty = Object.values(bonusLines[id].products).map(product => product.value == productId ? qty : product.qty).reduce((total, num) => total + num);

            if (allQty > maxQty) {
                return null;
            }

            this.props.onChangeBonusQty(id, productId, qty);
        }
    }

    handleChangeBonusBatch(id, productId) {
        return batch => {
            this.props.onChangeBonusBatch(id, productId, batch);
        }
    }

    handleRemoveLine(id) {
        const { lines, distributor, onFetchBonus, onRemoveLine } = this.props;

        let modedLines = { ...lines };

        delete modedLines[id];

        onFetchBonus(distributor.value, modedLines);
        onRemoveLine(id);
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
            onRemoveLine,
            onClearPage,
            invNumber,
            onChangeCustomer,
            customer,
            bonusLines,
            onChangeSalable,
            onChangeReason,
            onChangeBatch
        } = this.props;


        // Get already filled bonus ids
        let existBonusIds = [];

        Object.values(bonusLines).map((line, lineKey) => {
            Object.values(line.products).map((product, productKey) => {
                if (product.qty && !existBonusIds.includes(line.id))
                    existBonusIds.push(line.id)
            });
        });

        return (
            <Layout sidebar={true}>
                <Typography variant="h5" align="center">Create Return Order</Typography>
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
                                        style={{ width: 200 }}
                                    >
                                        Batch
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
                                        style={{ width: 50 }}
                                    >
                                        Salable
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
                                        style={{ width: 400 }}
                                    >
                                        Reason
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
                                    <CreateReturnLine
                                        key={key}
                                        {...line}
                                        index={key + 1}
                                        distributor={distributor}
                                        onChangeProduct={this.handleChangeProduct}
                                        onChangeQty={this.handleChangeQty}
                                        onChangeDiscount={onChangeDiscount}
                                        onAdd={onAddLine}
                                        onRemove={this.handleRemoveLine}
                                        onChangeSalable={onChangeSalable}
                                        onChangeReason={onChangeReason}
                                        onChangeBatch={onChangeBatch}
                                        salesman={salesman}
                                        otherLineCount={Object.keys(lines).length}
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
                                            <Button className={classes.margin} onClick={this.handleClickSave} color="primary" variant="contained" >Save</Button>
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
                                            <ListItem divider={true} key={key} >
                                                <ListItemText>{product.label}</ListItemText>
                                                <ListItemSecondaryAction className={classes.secondaryAction} >
                                                    <div style={{ width: 300 }} >
                                                        <Toolbar variant="dense">
                                                            <div style={{ width: 200 }} >
                                                                <AjaxDropdown value={product.batch} onChange={this.handleChangeBonusBatch(line.id, product.value)} link="distributor_batch" label="Batch" where={{ distributor, product }} />
                                                            </div>
                                                            <div style={{ width: 80, marginLeft: 8 }}>
                                                                <TextField
                                                                    disabled={existBonusIds.filter(id => line.excludes.includes(id)).length > 0}
                                                                    label="Qty"
                                                                    variant="outlined"
                                                                    margin="dense"
                                                                    type="number"
                                                                    value={product.qty}
                                                                    onChange={this.handleChangeBonusQty(line.id, product.value)}
                                                                />
                                                            </div>
                                                        </Toolbar>
                                                    </div>
                                                </ListItemSecondaryAction>
                                            </ListItem>
                                        ))}
                                    </List>
                                </div>
                            ))}
                        </Paper>
                    </div>
                    :
                    <Typography variant="caption" align="center">Please fill all fields.</Typography>
                }
            </Layout>
        );
    }
}


export default connect(mapStateToProps, mapDispatchToProps)(styler(CreateReturn));