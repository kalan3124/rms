import React, { Component } from "react";
import Layout from "../../App/Layout";
import withStyles from "@material-ui/core/styles/withStyles";
import Typography from "@material-ui/core/Typography";
import Toolbar from "@material-ui/core/Toolbar";
import Divider from "@material-ui/core/Divider";
import TextField from "@material-ui/core/TextField";
import Button from "@material-ui/core/Button";
import Table from "@material-ui/core/Table";
import TableHead from "@material-ui/core/TableHead";
import TableRow from "@material-ui/core/TableRow";
import TableCell from "@material-ui/core/TableCell";
import TableBody from "@material-ui/core/TableBody";
import TableFooter from "@material-ui/core/TableFooter";
import { changeDistributor, fetchNumber, changeQty, changeProduct, removeLine, addLine, fetchProductDetails, clearPage, save, changeSalesRep, changeSite } from "../../../actions/Distributor/PurchaseOrder";
import { connect } from "react-redux";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import { DISTRIBUTOR_TYPE, SALES_REP_TYPE } from "../../../constants/config";
import PurchaseOrderLine from "./PurchaseOrderLine";
import { alertDialog } from "../../../actions/Dialogs";
import CloudUploadIcon from "@material-ui/icons/CloudUpload";
import { Link } from "react-router-dom";

const styler = withStyles(theme => ({
    grow: {
        flexGrow: 1
    },
    fieldContainer: {
        width: 260,
        marginLeft: 20
    },
    button: {
        margin: theme.spacing.unit
    },
    darkCell: {
        background: theme.palette.grey[600],
        color: theme.palette.common.white,
        border: 'solid 1px ' + theme.palette.common.white,
        padding: theme.spacing.unit
    },
}));

const mapStateToProps = state => ({
    ...state.PurchaseOrder
});

const mapDispatchToProps = dispatch => ({
    onChangeDistributor: distributor => dispatch(changeDistributor(distributor)),
    onChangeDistributorRep: distributorRep => dispatch(changeSalesRep(distributorRep)),
    onFetchNumber: distributor => dispatch(fetchNumber(distributor)),
    onChangeQty: (number, qty) => dispatch(changeQty(number, qty)),
    onChangeProduct: (number, product) => dispatch(changeProduct(number, product)),
    onChangePrice: (number, price) => dispatch(changePrice(number, price)),
    onRemoveLine: (number) => dispatch(removeLine(number)),
    onAddLine: () => dispatch(addLine()),
    onMessage: (message, type) => dispatch(alertDialog(message, type)),
    onFetchProdutDetails: (number, product,distributor) => dispatch(fetchProductDetails(number, product,distributor)),
    onClearPage: () => dispatch(clearPage()),
    onSave: (distributor, dsr, site, lines) => dispatch(save(distributor, dsr, site, lines)),
    onChangeSite: site => dispatch(changeSite(site))
})

class PurchaseOrder extends Component {

    constructor(props) {
        super(props);

        this.handleChangeDistributor = this.handleChangeDistributor.bind(this);
        this.handleAddLine = this.handleAddLine.bind(this);
        this.handleChangeProduct = this.handleChangeProduct.bind(this);
        this.handleSaveClick = this.handleSaveClick.bind(this);
    }

    handleChangeDistributor(distributor) {
        this.props.onChangeDistributor(distributor);
        this.props.onFetchNumber(distributor);
    }

    handleAddLine() {
        const { lastId, lines, onMessage, onAddLine } = this.props;

        const line = lines[lastId];

        if (line&&(!line.product || !line.qty) ){
            onMessage("Please fill last line before adding new line.", "error");
            return;
        }

        onAddLine();
    }

    handleChangeProduct(number, product) {
        const { lines, onMessage, onChangeProduct, onFetchProdutDetails,distributor } = this.props;

        let products = Object.values(lines).map((line, key) => (
            line.product ? line.product.value : null
        ));

        if(products.includes(product.value)){
            onMessage("Product Already seleted.", "error");
            return;
        }

        onChangeProduct(number, product);
        onFetchProdutDetails(number, product,distributor);
    }

    handleSaveClick() {
        const { distributor, lines, onSave, dsr, site } = this.props;
        onSave(distributor, dsr, site, lines);
    }

    render() {
        const {
            classes,
            distributor,
            dsr,
            number,
            lines,
            site,
            onChangeQty,
            onRemoveLine,
            onClearPage,
            onChangeDistributorRep,
            onChangeSite
        } = this.props;
        return (
            <Layout sidebar >
                <Typography variant="h5" align="center">Purchase Order</Typography>
                <Toolbar>
                    <div className={classes.fieldContainer} >
                        <AjaxDropdown
                            where={{
                                u_tp_id: DISTRIBUTOR_TYPE
                            }}
                            label="Distributor"
                            link="user"
                            value={distributor}
                            onChange={this.handleChangeDistributor}
                        />
                    </div>
                    <div className={classes.fieldContainer} >
                        <AjaxDropdown
                            where={{
                                u_tp_id: SALES_REP_TYPE,
                                dis_id: distributor
                            }}
                            label="Distributor Sales Rep"
                            link="user"
                            value={dsr}
                            key={distributor ? distributor.value : 3}
                            otherValues={{ dis_id: distributor }}
                            onChange={onChangeDistributorRep}
                        />
                    </div>
                    <div className={classes.fieldContainer} >
                        <AjaxDropdown
                            where={{
                                dis_id: distributor
                            }}
                            label="Site"
                            link="site"
                            value={site}
                            key={distributor ? distributor.value : 4}
                            otherValues={{ dis_id: distributor }}
                            onChange={onChangeSite}
                        />
                    </div>
                    <div className={classes.fieldContainer}>
                        <TextField value={number ? number : ""} fullWidth placeholder="PO Number" margin="dense" variant="outlined" />
                    </div>
                    <Button className={classes.button} onClick={this.handleSaveClick} variant="contained" margin="dense" color="primary" >Save</Button>
                    <Button className={classes.button} onClick={onClearPage} variant="contained" margin="dense" color="secondary" >Cancel</Button>
                    <Button
                        variant="contained"
                        color="secondary"
                        className={classes.button}
                        component={Link}
                        to="/sales/other/upload_csv/purchase_order"
                        disabled={true}
                    >
                        <CloudUploadIcon />
                        Upload
                    </Button>
                </Toolbar>
                <Divider />
                {distributor ?
                    <Table id="exportMe" >
                        <TableHead>
                            <TableRow>
                                <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                    style={{ width: 28 }}
                                >
                                    #
                            </TableCell>
                                <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                    style={{ width: 80 }}
                                >
                                    Product Code
                            </TableCell>
                                <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                    style={{ width: 260 }}
                                >
                                    Product
                            </TableCell>
                            <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                    style={{ width: 80 }}
                                >
                                    Pack Size
                            </TableCell>

                            <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                    style={{ width: 60 }}
                            >
                                    Stock In Hand
                            </TableCell>
                            <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                    style={{ width: 60 }}
                            >
                                    Pending GRN Qty
                            </TableCell>
                            <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                    style={{ width: 80 }}
                                >
                                    Price
                            </TableCell>
                            <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                    style={{ width: 160 }}
                            >
                                    Qty
                            </TableCell>
                            <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                    style={{ width: 100 }}
                            >
                                    Amount
                            </TableCell>
                            <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                    style={{ width: 90 }}
                            >
                                    Add
                            </TableCell>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {Object.values(lines).map((line, key) => (
                                <PurchaseOrderLine
                                    product={line.product}
                                    index={key}
                                    price={line.price}
                                    qty={line.qty}
                                    number={line.id}
                                    packSize={line.packSize}
                                    code={line.code}
                                    key={key}
                                    onChangeQty={onChangeQty}
                                    onChangeProduct={this.handleChangeProduct}
                                    onRemoveLine={onRemoveLine}
                                    onAddLine={this.handleAddLine}
                                    stockInHand={line.stockInHand}
                                    stockPending={line.stockPending}
                                    otherLineCount={Object.keys(lines).length}
                                />
                            ))}
                        </TableBody>
                        <TableFooter>
                            <TableRow>
                                <TableCell colSpan={6} >
                                    <Typography variant="caption" align="right">Amount</Typography>
                                    <Typography variant="h6" align="right" >{Object.values(lines).map(line => line.qty * line.price).reduce((a, b) => a + b, 0).toFixed(2)}</Typography>
                                </TableCell>
                            </TableRow>
                        </TableFooter>
                    </Table>
                    : null}
            </Layout>
        )
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(styler(PurchaseOrder));
