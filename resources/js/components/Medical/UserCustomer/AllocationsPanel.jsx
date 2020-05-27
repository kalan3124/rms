import React, { Component } from "react";
import { connect } from "react-redux";

import withStyles from "@material-ui/core/styles/withStyles";

import Paper from "@material-ui/core/Paper";
import IconButton from "@material-ui/core/IconButton";
import InputBase from "@material-ui/core/InputBase";
import ClickAwayListener from "@material-ui/core/ClickAwayListener";
import MenuItem from "@material-ui/core/MenuItem";
import AppBar from "@material-ui/core/AppBar";
import Tabs from "@material-ui/core/Tabs";
import Tab from "@material-ui/core/Tab";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import ListItemIcon from "@material-ui/core/ListItemIcon";
import List from "@material-ui/core/List";
import Badge from "@material-ui/core/Badge";

import PersonIcon from "@material-ui/icons/Person";
import SearchIcon from "@material-ui/icons/Search";
import CloseIcon from "@material-ui/icons/Close";

import red from "@material-ui/core/colors/red";

import { fetchUsers, openUserMenu, closeUserMenu, changeRightTab, fetchCustomers } from "../../../actions/Medical/UserCustomer";

const mapStateToProps = state => ({
    ...state.UserCustomer,
    ...state.UserAllocation
})

const mapDispatchToProp = dispatch => ({
    onUserNameChange: userName => dispatch(fetchUsers(userName)),
    onUserMenuOpen: (userMenuRef) => dispatch(openUserMenu(userMenuRef)),
    onUserMenuClose: () => dispatch(closeUserMenu()),
    onTabChange: (e, tab) => dispatch(changeRightTab(tab)),
    onChangeUser: user => dispatch(fetchCustomers(user))
})

const styles = theme => ({
    paper: {
        background: theme.palette.grey[400],
        margin: theme.spacing.unit,
        position: "relative",
        padding: theme.spacing.unit / 2
    },
    inputRoot: {
        padding: '2px 4px',
        display: 'flex',
        alignItems: 'center',
    },
    input: {
        marginLeft: 8,
        flex: 1,
    },
    iconButton: {
        padding: 10,
    },
    menu: {
        marginTop: theme.spacing.unit * 7,
        position: 'absolute',
        top: 0,
        left: 0,
        marginLeft: theme.spacing.unit * 6,
        maxHeight: theme.spacing.unit * 40,
        overflowY: 'auto',
        zIndex: 1000
    },
    listItem: {
        background: theme.palette.common.white
    },
    closeIcon: {
        color: red[500],
        '&:hover': {
            color: red[100]
        },
        cursor: 'pointer'
    },
    list: {
        height: "calc(46vh + " + (theme.spacing.unit * 9) + "px)",
        overflowY: 'auto'
    }
})

class AllocationsPanel extends Component {

    constructor(props) {
        super(props);

        this.handleUserInputFocus = this.handleUserInputFocus.bind(this);
        this.handleUserNameChange = this.handleUserNameChange.bind(this);
        if(props.user&&props.user.label){
            this.handleUserChange(props.user.value,props.user.label);
        }
    }

    componentDidUpdate(prevProps){
        if(JSON.stringify(prevProps.user)!=JSON.stringify(this.props.user)&&prevProps.page!=this.props.path&&this.props.path){
            this.handleUserChange(this.props.user.value,this.props.user.label);
        }
    }

    handleUserInputFocus({ currentTarget }) {
        const { onUserMenuOpen } = this.props;

        onUserMenuOpen(currentTarget);
    }

    handleUserNameChange({ currentTarget }) {
        const { onUserNameChange } = this.props;

        onUserNameChange(currentTarget.value)
    }

    renderUserList() {
        const { userMenuOpen, userMenuRef, classes, onUserMenuClose } = this.props;

        if (!userMenuOpen) return null;

        return (
            <ClickAwayListener onClickAway={onUserMenuClose} >
                <Paper
                    className={classes.menu}
                    style={{
                        width: userMenuRef ? userMenuRef.offsetWidth : 'unset'
                    }}
                >
                    {this.renderUserListItems()}
                </Paper>
            </ClickAwayListener>
        )
    }

    handleUserChange(value, label) {
        const { onChangeUser, onUserMenuClose } = this.props;

        onChangeUser({ value, label });
        onUserMenuClose();
    }

    renderUserListItems() {
        const { users } = this.props;

        if (!users.length) {
            return (<MenuItem disabled >Start Typing to Search..</MenuItem>)
        }

        return users.map(({ label, value }, index) => (
            <MenuItem onClick={e => this.handleUserChange(value, label)} divider key={index} >{label}</MenuItem>
        ))
    }

    renderTabs() {
        const { tabs, chemists, doctors,staffs } = this.props;

        const counts = {
            doctor:Object.keys(doctors).length,
            chemist:Object.keys(chemists).length,
            other_hospital_staff:Object.keys(staffs).length
        }


        return tabs.map(({ label, link }, index) => (
            <Tab key={index} label={
                <Badge color="secondary" badgeContent={counts[link]} max={1000000} >
                    {label}
                </Badge>
            } />
        ));
    }

    handleRemoveButtonClick(item) {
        const { tabs, rightTab, onRemove } = this.props;

        const link = tabs[rightTab].link;

        onRemove(link, item);
    }

    renderListItems() {
        const { chemists, staffs ,doctors, classes, rightTab } = this.props;
        let items = {};

        if (rightTab == 1) {
            items = { ...doctors };
        } else if(rightTab ==0) {
            items = { ...chemists };
        } else {
            items = {...staffs};
        }

        return Object.keys(items).map(itemId => (
            <ListItem divider className={classes.listItem} key={itemId}>
                <ListItemText inset primary={items[itemId].label} />
                <ListItemIcon >
                    <CloseIcon onClick={e => this.handleRemoveButtonClick(items[itemId])} className={classes.closeIcon} />
                </ListItemIcon>
            </ListItem>
        ));
    }

    render() {
        const { classes, userName, rightTab, onTabChange } = this.props;

        return (
            <Paper className={classes.paper} >
                <Paper className={classes.inputRoot} elevation={1}>
                    <IconButton className={classes.iconButton} aria-label="Menu">
                        <PersonIcon />
                    </IconButton>
                    <InputBase value={userName?userName:""} onChange={this.handleUserNameChange} className={classes.input} placeholder="Search a user" onClick={this.handleUserInputFocus} />
                    <IconButton className={classes.iconButton} aria-label="Search">
                        <SearchIcon />
                    </IconButton>
                </Paper>
                {this.renderUserList()}
                <AppBar position="static">
                    <Tabs
                        value={rightTab}
                        onChange={onTabChange}
                        variant="scrollable"
                        scrollButtons="auto"
                    >
                        {this.renderTabs()}
                    </Tabs>
                </AppBar>
                <List className={classes.list}>
                    {this.renderListItems()}
                </List>
            </Paper>
        );
    }

}

export default connect(mapStateToProps, mapDispatchToProp)(withStyles(styles)(AllocationsPanel));