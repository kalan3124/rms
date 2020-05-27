import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';

import { changeTab, fetchTerritoryLevels, fetchTerritories } from '../../../actions/Medical/UserArea';

import AppBar from '@material-ui/core/AppBar';
import Tabs from '@material-ui/core/Tabs';
import Tab from '@material-ui/core/Tab';
import SearchIcon from '@material-ui/icons/Search';
import Toolbar from '@material-ui/core/Toolbar';
import FormControl from '@material-ui/core/FormControl';
import InputLabel from '@material-ui/core/InputLabel';
import Input from '@material-ui/core/Input';
import InputAdornment from '@material-ui/core/InputAdornment';
import IconButton from '@material-ui/core/IconButton';
import withStyles from '@material-ui/core/styles/withStyles';
import Paper from '@material-ui/core/Paper';
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";

const styles = theme => ({
    leftPaper: {
        background: theme.palette.grey[300],
    },
    margin: {
        margin: theme.spacing.unit,
    },
    listItem:{
        background:theme.palette.common.white
    },
    list:{
        height: theme.spacing.unit * 40,
        overflowY:'auto',
    }
});

export const mapStateToProps = state => ({
    ...state.UserArea,
    ...state.UserAllocation
});

export const mapDispatchToProps = dispatch => ({
    onTabChange: ( tab) => dispatch(changeTab(tab)),
    onLoad: () => dispatch(fetchTerritoryLevels()),
    onTerritoryFetch: (type, keyword) => dispatch(fetchTerritories(type, keyword))
});



class LeftPanel extends Component {

    constructor(props) {
        super(props);

        props.onLoad();

        this.handleTerritoryNameChange = this.handleTerritoryNameChange.bind(this);
        this.handleTabChange = this.handleTabChange.bind(this);
    }

    handleTerritoryNameChange({ currentTarget }) {
        const { tab, territoryLevels, onTerritoryFetch } = this.props;

        const type = territoryLevels[tab].link;

        const { value } = currentTarget;

        onTerritoryFetch(type, value);
    }

    handleTabChange(e,tab){
        const {onTabChange,territoryLevels,onTerritoryFetch} = this.props;

        onTabChange(tab);

        onTerritoryFetch(territoryLevels[tab].link)
    }

    handleAreaClick(area){
        const {tab,territoryLevels,onSelect} = this.props;

        onSelect(area.value,area.label,territoryLevels[tab].link);
    }

    renderTabs() {
        const { territoryLevels } = this.props;

        return territoryLevels.map((level, index) => (
            <Tab key={index} label={level.label} />
        ));
    }

    renderSearchResults() {
        const { territories,classes } = this.props;

        return territories.map((territory, index) => (
            <ListItem onClick={e=>this.handleAreaClick(territory)} button className={classes.listItem} divider button key={index} dense >
                <ListItemText primary={territory.label} />
            </ListItem>
        ))
    }

    render() {

        const {  classes, tab, territoryLevels, territoryName } = this.props;

        let activeLabel = "Territories";

        if (territoryLevels.length)
            activeLabel = territoryLevels[tab].label;

        return (
            <Paper className={classes.leftPaper}>
                <AppBar position="static">
                    <Tabs
                        value={tab}
                        onChange={this.handleTabChange}
                        variant="scrollable"
                        scrollButtons="auto"
                    >
                        {this.renderTabs()}
                    </Tabs>
                </AppBar>
                <Toolbar>
                    <FormControl fullWidth className={classes.margin}>
                        <InputLabel htmlFor="adornment-terr">{"Search for " + activeLabel}</InputLabel>
                        <Input
                            id="adornment-terr"
                            endAdornment={
                                <InputAdornment position="end">
                                    <IconButton>
                                        <SearchIcon />
                                    </IconButton>
                                </InputAdornment>
                            }
                            value={territoryName?territoryName:""}
                            onChange={this.handleTerritoryNameChange}
                        />
                    </FormControl>
                </Toolbar>
                <List className={classes.list}>
                    {this.renderSearchResults()}
                </List>
            </Paper>
        );
    }
}

LeftPanel.propTypes = {
    classes: PropTypes.object,
    onTabChange: PropTypes.func,
    // Active tab number
    tab: PropTypes.number,
    // Loaded territory levels
    territoryLevels: PropTypes.arrayOf(PropTypes.shape({
        label: PropTypes.string,
        link: PropTypes.string
    })),
    onTerritoryFetch: PropTypes.func,
    // Loaded territories
    territories: PropTypes.arrayOf(PropTypes.shape({
        label: PropTypes.string,
        value: PropTypes.number
    }))
};

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(LeftPanel));