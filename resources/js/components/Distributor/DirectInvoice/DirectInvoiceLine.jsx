import React, {Component} from 'react';

import TableRow from '@material-ui/core/TableRow';
import Button from '@material-ui/core/Button';
import PlusIcon from "@material-ui/icons/Add";
import CloseIcon from "@material-ui/icons/Close";
import EditIcon from "@material-ui/icons/Edit";
import TableCell from '@material-ui/core/TableCell';
import TextField from '@material-ui/core/TextField';
import withStyles from "@material-ui/core/styles/withStyles";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";

import AjaxDropdown from '../../CrudPage/Input/AjaxDropdown';
import IconButton from '@material-ui/core/IconButton';


const styler = withStyles(theme=>({
    lastButton:{
        minWidth:32,
        paddingRight: 2,
        paddingLeft: 2,
        marginRight: theme.spacing.unit
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
}));

class DirectInvoiceLine extends Component {

    constructor(props){
        super(props);

        this.state = {
            batchEdit: true
        };

        this.handleChangeDiscount = this.handleChangeDiscount.bind(this);
        this.handleChangeProduct = this.handleChangeProduct.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleRemoveClick = this.handleRemoveClick.bind(this);
        this.handleClickBatchEditButton = this.handleClickBatchEditButton.bind(this);
    }

    handleChangeProduct(product){
        const {id,onChangeProduct} = this.props;

        onChangeProduct(id,product);
    }

    handleChangeQty(e){
        const {id,onChangeQty,stock} = this.props;

        const qty = parseInt(e.target.value);

        if(isNaN(qty)||qty<0||qty>parseInt(stock))
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

    handleClickBatchEditButton(){
        const {id, onBatchEditClick} = this.props;

        onBatchEditClick(id);
    }

    render(){
        const {
            index,
            product,
            qty,
            discount,
            price,
            stock,
            otherLineCount,
            onAdd,
            salesman,
            classes, 
            batchDetails,
            availableBatches,
            distributor
        } = this.props;

        const last = otherLineCount== index;

        let batchEdited = false;

        for( const availableBatch of Object.values(availableBatches)){
            if(availableBatch.qty){
                batchEdited = true;
            }
        }

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
                    <AjaxDropdown where={{dsr_id:salesman, dis_id:distributor}} key={salesman?salesman.value:0} value={product} onChange={this.handleChangeProduct} link="product" label="Product" />
                </TableCell>
                <TableCell
                    align='left'
                    padding='dense'
                >
                    {price}
                </TableCell>
                <TableCell
                    align='left'
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
                    align='left'
                    padding='dense'
                >
                    {stock}
                </TableCell>
                <TableCell
                    align='left'
                    padding='dense'
                    style={{width:180}}
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
                    style={{width:180}}
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
                >
                    { ( qty * price * ((100-discount)/100) ).toFixed(2)}
                </TableCell>
                <TableCell style={{paddingLeft:2,paddingRight:2}} >
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

export default styler(DirectInvoiceLine);