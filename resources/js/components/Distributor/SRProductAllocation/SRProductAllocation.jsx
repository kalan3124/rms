import React, { Component } from 'react';
import { connect } from 'react-redux';

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
import { Link } from "react-router-dom";
import { addProduct, addSr, removeProduct, removeSr, fetchSrs, fetchProduct, submit, fetchProductsBySr } from '../../../actions/Distributor/SRProductAllocation';

const mapStateToProps = state => ({
    ...state.SrProductAllocation
});

const mapDispatchToProps = dispatch => ({
    onAddProduct: (product) => dispatch(addProduct(product)),
    onAddSr: sr => dispatch(addSr(sr)),
    onRemoveProduct: (product) => dispatch(removeProduct(product)),
    onRemoveSr: (sr) => dispatch(removeSr(sr)),
    onSearchSr: (keyword) => dispatch(fetchSrs(keyword)),
    onSearchProduct: (keyword) => dispatch(fetchProduct(keyword)),
    onSubmit: (srs, products) => dispatch(submit(srs, products)),
    onLoadProductsBySr: srId => dispatch(fetchProductsBySr(srId))
});

const styler = withStyles(theme => ({
    padding: {
        padding: theme.spacing.unit * 2
    },
    dense: {
        flexGrow: 1
    }, userField: {
        padding: theme.spacing.unit * 2
    }, zIndex: {
        zIndex: 1200
    },
    button: {
        margin: theme.spacing.unit
    }
}))

class SRProductAllocation extends Component {

    constructor(props) {
        super(props);
        this.handleCheckProduct = this.handleCheckProduct.bind(this);
        this.handleCheckSr = this.handleCheckSr.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleSearchProduct = this.handleSearchProduct.bind(this);
        this.handleSearchSr = this.handleSearchSr.bind(this);

        this.state = {
            count: 0
        }
    }

    handleCheckProduct(product, checked) {
        const {
            onAddProduct,
            onRemoveProduct
        } = this.props;

        if (!checked) {
            onRemoveProduct(product);
        } else {
            onAddProduct(product);
        }
    }

    handleCheckSr(sr, checked) {

        const {
            onAddSr,
            onRemoveSr,
            onLoadProductsBySr
        } = this.props;

        if (!checked) {
            onRemoveSr(sr);
            this.setState({ count: this.state.count - 1 })
        } else {
            this.setState({ count: this.state.count + 1 })
            onAddSr(sr);
            onLoadProductsBySr(sr.value);
        }


    }

    handleSubmit() {
        const { selectedProducts, selectedSrs, onSubmit } = this.props;

        onSubmit(selectedSrs, selectedProducts);
    }

    handleSearchProduct(keyword) {
        const { onSearchProduct } = this.props;

        onSearchProduct(keyword);
    }

    handleSearchSr(keyword) {
        const { onSearchSr } = this.props;

        onSearchSr(keyword);
    }

    render() {
        const {
            productKeyword,
            srKeyword,
            products,
            srs,
            selectedProducts,
            selectedSrs,
            classes
        } = this.props;

        return (
            <Layout sidebar >
                <Toolbar variant="dense" className={classes.zIndex}>
                    <Typography variant="h5" >SR Product Allocation</Typography>
                    <div className={classes.dense} />
                    <Button variant="contained" onClick={this.handleSubmit} color="secondary">Submit</Button>
                    <Button
                        variant="contained"
                        color="secondary"
                        className={classes.button}
                        component={Link}
                        to="/sales/other/upload_csv/sr_product_allocation"
                    >
                        <CloudUploadIcon />
                        Upload
                    </Button>
                </Toolbar>
                <Divider />
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
                            label="Product"
                            icon={<AccountBalanceIcon />}
                            keyword={productKeyword}
                            results={products}
                            checked={selectedProducts}
                            onSearch={this.handleSearchProduct}
                            onCheck={this.handleCheckProduct}
                        />
                    </Grid>
                </Grid>
            </Layout>
        )
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(styler(SRProductAllocation));