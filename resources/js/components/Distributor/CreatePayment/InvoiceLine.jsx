import React, { Component } from "react";
import TableRow from "@material-ui/core/TableRow";
import TableCell from "@material-ui/core/TableCell";
import TextField from "@material-ui/core/TextField";
import Checkbox from '@material-ui/core/Checkbox';
import Button from "@material-ui/core/Button";
import withStyles from "@material-ui/core/styles/withStyles";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import Check from "../../CrudPage/Input/Check";
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

class InvoiceLine extends Component {

    constructor(props) {
        super(props);

        // this.handleChangeQty = this.handleChangeQty.bind(this);
        // this.handleChangeProduct = this.handleChangeProduct.bind(this);
        // this.handleRemove = this.handleRemove.bind(this);
        // this.handleInvoiceChecked = this.handleInvoiceChecked.bind(this);
    }

    // handleChangeQty(e) {
    //     this.props.onChangeQty(this.props.number, e.target.value);
    // }

    // handleChangeProduct(product) {
    //     this.props.onChangeProduct(this.props.number, product);
    // }

    // handleRemove() {
    //     this.props.onRemoveLine(this.props.number);
    // }

    render() {
        const {
            classes,
            otherLineCount,
            index,
            distributor,
            salesman,
            customer,
            code,
            amount,
            key,
            di_id,
            invoices,
            onInvoiceChecked
        } = this.props;

        const last = otherLineCount == index+1;
        var nf = new Intl.NumberFormat();

        return (
            <TableRow key={index}>
                <TableCell className={classes.cell} >
                    <Checkbox
                        value={invoices}
                        onChange={this.props.onInvoiceChecked('fsdf')}
                        inputProps={{
                            'aria-label': 'primary checkbox',
                        }}
                        margin="dense"/>
                    {/* <Check checked={true} onChange={this.handleInvoiceChecked}></Check> */}
                </TableCell>
                <TableCell className={classes.cell} >
                    {distributor}
                </TableCell>
                <TableCell className={classes.cell} >
                    {salesman}
                </TableCell>
                <TableCell className={classes.cell} >
                    {customer}
                </TableCell>
                <TableCell className={classes.cell} >
                    {code}
                </TableCell>
                <TableCell className={classes.cell} >
                    {nf.format(amount)}
                </TableCell>
                <TableCell className={classes.cell} >
                    {key}
                </TableCell>
            </TableRow>
        )
    }
}

export default styler (InvoiceLine);
