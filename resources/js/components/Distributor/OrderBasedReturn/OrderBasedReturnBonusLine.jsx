import React, {Component} from 'react';
import TableRow from '@material-ui/core/TableRow';
import TableCell from '@material-ui/core/TableCell';
import TextField from '@material-ui/core/TextField';

class OrderBasedReturnBonusLine extends Component {

    constructor(props){
        super(props);

        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleLostFocusQty = this.handleLostFocusQty.bind(this);
    }

    render(){

        const {index,scheme,product,batch,showScheme,showProduct,qty,schemeRowspan,productRowspan,freeQty,issuedQty,sumQty} = this.props;
        return (
            <TableRow >
                {showScheme?[
                <TableCell key={0} rowSpan={schemeRowspan} style={{width:40}} >
                    {index}
                </TableCell>,
                <TableCell key={1} rowSpan={schemeRowspan}>
                 {scheme.label}
                </TableCell>,
                <TableCell rowSpan={schemeRowspan} key={2} >
                    {freeQty}
                </TableCell>
                ]
                :null}
                {showProduct?[
                <TableCell rowSpan={productRowspan} key={3} >
                    {product.label}
                </TableCell>
                ]:null}
                <TableCell >
                    {batch.label}
                </TableCell>
                <TableCell >
                    {issuedQty}
                </TableCell>
                <TableCell style={{width:160}} >
                    <TextField onBlur={this.handleLostFocusQty} variant="outlined" margin="dense" type="number" value={qty} onChange={this.handleChangeQty} />
                </TableCell>
            </TableRow>
        )
    }

    handleChangeQty(e){
        const {product,batch,id,qty,sumQty,freeQty,onChange,issuedQty} = this.props;

        let newQty = e.target.value; 

        if(newQty&&newQty!=""&&parseInt(newQty)+sumQty-parseInt(qty==""?"0":qty)>freeQty)
            newQty = freeQty+parseInt(qty==""?"0":qty)-sumQty;
        else if(newQty&&newQty!=""&&parseInt(issuedQty)<parseInt(newQty))
            newQty = issuedQty;
            
        onChange(id,product.value,batch.value,newQty);
    }


    handleLostFocusQty(e){
        const {product,batch,id,onChangeQty} = this.props;

        let qty = parseInt( e.target.value);

        if(isNaN(qty))
            onChangeQty(id,product.value,batch.value,0);
    }
}

export default OrderBasedReturnBonusLine;