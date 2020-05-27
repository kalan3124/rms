import React, { Component } from "react";
import { connect } from "react-redux";
import Typography from "@material-ui/core/Typography";
import Divider from "@material-ui/core/Divider";
import Toolbar from "@material-ui/core/Toolbar";
import withStyles from "@material-ui/core/styles/withStyles";
import Grid from "@material-ui/core/Grid";
import ExpandMoreIcon from "@material-ui/icons/ExpandMore";
import Button from "@material-ui/core/Button";
import Layout from "../../App/Layout";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import CheckTable from "../SalesAllocatioin/CheckTable";
import Form from "../Report/Form";
import {
    fetchData,
    changeTeam,
    searchInvoices,
    selectInvoice,
    changePage,
    changePerPage,
    changeSearchTerms,
    changeMode,
    submit,
    changePanel,
    changeProductMode,
    checkProduct,
    searchProducts,
    changeProductKeyword,
    changeProductPage,
    changeProductPerPage,
    changeMemberQty
} from "../../../actions/Medical/InvoiceAllocation";
import CheckTablePanel from "../SalesAllocatioin/CheckTablePanel";
import ExpansionPanelSummary from "@material-ui/core/ExpansionPanelSummary";
import ExpansionPanel from "@material-ui/core/ExpansionPanel";
import ExpansionPanelDetails from "@material-ui/core/ExpansionPanelDetails";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import ListItemSecondaryAction from "@material-ui/core/ListItemSecondaryAction";
import TextField from "@material-ui/core/TextField";
import Fab from "@material-ui/core/Fab";

const styler = withStyles(theme => ({
    grow: {
        flexGrow: 1
    },
    margin: {
        margin: theme.spacing.unit * 2
    },
    marginLeft:{
        marginLeft: theme.spacing.unit * 2
    },
    submitButton: {
        margin: theme.spacing.unit
    }
}));

const mapStateToProps = state => ({
    ...state.InvoiceAllocation
});

const mapDispatchToProps = dispatch => ({
    onLoad: team => dispatch(fetchData(team)),
    onChangeTeam: team => dispatch(changeTeam(team)),
    onSearch: (team, terms, page, perPage) =>
        dispatch(searchInvoices(team, terms, page, perPage)),
    onSelect: row => dispatch(selectInvoice(row)),
    onChangePage: page => dispatch(changePage(page)),
    onChangePerPage: perPage => dispatch(changePerPage(perPage)),
    onChangeSearchTerms: searchTerms =>
        dispatch(changeSearchTerms(searchTerms)),
    onChangeMode: mode => dispatch(changeMode(mode)),
    onSubmit: (team, mode, selected,productChecked,teamMembers) => dispatch(submit(team, mode, selected,productChecked,teamMembers)),
    onChangePanel: panel => dispatch(changePanel(panel)),

    onChangeProductMode: mode => dispatch(changeProductMode(mode)),
    onCheckProduct: row => dispatch(checkProduct(row)),
    onSearchProduct: (invoices, keyword, page, perPage) =>
        dispatch(searchProducts(invoices, keyword, page, perPage)),
    onChangeProductKeyword: keyword => dispatch(changeProductKeyword(keyword)),
    onChangeProductPage: page => dispatch(changeProductPage(page)),
    onChangeProductPerPage: perPage => dispatch(changeProductPerPage(perPage)),

    onChangeTeamMemberQty: (id, value) => dispatch(changeMemberQty(id, value))
});

class InvoiceAllocation extends Component {
    constructor(props) {
        super(props);

        this.handleChangePerPage = this.handleChangePerPage.bind(this);
        this.handleChangePage = this.handleChangePage.bind(this);
        this.handleChangeSearchTerms = this.handleChangeSearchTerms.bind(this);
        this.handleChangeTeam = this.handleChangeTeam.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleSearchProducts = this.handleSearchProducts.bind(this);
    }

    handleChangePage(page) {
        const { onChangePage, onSearch, team, perPage, values } = this.props;

        onChangePage(page);
        onSearch(team, values, page, perPage);
    }

    handleChangePerPage(e) {
        const { onChangePerPage, onSearch, page, values, team } = this.props;

        let perPage = e.target.value;

        onChangePerPage(perPage);
        onSearch(team, values, page, perPage);
    }

    handleChangeSearchTerms(values) {
        const {
            onChangeSearchTerms,
            page,
            perPage,
            team,
            onSearch
        } = this.props;

        onChangeSearchTerms(values);
        onSearch(team, values, page, perPage);
    }

    handleChangeTeam(team) {
        const {
            onChangeTeam,
            page,
            perPage,
            values,
            onSearch,
            onLoad
        } = this.props;

        onChangeTeam(team);

        if (!team) {
            return null;
        }
        onSearch(team, values, page, perPage);
        onLoad(team);
    }

    handleSubmit() {
        const { checked, mode, team, onSubmit,productChecked,teamMembers } = this.props;

        onSubmit(team, mode, checked,productChecked,teamMembers);
    }

    handleChangePanel(currentPanel) {
        return e => {
            const {
                onChangePanel,
                checked,
                onSearchProduct,
                panel
            } = this.props;

            if (
                (e.target.tagName == "DIV" || e.target.tagName == "INPUT") &&
                currentPanel == panel
            ) {
                return null;
            }

            if (currentPanel == 1) {
                onSearchProduct(checked, "", 1, 10);
            }

            onChangePanel(currentPanel);
        };
    }

    handleSearchProducts(searchTerm) {
        const {
            onSearchProduct,
            onChangeProductKeyword,
            productPage,
            productPerPage,
            checked
        } = this.props;

        onChangeProductKeyword(searchTerm);
        onSearchProduct(checked, searchTerm, productPage, productPerPage);
    }

    handleChangeTeamMemberQty(teamMemberId){
        return e=>{
            const {onChangeTeamMemberQty} = this.props;
            
            onChangeTeamMemberQty(teamMemberId,e.target.value);
        }
    }

    render() {
        const { classes, team } = this.props;

        return (
            <Layout sidebar>
                <Toolbar variant="dense">
                    <Typography variant="h6">Invoice Allocation</Typography>
                    <div className={classes.grow} />
                    <AjaxDropdown
                        onChange={this.handleChangeTeam}
                        link="team"
                        label="Team"
                        value={team}
                    />
                </Toolbar>
                <Divider />
                {this.renderPanels()}
            </Layout>
        );
    }

    renderPanels() {
        const {
            team,
            page,
            perPage,
            count,
            onSelect,
            onChangeMode,
            mode,
            checked,
            searchedResults,
            classes,
            products,
            productChecked,
            productPage,
            productPerPage,
            productMode,
            productCount,
            productKeyword,
            onChangeProductMode,
            onCheckProduct,
            onChangeProductPage,
            onChangeProductPerPage,
            panel,
            teamMembers
        } = this.props;

        if (!team) return null;

        return (
            <div>
                <ExpansionPanel
                    onChange={this.handleChangePanel(0)}
                    expanded={panel == 0}
                >
                    <ExpansionPanelSummary expandIcon={<ExpandMoreIcon />}>
                        Select Invoice(s)
                    </ExpansionPanelSummary>
                    <ExpansionPanelDetails>
                        <Grid
                            className={classes.margin}
                            justify="center"
                            container
                        >
                            <Grid item md={8}>
                                <Form
                                    title="Search Invoice"
                                    inputs={{
                                        invoice_num: {
                                            label: "Invoice Number",
                                            type: "text",
                                            name: "invoice_num"
                                        },
                                        from_date: {
                                            label: "From",
                                            type: "date",
                                            name: "from_date"
                                        },
                                        to_date: {
                                            label: "To",
                                            type: "date",
                                            name: "to_date"
                                        },
                                        chemist: {
                                            label: "Customer",
                                            type: "ajax_dropdown",
                                            name: "chemist",
                                            link: "chemist"
                                        },
                                        sub_town: {
                                            label: "Town",
                                            type: "ajax_dropdown",
                                            name: "sub_town",
                                            link: "sub_town"
                                        }
                                    }}
                                    inputsStructure={[
                                        ["invoice_num", "sub_town", "chemist"],
                                        ["from_date", "to_date"]
                                    ]}
                                    onSubmit={this.handleChangeSearchTerms}
                                />
                            </Grid>
                            <Grid item md={12}>
                                <CheckTable
                                    columns={[
                                        {
                                            name: "invoice_num",
                                            label: "Invoice Number"
                                        },
                                        {
                                            name: "date",
                                            label: "Invoice Date"
                                        },
                                        {
                                            name: "chemist",
                                            label: "Customer"
                                        },
                                        {
                                            name: "town",
                                            label: "Town"
                                        },
                                        {
                                            name: "type",
                                            label: "Type"
                                        },
                                        {
                                            name: "amount",
                                            label: "Invoice Amount"
                                        }
                                    ]}
                                    results={searchedResults}
                                    mode={mode}
                                    selected={checked}
                                    onChangeMode={onChangeMode}
                                    onSelect={onSelect}
                                    page={page}
                                    perPage={perPage}
                                    onChangePage={this.handleChangePage}
                                    onChangePerPage={this.handleChangePerPage}
                                    count={count}
                                />
                                <Toolbar>
                                    <div className={classes.grow} />
                                    <Button
                                        color="primary"
                                        variant="contained"
                                        onClick={this.handleChangePanel(1)}
                                    >
                                        Next
                                    </Button>
                                </Toolbar>
                            </Grid>
                        </Grid>
                    </ExpansionPanelDetails>
                </ExpansionPanel>
                <CheckTablePanel
                    label={"Invoice Line Selection"}
                    open={panel == 1}
                    columns={[
                        { name: "invoice_num", label: "Invoice Number" },
                        { name: "product_code", label: "Product Code" },
                        { name: "product_name", label: "Product Name" },
                        { name: "qty", label: "Qty" }
                    ]}
                    results={products}
                    mode={productMode}
                    selected={{ [productChecked.id]: productChecked }}
                    onChangeMode={onChangeProductMode}
                    onSelect={onCheckProduct}
                    onSubmit={this.handleChangePanel(2)}
                    onCollapse={this.handleChangePanel(1)}
                    value={productKeyword}
                    onSearch={this.handleSearchProducts}
                    page={productPage}
                    perPage={productPerPage}
                    onChangePage={onChangeProductPage}
                    onChangePerPage={onChangeProductPerPage}
                    resultCount={productCount}
                />
                <ExpansionPanel
                    onChange={this.handleChangePanel(2)}
                    expanded={panel == 2 && !!productChecked&&!!productChecked.qty}
                >
                    <ExpansionPanelSummary expandIcon={<ExpandMoreIcon />}>
                        Team Member Qty Assigning
                        {productChecked&&productChecked.qty?
                        <Fab
                            size="small"
                            variant="extended"
                            className={classes.marginLeft}
                        >
                            Invoice Qty:- {productChecked.qty}
                        </Fab>
                        :null}
                    </ExpansionPanelSummary>
                    <ExpansionPanelDetails>
                        <Grid container>
                            <Grid item md={8}>
                                <List>
                                    {Object.values(teamMembers).map(
                                        (teamMember, key) => (
                                            <ListItem key={key} divider>
                                                <ListItemText
                                                    secondary={teamMember.code}
                                                    primary={teamMember.name}
                                                />
                                                <ListItemSecondaryAction>
                                                    <TextField
                                                        variant="outlined"
                                                        margin="dense"
                                                        label="Qty"
                                                        fullWidth
                                                        value={ typeof teamMember.value !='undefined'?teamMember.value:""}
                                                        onChange={this.handleChangeTeamMemberQty(teamMember.id)}
                                                    />
                                                </ListItemSecondaryAction>
                                            </ListItem>
                                        )
                                    )}
                                </List>
                            </Grid>
                        </Grid>
                        <Toolbar>
                            <div className={classes.grow} />
                            <Button onClick={this.handleSubmit} variant="contained" color="primary">
                                Submit
                            </Button>
                        </Toolbar>
                    </ExpansionPanelDetails>
                </ExpansionPanel>
            </div>
        );
    }
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(styler(InvoiceAllocation));
