import React, {Component} from "react";
import withStyles from "@material-ui/core/styles/withStyles";
import TableRow from "@material-ui/core/TableRow";
import { TableCell, TextField, Checkbox } from "@material-ui/core";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";

const styler = withStyles(theme=>({
    paddingZero: {
        padding: 0
    }
}))

class CompanyReturnLine extends Component {

    constructor(props){
        super(props);

        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleChangeReason = this.handleChangeReason.bind(this);
        this.handleChangeSalable = this.handleChangeSalable.bind(this);
    }

    handleChangeReason(reason){
        const {id,onChangeReason} = this.props;
        onChangeReason(id,reason);
    }

    handleChangeSalable(e,checked){
        const {id,onChangeSalable} = this.props;

        onChangeSalable(id,checked);
    }

    handleChangeQty(e){
        const {id,onChangeQty, orgQty} = this.props;

        const qty = parseInt(e.target.value);

        if(!isNaN(qty)&&orgQty<qty){
            onChangeQty(id,orgQty);
            return;
        }

        onChangeQty(id,e.target.value);
    }

    render(){
        const {
            index,
            product,
            batch,
            qty,
            salable,
            price,
            expire,
            orgQty,
            classes
        } = this.props;
        return (
            <TableRow>
                <TableCell className={classes.paddingZero} >
                    {index}
                </TableCell>
                <TableCell className={classes.paddingZero} >
                    {product.label}
                </TableCell>
                <TableCell className={classes.paddingZero} >
                    {batch.label}
                </TableCell>
                <TableCell className={classes.paddingZero} >
                    {price}
                </TableCell>
                <TableCell className={classes.paddingZero} >
                    {expire}
                </TableCell>
                <TableCell className={classes.paddingZero} >
                    <AjaxDropdown
                        label="Reason"
                        link="reason"
                        onChange={this.handleChangeReason}
                        where={{
                            rsn_type: 9
                        }}
                    />
                </TableCell>
                <TableCell >
                    <Checkbox
                        value={salable}
                        onChange={this.handleChangeSalable}
                    />
                </TableCell>
                <TableCell className={classes.paddingZero} >
                    {orgQty}
                </TableCell>
                <TableCell className={classes.paddingZero} >
                    <TextField
                        variant="outlined"
                        margin="dense"
                        value={qty}
                        label="Qty"
                        onChange={this.handleChangeQty}
                    />
                </TableCell>
            </TableRow>
        );
    }
}


export default styler (CompanyReturnLine);
