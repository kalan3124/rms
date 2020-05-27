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
import { 
 changeQty,
 changeProduct,
 removeLine,
 addLine,
 fetchProductDetails,
 clearPage,
 save,
 changeNumber,
 fetchDetails
} from "../../../actions/Distributor/PurchaseOrderConfirm";
import { connect } from "react-redux";
import PurchaseOrderConfirmLine from "./PurchaseOrderConfirmLine";
import { alertDialog } from "../../../actions/Dialogs";
import withRouter from "react-router/withRouter";

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
    ...state.PurchaseOrderConfirm
});

const mapDispatchToProps = dispatch => ({
    onChangeQty: (number, qty) => dispatch(changeQty(number, qty)),
    onChangeProduct: (number, product) => dispatch(changeProduct(number, product)),
    onChangePrice: (number, price) => dispatch(changePrice(number, price)),
    onRemoveLine: (number) => dispatch(removeLine(number)),
    onAddLine: () => dispatch(addLine()),
    onMessage: (message, type) => dispatch(alertDialog(message, type)),
    onFetchProdutDetails: (id, product,number) => dispatch(fetchProductDetails(id, product,number)),
    onClearPage: () => dispatch(clearPage()),
    onSave: (number, lines) => dispatch(save(number, lines)),
    onChangeNumber: (number)=>dispatch(changeNumber(number)),
    onSearch: (number)=>dispatch(fetchDetails(number))
});

class PurchaseOrderConfirm extends Component {

    constructor(props) {
        super(props);

        this.handleAddLine = this.handleAddLine.bind(this);
        this.handleChangeProduct = this.handleChangeProduct.bind(this);
        this.handleSaveClick = this.handleSaveClick.bind(this);
        this.handleSearchClick = this.handleSearchClick.bind(this);
        this.handleChangeNumber = this.handleChangeNumber.bind(this);
        
        if(typeof this.props.match.params.number!=='undefined'){
            this.props.onChangeNumber(this.props.match.params.number.split("_").join("/").toUpperCase());
            this.props.onSearch(this.props.match.params.number.split("_").join("/").toUpperCase());
        }
    }

    componentDidUpdate(prevProps){
        if(this.props.match.params.number!=prevProps.match.params.number&&typeof this.props.match.params.number !=='undefined'){
            this.props.onChangeNumber(this.props.match.params.number.split("_").join("/").toUpperCase());
            this.props.onSearch(this.props.match.params.number.split("_").join("/").toUpperCase());
        }
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

    handleChangeProduct(id, product) {
        const { lines, onMessage, onChangeProduct, onFetchProdutDetails,number } = this.props;

        let products = Object.values(lines).map((line, key) => (
            line.product ? line.product.value : null
        ));

        if(products.includes(product.value)){
            onMessage("Product Already seleted.", "error");
            return;
        }
        
        onChangeProduct(id, product);
        onFetchProdutDetails(id, product,number);
    }

    handleChangeNumber(e){
        this.props.onChangeNumber(e.target.value);
    }

    handleSearchClick(){
        const {number,onSearch} = this.props;

        onSearch(number);
    }

    handleSaveClick() {
        const { number, lines, onSave } = this.props;
        onSave(number, lines);
    }

    render() {
        const {
            classes,
            number,
            lines,
            onChangeQty,
            onRemoveLine,
            onClearPage,
            searched,
            loading
        } = this.props;
        return (
            <Layout sidebar >
                <Toolbar>
                    <Typography variant="h5" align="center">Purchase Order Confirm</Typography>
                    <div className={classes.grow} />
                    <div className={classes.fieldContainer}>
                        <TextField value={number ? number : ""} onChange={this.handleChangeNumber} fullWidth placeholder="PO Number" margin="dense" variant="outlined" />
                    </div>
                    <Button className={classes.button} onClick={this.handleSearchClick} variant="contained" margin="dense" >Search</Button>
                    <Button className={classes.button} disabled={loading} onClick={this.handleSaveClick} variant="contained" margin="dense" color="primary" >Integrate</Button>
                    <Button className={classes.button} onClick={onClearPage} variant="contained" margin="dense" color="secondary" >Cancel</Button>
                </Toolbar>
                <Divider />
                {searched ?
                    <Table >
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
                                <PurchaseOrderConfirmLine
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
                    : 
                    <Typography align="center">Please search for a purchase order.</Typography>
                }
            </Layout>
        )
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(styler( withRouter (PurchaseOrderConfirm))); 