import React, {Component} from "react";
import {connect} from "react-redux";
import withRouter from "react-router/withRouter";

import SearchIcon from "@material-ui/icons/Search";
import SaveIcon from "@material-ui/icons/Save";
import VpnKeyIcon from "@material-ui/icons/VpnKey";

import Typography from "@material-ui/core/Typography";
import Toolbar from "@material-ui/core/Toolbar";
import withStyles from "@material-ui/core/styles/withStyles";
import TextField from "@material-ui/core/TextField";
import Button from "@material-ui/core/Button";
import Divider from "@material-ui/core/Divider";
import Table from "@material-ui/core/Table";
import TableHead from "@material-ui/core/TableHead";
import TableRow from "@material-ui/core/TableRow";
import TableCell from "@material-ui/core/TableCell";
import TableBody from "@material-ui/core/TableBody";
import Paper from "@material-ui/core/Paper";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import ListItemSecondaryAction from "@material-ui/core/ListItemSecondaryAction";

import {
    changeSONumber,
    fetchSalesOrderDetails,
    changeQty,
    changeDiscount,
    save,
    fetchBonus,
    changeBonus,
    requestApproval,
    fetchBatchDetails,
    openBatchEditForm,
    changeBatchQty,
    cancelBatchEditForm
} from "../../../actions/Distributor/CreateInvoice";
import InvoiceLine from "./InvoiceLine";
import Layout from "../../App/Layout";
import BatchEditForm from './BatchEditForm';

const styler = withStyles(theme=>({
    grow: {
        flexGrow:1
    },
    field: {
        width: 240
    },
    margin: {
        margin: theme.spacing.unit
    },
    darkCell:{
        background: theme.palette.grey[600],
        color: theme.palette.common.white,
        border: 'solid 1px '+theme.palette.common.white
    }
}));

export const mapStateToProps = state =>({
    ...state,
    ...state.CreateInvoice
});

export const mapDispatchToProps = dispatch =>({
    onChangeSONumber: number=>dispatch(changeSONumber(number)),
    onSearch: soNumber=>dispatch(fetchSalesOrderDetails(soNumber)),
    onChangeQty: (id,qty)=>dispatch(changeQty(id,qty)),
    onChangeDiscount: (id,discount)=>dispatch(changeDiscount(id,discount)),
    onSave: (soNumber,details,discount,bonusDetails)=>dispatch(save(soNumber,details,discount,bonusDetails)),
    onRequestApproval: (soNumber,details,discount,bonusDetails)=>dispatch(requestApproval(soNumber,details,discount,bonusDetails)),
    onFetchBonus: (soNumber,details)=>dispatch(fetchBonus(soNumber,details)),
    onChangeBonusQty: (id,productId,qty)=>dispatch(changeBonus(id,productId,qty)),
    onFetchBatchDetails: (id,soNumber,product,qty)=>dispatch(fetchBatchDetails(id,soNumber,product,qty)),
    onOpenBatchEditForm: (line) => dispatch(openBatchEditForm(line)),
    onChangeBatchQty: (batch, qty) => dispatch(changeBatchQty(batch, qty)),
    onCancelBatchEditForm: ()=>dispatch(cancelBatchEditForm())
});

class CreateInvoice extends Component {

    constructor(props){
        super(props);

        this.handleChangeSONumber = this.handleChangeSONumber.bind(this);
        this.handleClickSearch = this.handleClickSearch.bind(this);
        this.handleClickSave = this.handleClickSave.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleClickBatchEditButton = this.handleClickBatchEditButton.bind(this);
        this.handleConfirmBatchForm = this.handleConfirmBatchForm.bind(this);

        if(typeof this.props.match.params.number!=='undefined'){
            this.props.onChangeSONumber(this.props.match.params.number.split("_").join("/").toUpperCase());
            this.props.onSearch(this.props.match.params.number.split("_").join("/").toUpperCase());
        }
    }

    componentDidUpdate(prevProps){
        if(this.props.match.params.number!=prevProps.match.params.number&&typeof this.props.match.params.number !=='undefined'){
            this.props.onChangeSONumber(this.props.match.params.number.split("_").join("/").toUpperCase());
            this.props.onSearch(this.props.match.params.number.split("_").join("/").toUpperCase());
        }
    }

    handleChangeSONumber(e){
        this.props.onChangeSONumber(e.target.value);
    }

    handleClickSearch(){
        const {soNumber, onSearch} = this.props;

        onSearch(soNumber);
    }

    handleClickSave(){
        const {soNumber,details,discount,bonusDetails} = this.props;

        if(this.wantConfrimation())
            this.props.onRequestApproval(soNumber,details,discount,bonusDetails);
        else
            this.props.onSave(soNumber,details,discount,bonusDetails);
    }

    handleChangeQty(id,qty){
        const {onChangeQty,onFetchBonus,details,soNumber,onFetchBatchDetails} = this.props;

        if(soNumber||details){
            onFetchBonus(soNumber,{...details,[id]:{...details[id],invoiceQty:qty}});
            onFetchBatchDetails(id,soNumber,details[id].product,qty);
        }

        onChangeQty(id,qty);
    }

    handleChangeBonusQty(id,productId){
        return e=>{
            let qty = parseInt(e.target.value);

            if(isNaN(qty)||qty<0)
                return;

            this.props.onChangeBonusQty(id,productId,qty);
        }
    }

    wantConfrimation(){
        const {bonusDetails} = this.props;

        let wantConfrimation = false;

        Object.values(bonusDetails).forEach((line)=>{
            let qty = 0;
            Object.values(line.products).forEach((product)=>{
                if(product.qty)
                    qty += product.qty;
            });

            if(qty>line.qty)
                wantConfrimation = true;

        });

        return wantConfrimation;
    }

    handleClickBatchEditButton(id) {
        const { onOpenBatchEditForm } = this.props;

        onOpenBatchEditForm(id);
    }

    handleConfirmBatchForm() {
        const { onOpenBatchEditForm } = this.props;

        onOpenBatchEditForm(undefined);
    }

    render(){
        const {
            classes,
            soNumber,
            details,
            loaded,
            onChangeDiscount,
            bonusDetails,
            remark,
            batchEditLine,
            onChangeBatchQty,
            onCancelBatchEditForm
        } = this.props;

         // Get already filled bonus ids
         let existBonusIds = [];

         Object.values(bonusDetails).map((line,lineKey)=>{
             Object.values(line.products).map((product,productKey)=>{
                 if(product.qty&&!existBonusIds.includes(line.id))
                     existBonusIds.push(line.id)
             });
         });

         const wantConfrimation = this.wantConfrimation();

        return (
            <Layout sidebar={true}>
                <Toolbar>
                    <Typography variant="h5">Invoice Creation</Typography>
                    <div className={classes.grow}/>
                    <div className={classes.field}>
                        <TextField onChange={this.handleChangeSONumber} value={soNumber} label="Sales Order Number" fullWidth={true} margin="dense" variant="outlined"/>
                    </div>
                    <Button onClick={this.handleClickSearch} className={classes.margin} variant="contained" color="default" >
                        <SearchIcon />
                        Search
                    </Button>
                    {wantConfrimation?
                        <Button onClick={this.handleClickSave} variant="contained" color="secondary" className={classes.margin}>
                            <VpnKeyIcon />
                            Request Approval
                        </Button>
                    :
                        <Button onClick={this.handleClickSave} className={classes.margin} variant="contained" color="primary">
                            <SaveIcon />
                            Save
                        </Button>
                    }
                </Toolbar>
                <Divider/>
                {!loaded?
                <Typography variant="caption" align="center">
                    Please search for a sales order to invoice!
                </Typography>
                :
                <div>
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                    style={{width:20}}
                                >
                                    #
                                </TableCell>
                                <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                >
                                    Product
                                </TableCell>
                                <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                >
                                    Unit Price
                                </TableCell>
                                <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                >
                                    Batch(s)
                                </TableCell>
                                <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                >
                                    Available Stock
                                </TableCell>
                                <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                    style={{width:30}}
                                >
                                    SO Qty
                                </TableCell>
                                <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                >
                                    SO Amount
                                </TableCell>
                                <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                    style={{width:40}}
                                >
                                    Invoice Qty
                                </TableCell>
                                <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                    style={{width:40}}
                                >
                                    Discount
                                </TableCell>
                                <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                >
                                    Invoice Amount
                                </TableCell>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {Object.values(details).map((detail,key)=>(
                                <InvoiceLine
                                    onChangeDiscount={onChangeDiscount}
                                    onChange={this.handleChangeQty}
                                    {...detail}
                                    key={key}
                                    index={key+1}
                                    onBatchEditClick={this.handleClickBatchEditButton}
                                />
                            ))}
                        </TableBody>
                    </Table>
                    <Toolbar variant="dense" >
                        <Typography variant="h6">Discount:- </Typography>
                        <TextField
                            variant="outlined"
                            margin="dense"
                            style={{width:240}}
                            value={Object.values(details).map(line=>line.invoiceQty*line.unitPrice*(line.discountPercent/100)).reduce((a, b) => a + b, 0).toFixed(2)}
                            className={classes.margin}
                            type="number"
                            disabled={true}
                        />
                        <Typography variant="h6">Amount:- </Typography>
                        <TextField
                            variant="outlined"
                            margin="dense"
                            style={{width:240}}
                            value={Object.values(details).map(line=>line.invoiceQty*line.unitPrice*((100-line.discountPercent)/100)).reduce((a, b) => a + b, 0).toFixed(2)}
                            className={classes.margin}
                        />
                        <div className={classes.grow}/>
                        <Typography variant="h6">Remark:- </Typography>
                        <TextField
                            variant="outlined"
                            margin="dense"
                            style={{width:240}}
                            value={remark}
                            className={classes.margin}
                        />
                    </Toolbar>
                    <Divider/>

                    <Paper>
                        <Typography className={classes.darkCell} variant="h5">Bonus</Typography>
                        {Object.values(bonusDetails).map((line,lineKey)=>(
                            <div className={classes.margin} key={lineKey}>
                                <Divider />
                                <Typography variant="h6">{line.label} ({line.qty} Free)</Typography>
                                <List variant="dense" >
                                    {Object.values(line.products).map((product,key)=>(
                                        <ListItem divider={true} button={true} key={key} >
                                            <ListItemText>{product.label}</ListItemText>
                                            <ListItemSecondaryAction>
                                                <TextField
                                                    disabled={existBonusIds.filter(id=>line.excludes.includes(id)).length>0}
                                                    label="Qty"
                                                    variant="outlined"
                                                    margin="dense"
                                                    type="number"
                                                    value={product.qty}
                                                    onChange={this.handleChangeBonusQty(line.id,product.value)}
                                                />
                                            </ListItemSecondaryAction>
                                        </ListItem>
                                    ))}
                                </List>
                            </div>
                        ))}
                    </Paper>
                    <BatchEditForm
                        onClose={onCancelBatchEditForm}
                        onConfirm={this.handleConfirmBatchForm}
                        details={typeof batchEditLine !== 'undefined' ? details[batchEditLine].availableBatches : {}}
                        open={typeof batchEditLine !== 'undefined'}
                        onChange={ onChangeBatchQty }
                        qty={details[batchEditLine]?details[batchEditLine].qty:0}
                    />
                </div>
                }
            </Layout>
        )
    }
}

export default connect(mapStateToProps,mapDispatchToProps)( styler ( withRouter (CreateInvoice)));
