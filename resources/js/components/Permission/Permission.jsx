import React, { Component } from "react";
import { connect } from "react-redux";
import Layout from "../App/Layout";
import { fetchPermissions, expandPanel, changeValues, changeTab, changeKeyword, fetchResults, changeSelectedUsers, clearPage, save, loadByUser } from "../../actions/Permission";

import AddIcon from "@material-ui/icons/Add";
import EditIcon from "@material-ui/icons/Edit";
import CloseIcon from "@material-ui/icons/Close";
import CheckIcon from "@material-ui/icons/Check";
import SearchIcon from "@material-ui/icons/Search";
import SaveIcon from "@material-ui/icons/Save";
import ExpandMoreIcon from '@material-ui/icons/ExpandMore';
import SearchPanel from "./SearchPanel";

import Typography from "@material-ui/core/Typography";
import Divider from "@material-ui/core/Divider";
import ExpansionPanel from "@material-ui/core/ExpansionPanel";
import ExpansionPanelSummary from "@material-ui/core/ExpansionPanelSummary";
import Checkbox from "@material-ui/core/Checkbox";
import ExpansionPanelDetails from "@material-ui/core/ExpansionPanelDetails";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import withStyles from "@material-ui/core/styles/withStyles";
import Grid from "@material-ui/core/Grid";
import Button from "@material-ui/core/Button"; 


const mapStateToProps = state => ({
    ...state.Permission
});

const mapDispatchToProps = dispatch => ({
    onLoad: () => dispatch(fetchPermissions()),
    onExpand: expanded => dispatch(expandPanel(expanded)),
    onChangeValues: permissionValues => dispatch(changeValues(permissionValues)),
    onChangeTab: tab=>dispatch(changeTab(tab)),
    onKeywordChange: keyword=>dispatch(changeKeyword(keyword)),
    onSearch: (type,keyword,debounce)=>dispatch(fetchResults(type,keyword,debounce)),
    onChangeUsers:(users,permissionGroups)=>dispatch(changeSelectedUsers(users,permissionGroups)),
    onClearPage:()=>dispatch(clearPage()),
    onSavePermissions: (users,permissionGroups,permissionValues)=>dispatch(save(users,permissionGroups,permissionValues)),
    onSearchByUser:(user,type)=>dispatch(loadByUser(user,type))
});

const styles = theme => ({
    listItem: {
        paddingTop: 0,
        paddingBottom: 0
    },
    checkbox: {
        padding: theme.spacing.unit / 2
    },
    list: {
        width: '80%',
        margin: 'auto'
    },
    checkedIcon: {
        background: theme.palette.secondary.main,
        borderRadius: theme.spacing.unit / 4,
        color: theme.palette.common.white,
        padding: 0,
        fontSize: '20px'
    },
    unCheckedIcon: {
        background: theme.palette.grey[600],
        borderRadius: theme.spacing.unit / 4,
        color: theme.palette.common.white,
        padding: 0,
        fontSize: '20px'
    },
    mainCheckbox: {
        padding: 0,
        paddingRight: theme.spacing.unit
    },
    wrapper: {
        marginTop: theme.spacing.unit
    },
    button:{
        margin: theme.spacing.unit
    }
});

const icons = {
    create: AddIcon,
    update: EditIcon,
    delete: CloseIcon,
    view: SearchIcon
};

class Permission extends Component {

    constructor(props) {
        super(props);

        props.onLoad();
        this.handleKeywordChange = this.handleKeywordChange.bind(this);
        this.handleChangeTab = this.handleChangeTab.bind(this);
        this.handleSelectUserItem = this.handleSelectUserItem.bind(this);
        this.handleClickSaveButton = this.handleClickSaveButton.bind(this);
        props.onSearch(props.activeTab==0?"user":"permission_group","",false);
    }

    handleExpand(subMenuId) {
        const { onExpand } = this.props;

        return (e, expanded) => onExpand(subMenuId);

    }

    handleAllSelect(subMenuId) {

        const { permissionValues, onChangeValues } = this.props;

        return ({ currentTarget }) => {
            let modedPermissionValues = permissionValues.filter(a => !a.startsWith(subMenuId + '.'));

            if (currentTarget.checked) {
                modedPermissionValues.push(subMenuId);
            }

            onChangeValues(modedPermissionValues);
        }
    }

    handleKeywordChange(keyword){
        const {onKeywordChange,onSearch,activeTab} = this.props;

        onKeywordChange(keyword);
        onSearch(activeTab==0?"user":"permission_group",keyword,true);
    }

    handleSelectUserItem(type,item,checked){
        const {users, permissionGroups,onChangeUsers,onSearchByUser} = this.props;

        if(!users.length&&!permissionGroups.length) onSearchByUser(item,type)

        let modUsers = [...users];
        let modedPermissionGroups = [...permissionGroups];
        
        if(type=='user'){
            modUsers = users.filter(user=>user.value!=item.value);
            if(checked)
                modUsers = [...modUsers,item];
        } else {
            modedPermissionGroups = permissionGroups.filter(group=>group.value!=item.value);
            if(checked)
                modedPermissionGroups = [...modedPermissionGroups,item];
        }

        onChangeUsers(modUsers,modedPermissionGroups);
    }

    handleSelect(subMenuId, itemId, actionId) {
        const { permissionValues, onChangeValues } = this.props;

        const permissionId = subMenuId + '.' + itemId + '.' + actionId;

        return ({ currentTarget }) => {
            let modedPermissionValues = permissionValues.filter(id => id != subMenuId && id != permissionId);

            if (currentTarget.checked) {

                modedPermissionValues.push(permissionId);
            }

            onChangeValues(modedPermissionValues);
        }
    }

    handleClickSaveButton(){
        const {users,permissionGroups,permissionValues,onSavePermissions} = this.props;

        onSavePermissions(users,permissionGroups,permissionValues);
    }

    renderMainItems(permissions,sectionId) {
        const { classes, expanded, permissionValues } = this.props;

        if(!permissions){
            return null;
        }

        return Object.keys(permissions).map((subMenuId, index) => {
            const { items, title } = permissions[subMenuId];

            let indeterminate = false;
            let checked = permissionValues.includes(sectionId+'.'+subMenuId);

            permissionValues.forEach(value => {
                if (value.startsWith(sectionId+'.'+subMenuId + '.'))
                    indeterminate = true;
            });


            return (
                <ExpansionPanel key={index} expanded={subMenuId == expanded} onChange={this.handleExpand(subMenuId)}>
                    <ExpansionPanelSummary expandIcon={<ExpandMoreIcon />} >
                        <Checkbox onChange={this.handleAllSelect(sectionId+'.'+subMenuId)} checked={indeterminate || checked} indeterminate={indeterminate} className={classes.mainCheckbox} />
                        <Typography>{title}</Typography>
                    </ExpansionPanelSummary>
                    <ExpansionPanelDetails>
                        <List className={classes.list} dense >
                            {this.renderSubItems(items, sectionId+'.'+subMenuId)}
                        </List>
                    </ExpansionPanelDetails>
                </ExpansionPanel>
            )
        })
    }

    renderSubItems(items, subMenuId) {

        const { classes } = this.props;

        return Object.keys(items).map((subItemId, index) => {
            const { title, hasActions } = items[subItemId];

            return (
                <ListItem className={classes.listItem} key={index} divider >
                    <ListItemText primary={title} />
                    {this.renderActions(subMenuId, subItemId, hasActions)}
                </ListItem>
            );
        })

    }

    renderActions(subMenuId, subItemId, hasActions) {
        const { classes, permissionValues } = this.props;

        if (!hasActions) {

            let permissionId = subMenuId + '.' + subItemId + '.all';

            let checked = permissionValues.includes(subMenuId) || permissionValues.includes(permissionId);

            return (
                <Checkbox onChange={this.handleSelect(subMenuId, subItemId, 'all')} checkedIcon={<CheckIcon className={classes.checkedIcon} />} icon={<CheckIcon className={classes.unCheckedIcon} />} checked={checked} />
            );
        }

        return ["view", "create", "update", "delete"].map((action, index) => {
            const Icon = icons[action];

            let permissionId = subMenuId + '.' + subItemId + '.' + action;

            let checked = permissionValues.includes(subMenuId) || permissionValues.includes(permissionId);

            return (
                <Checkbox onChange={this.handleSelect(subMenuId, subItemId, action)} key={index} checkedIcon={<Icon className={classes.checkedIcon} />} icon={<Icon className={classes.unCheckedIcon} />} checked={checked} />
            );
        })
    }

    handleChangeTab(tab){
        const {onChangeTab,onSearch,keyword} = this.props;

        onChangeTab(tab);
        onSearch(tab==0?"user":"permission_group",keyword,false);
    }

    render() {

        const { classes,activeTab,keyword,results,users,permissionGroups,onClearPage,permissions } = this.props;

        return (
            <Layout sidebar>

                <Typography variant="h5" >Permissions</Typography>
                <Divider />
                <Grid container>

                    <Grid item md={5}>
                        <SearchPanel
                            tabs={[
                                {
                                    label: "Users",
                                    type: "user"
                                },
                                {
                                    label: "Group",
                                    type: "permission_group"
                                }
                            ]}
                            onChangeTab={this.handleChangeTab}
                            activeTab={activeTab}
                            keyword={keyword}
                            onKeywordChange={this.handleKeywordChange}
                            results={results}
                            values={{
                                user:users,
                                permission_group:permissionGroups
                            }}
                            onSelect={this.handleSelectUserItem}
                        />
                        <Divider/>
                        <Button onClick={this.handleClickSaveButton} className={classes.button} variant="contained" margin="dense" color="primary">
                            <SaveIcon/>
                            Save
                        </Button>
                        <Button onClick={onClearPage} className={classes.button} variant="contained" margin="dense" color="secondary">
                            Cancel
                        </Button>
                    </Grid>
                    <Grid item md={7}>
                        <div className={classes.wrapper} >
                            <Typography variant="h5" align="center">Medical</Typography>
                            <Divider/>
                            {this.renderMainItems(permissions['medical'],'medical')}
                            <Typography variant="h5" align="center">Sales</Typography>
                            <Divider/>
                            {this.renderMainItems(permissions['sales'],'sales')}
                            <Typography variant="h5" align="center">Distributor</Typography>
                            <Divider/>
                            {this.renderMainItems(permissions['distributor'],'distributor')}
                            <Typography variant="h5" align="center">Common</Typography>
                            <Divider/>
                            {this.renderMainItems(permissions['common'],'common')}
                        </div>
                    </Grid>
                </Grid>
            </Layout>
        );
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(Permission));