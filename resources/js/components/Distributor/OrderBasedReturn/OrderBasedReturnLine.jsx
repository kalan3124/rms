import React , {Component} from 'react';
import TableRow from '@material-ui/core/TableRow';
import TextField from '@material-ui/core/TextField';
import TableCell from '@material-ui/core/TableCell';
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import { Checkbox } from '@material-ui/core';

class OrderBasedReturnLine extends Component {

    constructor(props){
        super(props);

        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleLostFocusQty = this.handleLostFocusQty.bind(this);
        this.handleChangeSalable = this.handleChangeSalable.bind(this);
        this.handleChangeReason = this.handleChangeReason.bind(this);
    }

    render(){
        const {index,product,batch,orgQty,qty,discount,reason,salable} = this.props;
        return (
            <TableRow>
                <TableCell style={{width:40}}  >
                    {index}
                </TableCell>
                <TableCell  >
                    {product?product.label:"DELETED"}
                </TableCell>
                <TableCell  >
                    {batch?batch.label:"DELETED"}
                </TableCell>
                <TableCell  >
                    {orgQty}
                </TableCell>
                <TableCell  >
                    {discount}
                </TableCell>
                <TableCell style={{width:160,padding:0}}  >
                    <TextField onBlur={this.handleLostFocusQty} variant="outlined" margin="dense" type="number" value={qty} onChange={this.handleChangeQty} />
                </TableCell>
                <TableCell style={{width:160}}  >
                    <AjaxDropdown link="reason" where={{rsn_type:8}}  label="Reason" value={reason} onChange={this.handleChangeReason} />
                </TableCell>
                <TableCell   >
                    <Checkbox onChange={this.handleChangeSalable} checked={salable} />
                </TableCell>
            </TableRow>
        )
    }

    handleChangeQty(e){
        const {id,onChangeQty,orgQty} = this.props;

        let qty = e.target.value;

        if(qty&&qty!=""&&orgQty<qty)
            qty = orgQty;

        onChangeQty(id,qty)
    }

    handleLostFocusQty(e){
        const {id,onChangeQty} = this.props;

        let qty = parseInt( e.target.value);

        if(isNaN(qty))
            onChangeQty(id,0);
    }

    handleChangeReason(reason){
        const {id,onChangeReason} = this.props;

        onChangeReason(id,reason);
    }

    handleChangeSalable(e,salable){
        const {id,onChangeSalable} = this.props;

        onChangeSalable(id,salable);
    }
}

export default OrderBasedReturnLine;