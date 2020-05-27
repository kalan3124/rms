import React, { Component } from "react";
import Layout from "../../App/Layout";
import withStyles from "@material-ui/core/styles/withStyles";
import Typography from "@material-ui/core/Typography";
import Toolbar from "@material-ui/core/Toolbar";
import Divider from "@material-ui/core/Divider";
import TextField from "@material-ui/core/TextField";
import Button from "@material-ui/core/Button";
import Table from "@material-ui/core/Table";
import TableHead from "@material-ui/core/TableHead";
import TableRow from "@material-ui/core/TableRow";
import TableCell from "@material-ui/core/TableCell";
import TableBody from "@material-ui/core/TableBody";
import Select from "../../CrudPage/Input/Select";
import DatePicker from "../../CrudPage/Input/DatePicker";
import TableFooter from "@material-ui/core/TableFooter";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import Checkbox from '@material-ui/core/Checkbox';
import {
    clearPage,
    save,
    changeCheckedInvoices,
    changeDistributor,
    changeSalesman,
    changeCustomer,
    changeNumber,
    fetchDetails,
    changedData,
    changePaymentAmount,
    changePaymentType,
    changeChequeNo,
    changeChequeBank,
    changeChequeBranch,
    changeChequeDate,
} from "../../../actions/Distributor/InvoicePayment";
import { connect } from "react-redux";
// import InvoiceLine from "./InvoiceLine";
import { DISTRIBUTOR_TYPE, DISTRIBUTOR_SALES_REP_TYPE } from '../../../constants/config';
import { alertDialog } from "../../../actions/Dialogs";
import withRouter from "react-router/withRouter";

const styler = withStyles(theme => ({
    grow: {
        flexGrow: 1
    },
    fieldContainer: {
        width: '100%',
        marginLeft: 20
    },
    button: {
        margin: theme.spacing.unit
    },
    select: {
        width: 180,
        height: 35,
        // background:theme.palette.common.white
    },
    darkCell: {
        background: theme.palette.grey[600],
        color: theme.palette.common.white,
        border: 'solid 1px ' + theme.palette.common.white,
        padding: theme.spacing.unit
    },
    cell:{
        padding: theme.spacing.unit
    },
    background_color:{background:'#3ac280'},
}));

const mapStateToProps = state => ({
    ...state.InvoicePayment
});

const mapDispatchToProps = dispatch => ({
    onMessage: (message, type) => dispatch(alertDialog(message, type)),
    onClearPage: () => dispatch(clearPage()),
    onSave: (customer,payment,balance,pType,lines,c_no,c_bank,c_branch,c_date) => dispatch(save(customer,payment,balance,pType,lines,c_no,c_bank,c_branch,c_date)),
    onChangeNumber: (number)=>dispatch(changeNumber(number)),
    onChangeDistributor:(distributor)=>dispatch(changeDistributor(distributor)),
    onChangeSalesman:(salesman)=>dispatch(changeSalesman(salesman)),
    onChangeCustomer:(customer)=>dispatch(changeCustomer(customer)),
    onChangeCheckedInvoice:invoices=>dispatch(changeCheckedInvoices(invoices)),
    onSearch: (number,distributor,salesman,customer)=>dispatch(fetchDetails(number,distributor,salesman,customer)),
    onChangedData: (id,in_id,date,code,in_amount,os_amount,payment_amount,payment,balance,status) => dispatch(changedData(id,in_id,date,code,in_amount,os_amount,payment_amount,payment,balance,status)),
    onChangePaymentAmount: (payment)=>dispatch(changePaymentAmount(payment)),
    onChangePaymentType: (pType)=>dispatch(changePaymentType(pType)),
    onChangeChequeNo: (c_no)=>dispatch(changeChequeNo(c_no)),
    onChangeChequeBank: (c_bank)=>dispatch(changeChequeBank(c_bank)),
    onChangeChequeBranch: (c_branch)=>dispatch(changeChequeBranch(c_branch)),
    onChangeChequeDate: (c_date)=>dispatch(changeChequeDate(c_date)),
});

class InvoicePayment extends Component {

    constructor(props) {
        super(props);

        this.handleChangeSalesman = this.handleChangeSalesman.bind(this);
        this.handleSaveClick = this.handleSaveClick.bind(this);
        this.handleSearchClick = this.handleSearchClick.bind(this);
        this.handleChangeNumber = this.handleChangeNumber.bind(this);
        this.handleInvoiceCheckedChange = this.handleInvoiceCheckedChange.bind(this);
        this.handleChangePaymentAmount = this.handleChangePaymentAmount.bind(this);
        this.handlePaymentTypeChange = this.handlePaymentTypeChange.bind(this);

        this.handleChangeChequeNo = this.handleChangeChequeNo.bind(this);
        this.handleChangeChequeBank = this.handleChangeChequeBank.bind(this);
        this.handleChangeChequeBranch = this.handleChangeChequeBranch.bind(this);

        if(typeof this.props.match.params.number!=='undefined'){
            this.props.onChangeNumber(this.props.match.params.number.split("_").join("/").toUpperCase());
            this.props.onSearch(this.props.match.params.number.split("_").join("/").toUpperCase());
        }
    }

    componentDidUpdate(prevProps){
        if(this.props.match.params.number!=prevProps.match.params.number&&typeof this.props.match.params.number !=='undefined'){
            this.props.onChangeNumber(this.props.match.params.number.split("_").join("/").toUpperCase());
            this.props.onSearch(this.props.match.params.number.split("_").join("/").toUpperCase());
        }
    }

    handleChangeSalesman(salesman){
        const {onChangeSalesman,distributor,onFetchInvoiceNumber} = this.props;

        // onFetchInvoiceNumber(distributor,salesman);
        onChangeSalesman(salesman);
    }

    handleChangeNumber(e){
        this.props.onChangeNumber(e.target.value);
    }

    handleSearchClick(){
        const {number,distributor,salesman,customer,onSearch} = this.props;

        onSearch(number,distributor,salesman,customer);
    }


    handleInvoiceCheckedChange(index){
        return (e, value) => {
            const { onChangedData,payment,balance } = this.props;

            const { id,in_id,date,code,in_amount,os_amount,payment_amount } = this.props.lines[index];

            if (value) {
                onChangedData(id,in_id,date,code,in_amount,os_amount,payment_amount,payment,balance,(value ? true : false));
            }else{
                onChangedData(id,in_id,date,code,in_amount,os_amount,payment_amount,payment,balance,(value ? true : false));
            }
        }
    }

    handleChangePaymentAmount(e){
        const { onChangePaymentAmount } = this.props;
        // console.log(e.target.value);
        onChangePaymentAmount(e.target.value);
    }

    handleSaveClick() {

        const { customer,payment,balance,lines,onSave,saveStatus,pType,onMessage,c_no,c_bank,c_branch,c_date } = this.props;
        if(pType){
            onSave(customer,payment,balance,pType,lines,c_no,c_bank,c_branch,c_date);
        }else{
            onMessage("Payment Method Required.","error");
        }
    }

    handlePaymentTypeChange(e) {
        const { onChangePaymentType } = this.props;

        onChangePaymentType(e);
    }

    handleChangeChequeNo(e){
        const { onChangeChequeNo } = this.props;
        onChangeChequeNo(e.target.value);
    }

    handleChangeChequeBank(e){
        const { onChangeChequeBank } = this.props;
        onChangeChequeBank(e.target.value);
    }

    handleChangeChequeBranch(e){
        const { onChangeChequeBranch } = this.props;
        onChangeChequeBranch(e.target.value);
    }

    render() {
        const {
            classes,
            number,
            onChangeDistributor,
            distributor,
            salesman,
            onChangeCustomer,
            customer,
            lines,
            onClearPage,
            searched,
            payment,
            balance,
            cos_amount,
            pType,
            c_no,
            c_bank,
            c_branch,
            c_date,
            onChangeChequeDate,
            ifCheque
        } = this.props;
        var nf = new Intl.NumberFormat();
        return (
            <Layout sidebar >
                <Typography variant="h5" align="center">INVOICE PAYMENT</Typography>
                <Divider/>
                <Toolbar>
                    {/* <div className={classes.field} >
                        <AjaxDropdown
                            label="Distributor"
                            link="user"
                            onChange={onChangeDistributor}
                            value={distributor}
                            where={{
                                u_tp_id: DISTRIBUTOR_TYPE
                            }}
                        />
                    </div>
                    <div className={classes.field} >
                        <AjaxDropdown
                            label="Salesman"
                            link="user"
                            value={salesman}
                            onChange={this.handleChangeSalesman}
                            // key={typeof distributor ==='undefined'||!distributor?0:distributor.value}
                            where={{
                                u_tp_id: DISTRIBUTOR_SALES_REP_TYPE,
                                dis_id: typeof distributor ==='undefined'||!distributor?undefined:distributor.value
                            }}
                        />
                    </div> */}
                    <div className={classes.field} >
                        <AjaxDropdown
                            label="CUSTOMER"
                            link="distributor_customer"
                            value={customer}
                            onChange={onChangeCustomer}
                            // key={typeof distributor ==='undefined'||!distributor?0:distributor.value}
                            where={{
                                dis_id: typeof distributor ==='undefined'||!distributor?undefined:distributor.value
                            }}
                        />
                    </div>
                    <div className={classes.fieldContainer}>
                        <TextField
                            value={number}
                            onChange={this.handleChangeNumber}
                            fullWidth
                            placeholder="Invoice Number"
                            margin="dense"
                            variant="outlined"
                            label="INVOICE NUMBER"
                        />
                    </div>

                    <Button className={classes.button} onClick={this.handleSearchClick} variant="contained" margin="dense" >Search</Button>
                    {/* <Button className={classes.button} onClick={this.handleSaveClick} variant="contained" margin="dense" color="primary" >Integrate</Button> */}
                    <Button className={classes.button} onClick={onClearPage} variant="contained" margin="dense" color="secondary" >Cancel</Button>
                </Toolbar>
                <Divider />
                <Toolbar>
                    <div className={classes.fieldContainer}>
                        <TextField
                            readonly
                            value={nf.format(cos_amount)}
                            fullWidth
                            placeholder="0.00"
                            margin="dense"
                            variant="outlined"
                            label="TOTAL OUTSTANDING"
                        />
                    </div>
                    <div className={classes.fieldContainer}>
                        <TextField
                            disabled={parseFloat(payment>0?payment:0)==parseFloat(balance>0?balance:0)?false:true}
                            value={payment}
                            onChange={this.handleChangePaymentAmount}
                            fullWidth
                            placeholder="0.00"
                            margin="dense"
                            variant="outlined"
                            label="PAYMENT AMOUNT"
                        />
                    </div>
                    <div className={classes.fieldContainer}>
                        <TextField
                            readonly
                            value={nf.format(balance)}
                            fullWidth
                            placeholder="0.00"
                            margin="dense"
                            variant="outlined"
                            label="BALANCE AMOUNT"
                        />
                    </div>
                    <div className={classes.fieldContainer}>
                        <Select
                            className={classes.select}
                            options={{ 1: 'Cash', 2: 'Cheque' }}
                            label={'PAYMENT METHOD'}
                            margin="dense"
                            value={pType}
                            name={'p_type'}
                            onChange={this.handlePaymentTypeChange}
                        />
                    </div>
                    <Button
                        className={classes.button}
                        disabled={parseFloat(payment>0?payment:0)==parseFloat(balance>0?balance:0)?true:false}
                        onClick={this.handleSaveClick}
                        variant="contained"
                        margin="dense"
                    >Save</Button>
                </Toolbar>
                <Divider />
                {ifCheque ?
                    <Toolbar>
                        <div className={classes.fieldContainer}>
                            <TextField
                                value={c_no}
                                onChange={this.handleChangeChequeNo}
                                fullWidth
                                placeholder=""
                                margin="dense"
                                variant="outlined"
                                label="CHEQUE NO"
                            />
                        </div>
                        <div className={classes.fieldContainer}>
                            <TextField
                                value={c_bank}
                                onChange={this.handleChangeChequeBank}
                                fullWidth
                                placeholder=""
                                margin="dense"
                                variant="outlined"
                                label="BANK"
                            />
                        </div>
                        <div className={classes.fieldContainer}>
                            <TextField
                                value={c_branch}
                                onChange={this.handleChangeChequeBranch}
                                fullWidth
                                placeholder=""
                                margin="dense"
                                variant="outlined"
                                label="BRANCH"
                            />
                        </div>
                        <div className={classes.fieldContainer}>
                            <DatePicker
                                value={c_date}
                                label="BANKING DATE"
                                onChange={onChangeChequeDate}
                            />
                        </div>
                    </Toolbar>
                :
                    null
                }

                <Divider />
                {searched ?
                    <Table >
                        <TableHead>
                            <TableRow>
                                <TableCell
                                    align='center'
                                    padding='dense'
                                    className={classes.darkCell}
                                    // style={{ width: 25 }}
                                    colSpan={7}
                                >
                                    OUTSTANDING INVOICES
                                </TableCell>
                            </TableRow>
                            {parseFloat(payment>0?payment:0) > 0 ?
                                <TableRow>
                                    <TableCell
                                        align='left'
                                        padding='dense'
                                        className={classes.darkCell}
                                        // style={{ width: 25 }}
                                        colSpan={7}
                                    >
                                        Select Invoices need to pay
                                    </TableCell>
                                </TableRow>
                                :
                                null
                            }
                            <TableRow>
                                <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                    style={{ width: 25 }}
                                >
                                    Tick to Pay
                                </TableCell>
                                <TableCell
                                    align='left'
                                    padding='dense'
                                    className={classes.darkCell}
                                    style={{ width: 28 }}
                                >
                                    Invoice Date
                                </TableCell>
                                    <TableCell
                                        align='left'
                                        padding='dense'
                                        className={classes.darkCell}
                                        style={{ width: 80 }}
                                    >
                                    Invoice No
                                </TableCell>
                                    <TableCell
                                        align='left'
                                        padding='dense'
                                        className={classes.darkCell}
                                        style={{ width: 100 }}
                                    >
                                    Invoice Amount
                                </TableCell>
                                <TableCell
                                        align='left'
                                        padding='dense'
                                        className={classes.darkCell}
                                        style={{ width: 80 }}
                                    >
                                    O/S Amount
                                </TableCell>

                                <TableCell
                                        align='left'
                                        padding='dense'
                                        className={classes.darkCell}
                                        style={{ width: 60 }}
                                >
                                    Amount
                                </TableCell>
                                <TableCell
                                        align='left'
                                        padding='dense'
                                        className={classes.darkCell}
                                        style={{ width: 60 }}
                                >
                                    Balance
                                </TableCell>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {Object.values(lines).map((line, key) => {
                                return [
                                    <TableRow key={key} className={line.status?classes.background_color:null}>
                                        <TableCell className={classes.cell} >
                                            <Checkbox
                                                disabled={balance==0&&line.status==false?true:false}
                                                // checked={line.status}
                                                onChange={this.handleInvoiceCheckedChange(key)}/>
                                        </TableCell>
                                        <TableCell className={classes.cell} >
                                            {line.date}
                                        </TableCell>
                                        <TableCell className={classes.cell} >
                                            {line.code}
                                        </TableCell>
                                        <TableCell className={classes.cell} >
                                            {nf.format(line.in_amount)}
                                        </TableCell>
                                        <TableCell className={classes.cell} >
                                            {nf.format(line.os_amount)}
                                        </TableCell>
                                        <TableCell className={classes.cell} >
                                            {nf.format(line.payment_amount)}
                                        </TableCell>
                                        <TableCell className={classes.cell} >
                                            {nf.format(line.balance_amount)}
                                        </TableCell>
                                    </TableRow>
                                ];
                            })}
                        </TableBody>
                        {/* <TableFooter>
                            <TableRow>
                                <TableCell colSpan={6} >
                                    <Typography variant="caption" align="right">Amount</Typography>
                                    <Typography variant="h6" align="right" >{Object.values(lines).map(line => line.qty * line.price).reduce((a, b) => a + b, 0).toFixed(2)}</Typography>
                                </TableCell>
                            </TableRow>
                        </TableFooter> */}
                    </Table>
                :
                    <Typography align="center">Please search for a invoice or customer.</Typography>
                }
            </Layout>
        )
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(styler( withRouter (InvoicePayment)));
