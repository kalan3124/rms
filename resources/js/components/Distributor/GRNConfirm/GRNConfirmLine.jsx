import React, {Component} from 'react';

import TableRow from '@material-ui/core/TableRow';
import TableCell from '@material-ui/core/TableCell';
import TextField from '@material-ui/core/TextField';

class GRNConfirmLine extends Component {

    constructor(props){
        super(props);

        this.handleChangeQty = this.handleChangeQty.bind(this);

    }

    handleChangeQty(e){
        const {id,orgQty} = this.props;
        var qty = e.target.value;
        if(qty<0||orgQty<qty){
            return;
        }

        this.props.onChangeQty(id,e.target.value)
    }

    render(){
        const {
            index,
            product,
            batch,
            expired,
            price,
            qty
        } = this.props;
        return  (
            <TableRow>
                <TableCell
                    align='left'
                    padding='dense'
                    style={{width:20}}
                >
                    {index}
                </TableCell>
                <TableCell
                    align='left'
                    padding='dense'
                >
                    {product?product.label:null}
                </TableCell>
                <TableCell
                    align='left'
                    padding='dense'
                >
                    {batch?batch.value:null}
                </TableCell>
                <TableCell
                    align='left'
                    padding='dense'
                >
                    {expired}
                </TableCell>
                <TableCell
                    align='left'
                    padding='dense'
                    style={{width:50}}
                >
                    {price}
                </TableCell>
                <TableCell
                    align='left'
                    padding='dense'
                    style={{width:50}}
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
                >
                    { ( qty * price ).toFixed(2)}
                </TableCell>
            </TableRow>
        )
    }
}

export default GRNConfirmLine;