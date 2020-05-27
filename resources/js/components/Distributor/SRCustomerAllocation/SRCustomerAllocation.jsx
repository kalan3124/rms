import React , {Component} from 'react';
import { connect } from 'react-redux';
import { Link } from "react-router-dom";
import Typography from '@material-ui/core/Typography';

import Layout from '../../App/Layout';
import Divider from "@material-ui/core/Divider";
import Grid from "@material-ui/core/Grid";
import withStyles from "@material-ui/core/styles/withStyles";
import Toolbar from "@material-ui/core/Toolbar";
import Button from "@material-ui/core/Button"; 
import SearchAndCheckPanel from './SearchAndCheckPanel';
import PersonIcon from '@material-ui/icons/Person';
import AccountBalanceIcon from '@material-ui/icons/AccountBalance';
import CloudUploadIcon from "@material-ui/icons/CloudUpload";
import { addCustomer, addSr, removeCustomer, removeSr, fetchSrs, fetchCustomer, submit, fetchCustomersBySr } from '../../../actions/Distributor/SRCustomerAllocation';

const mapStateToProps = state =>({
    ...state.SrCustomerAllocation
});

const mapDispatchToProps = dispatch=>({
    onAddCustomer:(customer)=>dispatch(addCustomer(customer)),
    onAddSr:sr=>dispatch(addSr(sr)),
    onRemoveCustomer:(customer)=>dispatch(removeCustomer(customer)),
    onRemoveSr:(sr)=>dispatch(removeSr(sr)),
    onSearchSr:(keyword)=>dispatch(fetchSrs(keyword)),
    onSearchCustomer:(keyword)=>dispatch(fetchCustomer(keyword)),
    onSubmit:(srs,customers)=>dispatch(submit(srs,customers)),
    onLoadCustomersBySr:srId=>dispatch(fetchCustomersBySr(srId))
});

const styler = withStyles(theme=>({
    padding: {
        padding: theme.spacing.unit*2
    },
    dense:{
        flexGrow:1
    },userField: {
        padding: theme.spacing.unit*2
    },zIndex: {
        zIndex: 1200
    },
    button: {
        margin: theme.spacing.unit
    }
}))

class SrCustomerAllocation extends Component {

    constructor(props){
        super(props);
        this.handleCheckCustomer = this.handleCheckCustomer.bind(this);
        this.handleCheckSr = this.handleCheckSr.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleSearchCustomer = this.handleSearchCustomer.bind(this);
        this.handleSearchSr = this.handleSearchSr.bind(this);

        this.state = {
            count: 0
        }
    }

    handleCheckCustomer(customer,checked){
        const {
            onAddCustomer,
            onRemoveCustomer
        } = this.props;

        if(!checked){
            onRemoveCustomer(customer);
        } else {
            onAddCustomer(customer);
        }
    }

    handleCheckSr(sr,checked){
        
        const {
            onAddSr,
            onRemoveSr,
            onLoadCustomersBySr
        } = this.props;

        if(!checked){
            onRemoveSr(sr);
            this.setState({count: this.state.count - 1})
        } else {
            this.setState({count: this.state.count + 1})
            onAddSr(sr);
            onLoadCustomersBySr(sr.value);
        }
        
       
    }

    handleSubmit(){
        const {selectedCustomers,selectedSrs,onSubmit} = this.props;
        
            onSubmit(selectedSrs,selectedCustomers); 
    }

    handleSearchCustomer(keyword){
        const {onSearchCustomer} = this.props;

        onSearchCustomer(keyword);
    }

    handleSearchSr(keyword){
        const {onSearchSr} = this.props;

        onSearchSr(keyword);
    }

    render(){
        const {
            customerKeyword,
            srKeyword,
            customers,
            srs,
            selectedCustomers,
            selectedSrs,
            classes
        } = this.props;

        return (
            <Layout sidebar >
                <Toolbar variant="dense" className={classes.zIndex}>
                    <Typography variant="h5" >SR Customer Allocation</Typography>
                    <div className={classes.dense} />
                    <Button variant="contained" onClick={this.handleSubmit} color="secondary">Submit</Button>
                    <Button
                        variant="contained"
                        color="secondary"
                        className={classes.button}
                        component={Link}
                        to="/sales/other/upload_csv/dsr_customer"
                    >
                        <CloudUploadIcon />
                        Upload
                    </Button>
                </Toolbar>
                <Divider/>
                <Grid container>
                    <Grid className={classes.padding} item md={6}>
                        <SearchAndCheckPanel 
                            label="SR"
                            icon={<PersonIcon />}
                            keyword={srKeyword}
                            results={srs}
                            checked={selectedSrs}
                            onSearch={this.handleSearchSr}
                            onCheck={this.handleCheckSr}
                        />
                    </Grid>
                    <Grid className={classes.padding} item md={6}>
                        <SearchAndCheckPanel 
                            label="Customer"
                            icon={<AccountBalanceIcon />}
                            keyword={customerKeyword}
                            results={customers}
                            checked={selectedCustomers}
                            onSearch={this.handleSearchCustomer}
                            onCheck={this.handleCheckCustomer}
                        />
                    </Grid>
                </Grid>
            </Layout>
        )
    }
}

export default connect(mapStateToProps,mapDispatchToProps) ( styler (SrCustomerAllocation));