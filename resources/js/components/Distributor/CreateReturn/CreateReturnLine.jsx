import React, {Component} from 'react';

import TableRow from '@material-ui/core/TableRow';
import Button from '@material-ui/core/Button';
import PlusIcon from "@material-ui/icons/Add";
import CloseIcon from "@material-ui/icons/Close";
import TableCell from '@material-ui/core/TableCell';
import TextField from '@material-ui/core/TextField';
import Checkbox from '@material-ui/core/Checkbox';
import withStyles from "@material-ui/core/styles/withStyles";

import AjaxDropdown from '../../CrudPage/Input/AjaxDropdown';


const styler = withStyles(theme=>({
    lastButton:{
        minWidth:28,
        paddingRight: 2,
        paddingLeft: 2,
        marginRight: 2
    },
    cell:{
        padding: theme.spacing.unit
    },
}))

class CreateReturnLine extends Component {

    constructor(props){
        super(props);

        this.handleChangeDiscount = this.handleChangeDiscount.bind(this);
        this.handleChangeProduct = this.handleChangeProduct.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleRemoveClick = this.handleRemoveClick.bind(this);
        this.handleChangeSalable = this.handleChangeSalable.bind(this);
        this.handleChangeReason = this.handleChangeReason.bind(this);
        this.handleChangeBatch = this.handleChangeBatch.bind(this);
    }

    handleChangeProduct(product){
        const {id,onChangeProduct} = this.props;

        onChangeProduct(id,product);
    }

    handleChangeQty(e){
        const {id,onChangeQty,stock} = this.props;

        const qty = parseInt(e.target.value);

        if(isNaN(qty)||qty<0)
            return;

        onChangeQty(id,qty);
    }

    handleChangeDiscount(e){
        const {id,onChangeDiscount} = this.props;

        const discount = e.target.value;

        if(discount<0||discount>99)
            return;

        onChangeDiscount(id,discount);
    }

    handleRemoveClick(){
        const {id, onRemove} = this.props;

        onRemove(id);
    }

    handleChangeSalable(e,v){
        const {onChangeSalable,id} = this.props;

        onChangeSalable(id,v);
    }

    handleChangeReason(reason){
        const {id,onChangeReason} = this.props;

        onChangeReason(id,reason);
    }

    handleChangeBatch(batch){
        const {id,onChangeBatch} = this.props;

        onChangeBatch(id,batch);
    }

    render(){
        const {
            index,
            product,
            qty,
            discount,
            price,
            onAdd,
            salable,
            reason,
            distributor,
            batch,
            salesman,
            otherLineCount,
            classes
        } = this.props;

        const last = otherLineCount== index;

        return  (
            <TableRow>
                <TableCell
                    align='left'
                    padding='dense'
                    style={{width:20}}
                    className={classes.cell}
                >
                    {index}
                </TableCell>
                <TableCell
                    align='left'
                    padding='dense'
                    className={classes.cell}
                >
                    <AjaxDropdown where={{dsr_id:salesman}} key={salesman?salesman.value:0} value={product} onChange={this.handleChangeProduct} link="product" label="Product" />
                </TableCell>
                <TableCell
                    align='left'
                    padding='dense'
                    className={classes.cell}
                >
                    <AjaxDropdown onChange={this.handleChangeBatch} value={batch} key={product?product.value:0} link="distributor_batch" where={{distributor,product}} label="Batch" />
                </TableCell>
                <TableCell
                    align='left'
                    padding='dense'
                    className={classes.cell}
                >
                    {price}
                </TableCell>
                <TableCell
                    align='left'
                    padding='dense'
                    className={classes.cell}
                >
                   <Checkbox checked={salable} onChange={this.handleChangeSalable} color="secondary" />
                </TableCell>
                <TableCell
                    align='left'
                    padding='dense'
                    style={{width:180}}
                    className={classes.cell}
                >
                    <TextField
                        label="Qty"
                        value={qty}
                        onChange={this.handleChangeQty}
                        margin="dense"
                        fullWidth={true}
                        variant="outlined"
                        type="number"
                    />
                </TableCell>
                <TableCell
                    align='left'
                    padding='dense'
                    style={{width:160}}
                    className={classes.cell}
                >
                     <TextField
                        label="Discount %"
                        value={discount}
                        onChange={this.handleChangeDiscount}
                        margin="dense"
                        fullWidth={true}
                        variant="outlined"
                        type="number"
                        step="0.01"
                    />
                </TableCell>
                <TableCell
                    align='left'
                    padding='dense'
                    className={classes.cell}
                >
                    { ( qty * price * ((100-discount)/100) ).toFixed(2)}
                </TableCell>
                <TableCell
                    align='left'
                    padding='dense'
                    style={{width:380}}
                    className={classes.cell}
                >
                    <AjaxDropdown value={reason} onChange={this.handleChangeReason} link="reason" where={{rsn_type:8}} label="Reason" />
                </TableCell>
                <TableCell style={{width:120}} >
                    {index==1&&last?null:
                    <Button className={last?classes.lastButton:undefined} onClick={this.handleRemoveClick} color="secondary" margin="dense" variant="contained">
                        <CloseIcon/>
                    </Button>}
                    {last?
                    <Button className={ index!==1?classes.lastButton:undefined } color="primary" onClick={onAdd} margin="dense" variant="contained">
                        <PlusIcon/>
                    </Button>
                    :null}
                </TableCell>
            </TableRow>
        )
    }
}

export default styler (CreateReturnLine);