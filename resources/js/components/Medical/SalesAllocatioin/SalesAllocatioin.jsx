import React, { Component } from "react";
import { connect } from "react-redux";

import Typography from "@material-ui/core/Typography";
import Divider from "@material-ui/core/Divider";
import Toolbar from "@material-ui/core/Toolbar";
import withStyles from "@material-ui/core/styles/withStyles";
import ExpansionPanel from "@material-ui/core/ExpansionPanel";
import ExpansionPanelSummary from "@material-ui/core/ExpansionPanelSummary";
import ExpansionPanelDetails from "@material-ui/core/ExpansionPanelDetails";
import Button from "@material-ui/core/Button";
import Grid from "@material-ui/core/Grid";
import ExpandMoreIcon from "@material-ui/icons/ExpandMore";

import Layout from "../../App/Layout";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import {
    changeTeam,
    changeMode,
    changeUpdatingMode,
    selectRow,
    changeSearchTerm,
    fetchSearchResults,
    changePage,
    changePerPage,
    changeMemberPercentage,
    fetchData,
    submit
} from "../../../actions/Medical/SalesAllocation";
import CheckTablePanel from "./CheckTablePanel";

import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemSecondaryAction from "@material-ui/core/ListItemSecondaryAction";
import ListItemText from "@material-ui/core/ListItemText";
import TextField from "@material-ui/core/TextField";
import ReactSvgPieChart from "react-svg-piechart";

const styler = withStyles(theme => ({
    grow: {
        flexGrow: 1
    },
    divider: {
        marginBottom: theme.spacing.unit * 2
    }
}));

const mapStateToProps = state => ({
    ...state.SalesAllocation
});

const mapDispatchToProps = dispatch => ({
    onTeamChange: team => dispatch(changeTeam(team)),
    onFetchData: team => dispatch(fetchData(team)),
    onChangeMode: mode => dispatch(changeMode(mode)),
    onChangeUpdatingMode: mode => dispatch(changeUpdatingMode(mode)),
    onSelectRow: row => dispatch(selectRow(row)),
    onChangeSearchTerm: term => dispatch(changeSearchTerm(term)),
    onSearch: ( mode, term, page, perPage,additional) =>
        dispatch(fetchSearchResults( mode, term, page, perPage,additional)),
    onChangePage: page => dispatch(changePage(page)),
    onChangePerPage: perPage => dispatch(changePerPage(perPage)),
    onChangeMemberPercentage: (id, value) =>
        dispatch(changeMemberPercentage(id, value)),
    onSubmit: (team, modes, selected, members) =>
        dispatch(submit(team, modes, selected, members))
});

const colors = [
    "#006887",
    "#bc0c55",
    "#ff846d",
    "#0090a0",
    "#b2f700",
    "#3371cc",
    "#e8e8e8",
    "#eb596a",
    "#f7aa84",
    "#e09bb2",
    "#eb3800",
    "#20cae6",
    "#d4195a",
    "#e57055",
    "#822106"
];

class SalesAllocatioin extends Component {
    constructor(props) {
        super(props);

        this.handleChangePage = this.handleChangePage.bind(this);
        this.handleChangePerPage = this.handleChangePerPage.bind(this);
        this.handleChangeTeam = this.handleChangeTeam.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
    }

    handleChangeTeam(team) {
        const { onTeamChange, onFetchData } = this.props;

        onTeamChange(team);
        onFetchData(team);
    }

    handleChangeMode(newMode) {
        const { onChangeMode, onSearch,team } = this.props;

        return e => {
            const { activeMode,checked,updatingMode } = this.props;
            if (
                activeMode == newMode &&
                (e.target.tagName == "DIV" || e.target.tagName == "INPUT")
            ) {
                return null;
            }

            if (newMode && newMode != "members") {
                if(newMode=="customers"){
                    onSearch(newMode,"",1,10,{towns:checked.towns,mode:updatingMode.towns});
                } else if (newMode=="products"){
                    onSearch(newMode,"",1,10,{team})
                }  else {
                    onSearch( newMode);
                }
            }

            onChangeMode(newMode);
        };
    }

    handleClickNextMode(mode) {
        const { onChangeMode, onSearch,team } = this.props;

        return e => {
            const {checked,updatingMode} = this.props;
            if (mode && mode != "members") {
                if(mode=="customers"){
                    onSearch(mode,"",1,10,{towns:checked.towns,mode:updatingMode.towns})
                } else if (mode=="products"){
                    onSearch(mode,"",1,10,{team})
                } else {
                    onSearch( mode);
                }
            }
            onChangeMode(mode);
        };
    }

    handleChangeSearchTerm(mode) {
        return value => {
            const { onChangeSearchTerm, onSearch, team,checked,updatingMode } = this.props;

            let additional = {};

            if(mode=='products')
                additional.team = team;
            if(mode=='customers'){
                additional.towns = checked.towns;
                additional.mode = updatingMode.customers;
            }

            onChangeSearchTerm(value);
            onSearch( mode, value,1,10,additional);
        };
    }

    handleChangePage(page) {
        const {
            onSearch,
            onChangePage,
            searchTerm,
            perPage,
            checked,
            team,
            activeMode,
            updatingMode
        } = this.props;

        onChangePage(page);

        if (activeMode && activeMode != "members") {
            if(activeMode=="customers"){
                onSearch(activeMode,searchTerm,page,perPage,{towns:checked.towns,mode:updatingMode.towns});
            } else if (activeMode=="products"){
                onSearch(activeMode,searchTerm,page,perPage,{team});
            }  else {
                onSearch( activeMode,searchTerm,page,perPage);
            }
        }
        
    }

    handleChangePerPage(perPage) {
        const {
            onSearch,
            onChangePerPage,
            searchTerm,
            activeMode,
            page,
            checked,
            updatingMode
        } = this.props;

        onChangePerPage( parseInt(perPage.target.value));

        if (activeMode && activeMode != "members") {
            if(activeMode=="customers"){
                onSearch(activeMode,searchTerm,page,perPage.target.value,{towns:checked.towns,mode:updatingMode.towns});
            } else if (activeMode=="products"){
                onSearch(activeMode,searchTerm,page,perPage.target.value,{team});
            }  else {
                onSearch( activeMode,searchTerm,page,perPage.target.value);
            }
        }
    }

    handleChangeMemberPercentage(memberId) {
        const { onChangeMemberPercentage } = this.props;

        return e => {
            const { members } = this.props;

            let value = parseFloat(e.target.value);

            if (isNaN(value)) {
                value = 0;
            }

            if (value > 100) {
                value = 100;
            } else if (value < 0) {
                value = 0;
            }

            let total = 0;

            Object.values(members).forEach(member => {
                if (member.id != memberId && member.value) {
                    total += parseFloat(member.value);
                }
            });

            onChangeMemberPercentage(
                memberId,
                total + value > 100 ? 100 - total : value
            );
        };
    }

    handleSubmit() {
        const { onSubmit, team, members, checked, updatingMode } = this.props;

        onSubmit(team, updatingMode, checked, members);
    }

    render() {
        const {
            classes,
            team,
            activeMode,
            onChangeUpdatingMode,
            onSelectRow,
            checked,
            updatingMode,
            searchTerm,
            sectionData,
            resultsCount,
            page,
            perPage,
            members
        } = this.props;

        return (
            <Layout sidebar>
                <Toolbar>
                    <Typography id="emp" variant="h5" align="center">
                        Team Sales Allocation
                    </Typography>
                    <div className={classes.grow} />
                    <AjaxDropdown
                        onChange={this.handleChangeTeam}
                        value={team}
                        link="team"
                        label="Team"
                    />
                </Toolbar>
                <Divider className={classes.divider} />
                <div className={classes.grow} />
                {team ? (
                    <div>
                        <CheckTablePanel
                            label="Towns"
                            open={activeMode == "towns"}
                            columns={[
                                { name: "name", label: "Name" },
                                { name: "code", label: "Code" },
                                { name: "town", label: "Town" }
                            ]}
                            results={activeMode == "towns" ? sectionData : {}}
                            mode={updatingMode.towns}
                            selected={checked.towns}
                            onChangeMode={onChangeUpdatingMode}
                            onSelect={onSelectRow}
                            onSubmit={this.handleClickNextMode("customers")}
                            onCollapse={this.handleChangeMode("towns")}
                            value={searchTerm}
                            onSearch={this.handleChangeSearchTerm("towns")}
                            resultCount={resultsCount}
                            page={page}
                            perPage={perPage}
                            onChangePage={this.handleChangePage}
                            onChangePerPage={this.handleChangePerPage}
                        />
                        <CheckTablePanel
                            label="Customers"
                            open={activeMode == "customers"}
                            columns={[
                                { name: "name", label: "Name" },
                                { name: "code", label: "Code" },
                                { name: "town", label: "Town" }
                            ]}
                            results={
                                activeMode == "customers" ? sectionData : {}
                            }
                            mode={updatingMode.customers}
                            selected={checked.customers}
                            onChangeMode={onChangeUpdatingMode}
                            onSelect={onSelectRow}
                            onSubmit={this.handleClickNextMode("products")}
                            onCollapse={this.handleChangeMode("customers")}
                            value={searchTerm}
                            onSearch={this.handleChangeSearchTerm("customers")}
                            resultCount={resultsCount}
                            page={page}
                            perPage={perPage}
                            onChangePage={this.handleChangePage}
                            onChangePerPage={this.handleChangePerPage}
                        />
                        <CheckTablePanel
                            label="Products"
                            open={activeMode == "products"}
                            columns={[
                                { name: "name", label: "Name" },
                                { name: "code", label: "Code" },
                                { name: "category", label: "Category" }
                            ]}
                            results={
                                activeMode == "products" ? sectionData : {}
                            }
                            mode={updatingMode.products}
                            selected={checked.products}
                            onChangeMode={onChangeUpdatingMode}
                            onSelect={onSelectRow}
                            onSubmit={this.handleClickNextMode("members")}
                            onCollapse={this.handleChangeMode("products")}
                            value={searchTerm}
                            onSearch={this.handleChangeSearchTerm("products")}
                            resultCount={resultsCount}
                            page={page}
                            perPage={perPage}
                            onChangePage={this.handleChangePage}
                            onChangePerPage={this.handleChangePerPage}
                        />
                        <ExpansionPanel
                            className={classes.expansion}
                            onChange={this.handleChangeMode("members")}
                            expanded={activeMode == "members"}
                        >
                            <ExpansionPanelSummary
                                expandIcon={<ExpandMoreIcon />}
                            >
                                Members
                            </ExpansionPanelSummary>
                            <ExpansionPanelDetails>
                                <Grid container>
                                    <Grid item md={6}>
                                        <List>
                                            {Object.values(members).map(
                                                (member, key) => (
                                                    <ListItem key={key}>
                                                        <ListItemText
                                                            primary={
                                                                member.name
                                                            }
                                                            secondary={
                                                                member.code
                                                            }
                                                        />
                                                        <ListItemSecondaryAction>
                                                            <TextField
                                                                type="number"
                                                                onChange={this.handleChangeMemberPercentage(
                                                                    member.id
                                                                )}
                                                                value={
                                                                    member.value
                                                                        ? member.value
                                                                        : ""
                                                                }
                                                            />
                                                        </ListItemSecondaryAction>
                                                    </ListItem>
                                                )
                                            )}
                                        </List>
                                    </Grid>
                                    <Grid item md={6}>
                                        <ReactSvgPieChart
                                            data={Object.values(members).map(
                                                ({ name, value }, key) => ({
                                                    title: name,
                                                    value: value
                                                        ? parseFloat(value)
                                                        : 0,
                                                    color: colors[key]
                                                })
                                            )}
                                            // If you need expand on hover (or touch) effect
                                            expandOnHover
                                        />
                                    </Grid>
                                </Grid>
                                <Divider />
                                <Toolbar>
                                    <div className={classes.grow} />
                                    <Button
                                        variant="contained"
                                        color="primary"
                                        onClick={this.handleSubmit}
                                    >
                                        Submit
                                    </Button>
                                </Toolbar>
                            </ExpansionPanelDetails>
                        </ExpansionPanel>
                    </div>
                ) : null}
            </Layout>
        );
    }
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(styler(SalesAllocatioin));
