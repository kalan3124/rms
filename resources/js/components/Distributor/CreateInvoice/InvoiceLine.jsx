import React, { Component } from "react";
import TableRow from "@material-ui/core/TableRow";
import TextField from "@material-ui/core/TextField";
import TableCell from "@material-ui/core/TableCell";
import withStyles from "@material-ui/core/styles/withStyles";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import red from "@material-ui/core/colors/red";
import blue from "@material-ui/core/colors/blue";
import yellow from "@material-ui/core/colors/yellow";
import IconButton from '@material-ui/core/IconButton';
import EditIcon from "@material-ui/icons/Edit";

const styler = withStyles(theme=>({
    red: {
        background: red[400]
    },
    blue: {
        background: blue[400]
    },
    yellow: {
        background: yellow[400]
    },
    batchTableCell:{
        position: "relative",
        padding: 0
    },
    editButton: {
        position: "absolute",
        top: 0,
        right: 0,
        zIndex: 1201
    }
}))

class InvoiceLine extends Component {

    constructor(props){
        super(props);

        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleChangeDiscount = this.handleChangeDiscount.bind(this);
        this.handleClickBatchEditButton = this.handleClickBatchEditButton.bind(this);
    }

    handleChangeQty(e){
        const {onChange,availableStock,soQty,invoiceQty,id} = this.props;

        const qty = parseInt(e.target.value);

        if((availableStock<qty&&invoiceQty<qty) || (soQty<qty&&invoiceQty<qty)|| qty<0){
            return;
        }

        onChange(id,qty);
    }

    handleChangeDiscount(e){
        const {onChangeDiscount,id} = this.props;

        let discount = parseInt(e.target.value);

        if(discount<0 ||discount>=100)
            return;

        if(isNaN(discount))
            discount="" ;

        onChangeDiscount(id,discount)
    }

    handleClickBatchEditButton(){
        const {id, onBatchEditClick} = this.props;

        onBatchEditClick(id);
    }

    render() {
        const {
            product,
            unitPrice,
            availableStock,
            soQty,
            invoiceQty,
            index,
            classes,
            discountPercent,
            batchDetails,
            availableBatches
        } = this.props;

        let batchEdited = false;

        for( const availableBatch of Object.values(availableBatches)){
            if(availableBatch.qty){
                batchEdited = true;
            }
        }

        return (
            <TableRow className={invoiceQty>availableStock?classes.red:(invoiceQty==availableStock?classes.yellow:(invoiceQty!==soQty?classes.blue:undefined))} >
                <TableCell
                    align="left"
                    padding="dense"
                    style={{ width: 20 }}
                >
                    {index}
                </TableCell>
                <TableCell
                    align="left"
                    padding="dense"
                >
                    {product.label}
                </TableCell>
                <TableCell
                    align="left"
                    padding="dense"
                >
                    {unitPrice}
                </TableCell>
                <TableCell
                    align="left"
                    className={classes.batchTableCell}
                >
                    {Object.keys( batchEdited? availableBatches:  batchDetails).length?
                        <IconButton onClick={this.handleClickBatchEditButton} className={classes.editButton} size="small">
                            <EditIcon />
                        </IconButton>
                    :null}
                    <List dense={true}>
                        {Object.values(batchEdited? availableBatches:  batchDetails).map((batch,key)=>(
                            <ListItem key={key} dense={true} divider={true}>
                                <ListItemText primary={batch.code} secondary={batch.expire+" ("+batch.qty+")"}  />
                            </ListItem>
                        ))}
                    </List>
                </TableCell>
                <TableCell
                    align="left"
                    padding="dense"
                >
                    {availableStock}
                </TableCell>
                <TableCell
                    align="left"
                    padding="dense"
                    style={{ width: 50 }}
                >
                    {soQty}
                </TableCell>
                <TableCell
                    align="left"
                    padding="dense"
                >
                    {(soQty*unitPrice).toFixed(2)}
                </TableCell>
                <TableCell
                    align="left"
                    padding="dense"
                    style={{ width: 100 }}
                >
                    <TextField
                        placeholder="0"
                        value={invoiceQty}
                        variant="outlined"
                        margin="dense"
                        type="number"
                        onChange={this.handleChangeQty}
                    />
                </TableCell>
                <TableCell
                    align="left"
                    padding="dense"
                    style={{ width: 100 }}
                >
                    <TextField
                        placeholder="0"
                        value={discountPercent}
                        variant="outlined"
                        margin="dense"
                        type="number"
                        onChange={this.handleChangeDiscount}
                    />
                </TableCell>
                <TableCell
                    align="left"
                    padding="dense"
                >
                    { ( invoiceQty*unitPrice*(100-discountPercent)/100).toFixed(2)}
                </TableCell>
            </TableRow>
        );
    }
}

export default styler (InvoiceLine);