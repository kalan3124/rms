import React, { Component, Fragment } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';

import withStyles from '@material-ui/core/styles/withStyles';
import Paper from '@material-ui/core/Paper';
import InputBase from '@material-ui/core/InputBase';
import IconButton from '@material-ui/core/IconButton';
import SearchIcon from '@material-ui/icons/Search';
import PersonIcon from '@material-ui/icons/Person';
import MenuItem from '@material-ui/core/MenuItem';
import CloseIcon from '@material-ui/icons/Close';
import ClickAwayListener from "@material-ui/core/ClickAwayListener";
import AppBar from "@material-ui/core/AppBar";
import Tabs from "@material-ui/core/Tabs";
import Tab from "@material-ui/core/Tab";
import Badge from "@material-ui/core/Badge";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import ListItemIcon from "@material-ui/core/ListItemIcon";
import red from '@material-ui/core/colors/red';

import { fetchUsers, openUserMenu, closeUserMenu, changeUserTab, fetchInformations } from '../../../actions/Medical/UserArea';

const styles = theme => ({
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
    paper: {
        background: theme.palette.grey[300],
        position: 'relative'
    },
    usersWrapper: {
        height: theme.spacing.unit * 44,
        overflowY:'auto'
    },
    menu: {
        marginTop: theme.spacing.unit * 7,
        position: 'absolute',
        top: 0,
        left: 0,
        marginLeft: theme.spacing.unit * 6,
        maxHeight: theme.spacing.unit * 40,
        overflowY: 'auto'
    },
    listItem:{
        background:theme.palette.common.white
    },
    closeIcon:{
        color:red[500],
        '&:hover':{
            color:red[100]
        },
        cursor:'pointer'
    }
});

const mapStateToProps = state => ({
    ...state.UserArea,
    ...state.UserAllocation
})

const mapDispatchToProps = dispatch => ({
    onFetchUser: keyword => dispatch(fetchUsers(keyword)),
    onUserMenuOpen: (userMenuRef) => dispatch(openUserMenu(userMenuRef)),
    onUserMenuClose: () => dispatch(closeUserMenu()),
    onUserSelect: user => dispatch(fetchInformations(user)),
    onUserTabChange: tab => dispatch(changeUserTab(tab))
})

class RightPanel extends Component {

    constructor(props) {
        super(props);

        this.handleUserNameChange = this.handleUserNameChange.bind(this);
        this.handleFocusUserInput = this.handleFocusUserInput.bind(this);
        this.handleCloseUserMenu = this.handleCloseUserMenu.bind(this);
        this.handleTabChange = this.handleTabChange.bind(this);

        if(props.user&&props.user.label){
            this.handleSelectUser(props.user);
        }
    }

    componentDidUpdate(prevProps){
        if(JSON.stringify(prevProps.user)!=JSON.stringify(this.props.user)&&prevProps.path!=this.props.path){
            this.handleSelectUser(this.props.user)
        }
    }

    handleUserNameChange(e) {
        const { onFetchUser } = this.props;

        onFetchUser(e.currentTarget.value);
    }

    handleFocusUserInput(e) {
        const { onUserMenuOpen } = this.props;

        onUserMenuOpen(e.currentTarget);
    }

    handleCloseUserMenu() {
        const { onUserMenuClose } = this.props;

        onUserMenuClose();
    }

    handleSelectUser(user) {
        const { onUserSelect, onUserMenuClose } = this.props;

        onUserSelect(user);
        onUserMenuClose();
    }

    handleTabChange(e, tab) {
        const { onUserTabChange } = this.props;

        onUserTabChange(tab);
    }

    handleRemoveButtonClick({label,value,type}){
        const {onRemove} = this.props;

        onRemove(value,label,type);
    }

    renderUserListItems() {
        const { users } = this.props;

        if (!users.length) {
            return (<MenuItem disabled >Start Typing to Search..</MenuItem>)
        }

        return users.map((user, index) => (
            <MenuItem onClick={e => this.handleSelectUser(user)} divider key={index} >{user.label}</MenuItem>
        ))
    }

    renderUserList() {
        const { userMenuOpen, userMenuRef, classes } = this.props;

        if (!userMenuOpen) return null;

        return (
            <ClickAwayListener onClickAway={this.handleCloseUserMenu} >
                <Paper
                    onClose={this.handleCloseUserMenu}
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

    renderTabs() {
        const { levels, areas } = this.props;

        return levels.map((level, index) => {

            let count = areas[level.link] ? Object.keys(areas[level.link]).length : 0;

            return (
                <Tab key={index} label={
                    <Badge badgeContent={count} max={100000} color="secondary">
                        {level.label}
                    </Badge>
                } />
            )
        })
    }

    renderUserArea() {
        const { user, activeUserTab,classes } = this.props;

        if (!user) return null;

        return (
            <Fragment>
                <AppBar position="static">
                    <Tabs
                        value={activeUserTab}
                        onChange={this.handleTabChange}
                        variant="scrollable"
                        scrollButtons="auto"
                    >
                        {this.renderTabs()}
                    </Tabs>
                </AppBar>
                <div className={classes.usersWrapper}>
                    <List>
                        {this.renderListItems()}
                    </List>
                </div>
            </Fragment>
        )
    }

    renderListItems() {
        const { areas, activeUserTab, levels, classes } = this.props;

        if (!levels.length) return null;

        let activeTabLink = levels[activeUserTab].link;

        const selectedAreas = areas[activeTabLink];

        if (!selectedAreas) return null;

        return Object.keys(selectedAreas).map(areaId => (
            <ListItem divider className={classes.listItem} key={areaId}>
                <ListItemText inset primary={selectedAreas[areaId].label} />
                <ListItemIcon >
                    <CloseIcon onClick={e=>this.handleRemoveButtonClick(selectedAreas[areaId])} className={classes.closeIcon} />
                </ListItemIcon>
            </ListItem>
        ))
    }

    render() {
        const { classes, userName } = this.props;
        return (
            <Paper className={classes.paper}>
                <Paper className={classes.inputRoot} elevation={1}>
                    <IconButton className={classes.iconButton} aria-label="Menu">
                        <PersonIcon />
                    </IconButton>
                    <InputBase value={userName?userName:""} onChange={this.handleUserNameChange} className={classes.input} placeholder="Search a user" onClick={this.handleFocusUserInput} />
                    <IconButton className={classes.iconButton} aria-label="Search">
                        <SearchIcon />
                    </IconButton>
                </Paper>
                {this.renderUserArea()}
                {this.renderUserList()}
            </Paper>
        );
    }
}


RightPanel.propTypes = {
    classes: PropTypes.object.isRequired,
    users: PropTypes.arrayOf(PropTypes.shape({
        label: PropTypes.string,
        value: PropTypes.number
    })),
    userName: PropTypes.string,
    userMenuRef: PropTypes.object,
    userMenuOpen: PropTypes.bool,
    levels: PropTypes.arrayOf(PropTypes.shape({
        label: PropTypes.string,
        link: PropTypes.string
    })),
    areas: PropTypes.object
};

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(RightPanel));