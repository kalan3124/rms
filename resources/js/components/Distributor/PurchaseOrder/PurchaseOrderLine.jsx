import React, { Component } from "react";
import TableRow from "@material-ui/core/TableRow";
import TableCell from "@material-ui/core/TableCell";
import TextField from "@material-ui/core/TextField";
import Button from "@material-ui/core/Button";
import withStyles from "@material-ui/core/styles/withStyles";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import PlusIcon from "@material-ui/icons/Add";
import CloseIcon from "@material-ui/icons/Close";

const styler = withStyles(theme=>({
    cell:{
        padding: theme.spacing.unit
    },
    lastButton:{
        minWidth:32,
        paddingRight: 2,
        paddingLeft: 2,
        marginRight: theme.spacing.unit
    }
}))

class PurchaseOrderLine extends Component {

    constructor(props) {
        super(props);

        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleChangeProduct = this.handleChangeProduct.bind(this);
        this.handleRemove = this.handleRemove.bind(this);
    }

    handleChangeQty(e) {
        this.props.onChangeQty(this.props.number, e.target.value);
    }

    handleChangeProduct(product) {
        this.props.onChangeProduct(this.props.number, product);
    }

    handleRemove() {
        this.props.onRemoveLine(this.props.number);
    }

    render() {
        const { product, price, qty, onAddLine, index, packSize, code, stockInHand, stockPending,classes,otherLineCount } = this.props;

        const last = otherLineCount == index+1;
        var nf = new Intl.NumberFormat();

        return (
            <TableRow>
                <TableCell className={classes.cell} >
                    {index + 1}
                </TableCell>
                <TableCell className={classes.cell} >
                    {code}
                </TableCell>
                <TableCell className={classes.cell} >
                    <AjaxDropdown onChange={this.handleChangeProduct} value={product} label="Product" link="product" />
                </TableCell>
                <TableCell className={classes.cell} >
                    {packSize}
                </TableCell>
                <TableCell className={classes.cell}  >
                    {stockInHand}
                </TableCell>
                <TableCell className={classes.cell}  >
                    {stockPending}
                </TableCell>
                <TableCell className={classes.cell}  >
                    {price}
                </TableCell>
                <TableCell className={classes.cell} >
                    <TextField style={{ minWidth: 90 }} onChange={this.handleChangeQty} value={qty} margin="dense" variant="outlined" label="Qty" />
                </TableCell>
                <TableCell className={classes.cell} >
                    {nf.format(qty * price)}
                </TableCell>
                <TableCell className={classes.cell} >
                    {index==0&&last?null:
                    <Button className={last?classes.lastButton:undefined} onClick={this.handleRemove} color="secondary" margin="dense" variant="contained">
                        <CloseIcon/>
                    </Button>}
                    {last?
                    <Button className={ index!==0?classes.lastButton:undefined } color="primary" onClick={onAddLine} margin="dense" variant="contained">
                        <PlusIcon/>
                    </Button>
                    :null}
                </TableCell>
            </TableRow>
        )
    }
}

export default styler (PurchaseOrderLine);