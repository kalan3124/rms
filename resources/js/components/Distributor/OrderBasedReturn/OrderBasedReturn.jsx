import React , {Component} from 'react';
import {connect} from 'react-redux';
import Layout from '../../App/Layout';
import Typography from '@material-ui/core/Typography';
import Toolbar from '@material-ui/core/Toolbar';
import withStyles from '@material-ui/core/styles/withStyles';
import TextField from '@material-ui/core/TextField';
import Button from '@material-ui/core/Button';
import Divider from '@material-ui/core/Divider';
import Table from "@material-ui/core/Table";
import TableHead from "@material-ui/core/TableHead";
import TableRow from "@material-ui/core/TableRow";
import TableCell from "@material-ui/core/TableCell";
import TableBody from '@material-ui/core/TableBody';

import { changeInvoiceNumber, fetchInvoiceInfo, fetchBonus, save, changeBonusQty, changeQty, changeReason, changeSalable } from '../../../actions/Distributor/OrderBasedReturn';
import OrderBasedReturnLine from './OrderBasedReturnLine';
import OrderBasedReturnBonusLine from './OrderBasedReturnBonusLine';


const styler = withStyles(theme=>({
    grow: {
        flexGrow:1
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

const mapStateToProps = state=>({
    ...state.OrderBasedReturn
});

const mapDispatchToProps = dispatch =>({
    onChangeInvoiceNumber: (invNumber) =>dispatch(changeInvoiceNumber(invNumber)),
    onFetchInvoiceInfo: (invNumber)=>dispatch(fetchInvoiceInfo(invNumber)),
    onFetchBonusInfo: (invNumber,lines)=>dispatch(fetchBonus(invNumber,lines)),
    onSave:(invNumber,lines,bonusLines)=>dispatch(save(invNumber,lines,bonusLines)),
    onChangeQty:(id,qty)=>dispatch(changeQty(id,qty)),
    onChangeBonusQty: (id,productId,batchId,qty)=>dispatch(changeBonusQty(id,productId,batchId,qty)),
    onChangeReason:(id,reason)=>dispatch(changeReason(id,reason)),
    onChangeSalable:(id,salable)=>dispatch(changeSalable(id,salable))
});

class OrderBasedReturn extends Component {

    constructor(props){
        super(props);

        this.handleChangeInvoiceNumber = this.handleChangeInvoiceNumber.bind(this);
        this.handleClickSearchButton = this.handleClickSearchButton.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleSave = this.handleSave.bind(this);
    }

    render(){

        const {classes,loaded,lines,bonusLines,onChangeBonusQty,onChangeReason,onChangeSalable,invNumber,returnNumber} = this.props;

        return (
            <Layout sidebar={true}>
                <Toolbar variant="dense" >
                    <Typography variant="h5">Order Based Return</Typography>
                    <div className={classes.grow}/>
                    <TextField className={classes.margin} value={invNumber} onChange={this.handleChangeInvoiceNumber} variant="outlined" label="Invoice Number" margin="dense" />
                    {loaded?
                    <TextField disabled={true} className={classes.margin} value={returnNumber}  variant="outlined" label="Return Number" margin="dense" />
                    :null}
                    <Button className={classes.margin} onClick={this.handleClickSearchButton} variant="contained" >Search</Button>
                    <Button className={classes.margin} onClick={this.handleSave} variant="contained" color="primary" >Save</Button>
                </Toolbar>
                <Divider/>
                {loaded?
                <div>
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableCell style={{width:40}} className={classes.darkCell} >
                                    #
                                </TableCell>
                                <TableCell className={classes.darkCell} >
                                    Product
                                </TableCell>
                                <TableCell className={classes.darkCell} >
                                    Batch
                                </TableCell>
                                <TableCell className={classes.darkCell} >
                                    Qty
                                </TableCell>
                                <TableCell className={classes.darkCell} >
                                    Discount
                                </TableCell>
                                <TableCell className={classes.darkCell} >
                                    Return Qty
                                </TableCell>
                                <TableCell className={classes.darkCell} >
                                    Reason
                                </TableCell>
                                <TableCell className={classes.darkCell} >
                                    Salable
                                </TableCell>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {Object.values(lines).map((line,key)=>(
                                <OrderBasedReturnLine
                                    {...line}
                                    key={key}
                                    index={key+1}
                                    onChangeQty={this.handleChangeQty}
                                    onChangeReason={onChangeReason}
                                    onChangeSalable={onChangeSalable}
                                />
                            ))}
                        </TableBody>
                    </Table>
                    <Typography variant="h6" >Bonus Lines</Typography>
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableCell style={{width:40}} className={classes.darkCell} >
                                    #
                                </TableCell>
                                <TableCell className={classes.darkCell} >
                                    Scheme
                                </TableCell>
                                <TableCell className={classes.darkCell} >
                                    Free Qty
                                </TableCell>
                                <TableCell className={classes.darkCell} >
                                    Product
                                </TableCell>
                                <TableCell className={classes.darkCell} >
                                    Batch
                                </TableCell>
                                <TableCell className={classes.darkCell} >
                                    Issued Qty
                                </TableCell>
                                <TableCell style={{width:160}} className={classes.darkCell} >
                                    Qty
                                </TableCell>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {
                                Object.values(bonusLines).map((bonusLine,bonusKey)=>Object.values(bonusLine.products).map((product,productKey)=>(Object.values(product.batchWise).map((batch,batchKey)=>(
                                    <OrderBasedReturnBonusLine
                                        index={bonusKey+1}
                                        id={bonusLine.id}
                                        key={`${bonusKey}-${productKey}-${batchKey}`}
                                        product={{
                                            label:product.label,
                                            value: product.value
                                        }}
                                        batch={{
                                            label: batch.label,
                                            value: batch.value
                                        }}
                                        showScheme={productKey==0 && batchKey==0}
                                        showProduct={batchKey==0}
                                        scheme={{
                                            label: bonusLine.label,
                                            value: bonusLine.id
                                        }}
                                        issuedQty={batch.issuedQty}
                                        qty={batch.qty}
                                        freeQty={bonusLine.qty}
                                        sumQty={Object.values(bonusLine.products).map(
                                            product1=>Object.values(product1.batchWise).map(batch=>batch.qty!=""&&batch.qty?parseInt(batch.qty):0).reduce((c,d)=>c+d,0)
                                        ).reduce((a,b)=>a+b,0)}
                                        schemeRowspan={ productKey==0&&bonusKey==0 ? Object.values(bonusLine.products).map(
                                            product1=>Object.values(product1.batchWise).length
                                        ).reduce((a,b)=>a+b,0):0}
                                        productRowspan={batchKey==0?
                                            Object.values(product.batchWise).length
                                        :0}
                                        onChange={onChangeBonusQty}
                                    />
                                )))))
                            }
                        </TableBody>
                    </Table>
                </div>
                :<div>
                    <Typography align="center">Please search for a invoice number</Typography>
                </div>}
            </Layout>
        )
    }

    handleChangeInvoiceNumber(e){
        const {onChangeInvoiceNumber} = this.props;

        onChangeInvoiceNumber(e.target.value);
    }

    handleClickSearchButton(e){
        const {invNumber,onFetchInvoiceInfo} = this.props;

        onFetchInvoiceInfo(invNumber);
    }

    handleChangeQty(id,qty){
        const {lines,onChangeQty,invNumber,onFetchBonusInfo} = this.props;

        onChangeQty(id,qty);

        onFetchBonusInfo(invNumber,{...lines,[id]:{...lines[id],qty}});
    }

    handleSave(){
        const {lines,bonusLines,invNumber,onSave} = this.props;

        onSave(invNumber,lines,bonusLines);
    }
}

export default connect(mapStateToProps,mapDispatchToProps)( styler(OrderBasedReturn));