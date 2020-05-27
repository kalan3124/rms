import React,{ Component } from "react";
import {connect} from "react-redux";
import PropTypes from "prop-types";
import AppBar from "@material-ui/core/AppBar";
import Toolbar from "@material-ui/core/Toolbar";
import Tabs from "@material-ui/core/Tabs";
import Tab from "@material-ui/core/Tab";
import Typography from "@material-ui/core/Typography";
import Divider from "@material-ui/core/Divider";
import withStyles from "@material-ui/core/styles/withStyles";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";

import { fetchIssues, changeType } from "../../actions/IssueTracker";

const style = theme=>({
    issuesWrapper:{
        height:'60vh',
        overflowY:"auto",
        background:theme.palette.grey[400]
    },
    issue:{
        background:theme.palette.common.white
    }
});

const mapStateToProps = state=>({
    ...state,
    ...state.IssueTracker
});

const mapDispatchToProps = dispatch=>({
    onSearch:(state,page)=>dispatch(fetchIssues(state,page)),
    onTabChange:value=>dispatch(changeType(value))
});

class ViewPage extends Component {

    constructor(props){
        super(props);

        props.onSearch("opened",1);
        this.handleTabChange = this.handleTabChange.bind(this);
    }

    handleTabChange(e,value){
        const {onTabChange,onSearch,page} = this.props;

        onTabChange(value);
        onSearch(value,page);
    }

    renderIssues(){
        const {issues,classes} = this.props;

        return issues.map(({title,createdAt,dueAt,assignees,closedBy,closedAt},index)=>(
            <ListItem divider className={classes.issue} key={index}>
                <ListItemText primary={title} secondary={this.renderSecondaryDescription(createdAt,dueAt,assignees,closedBy,closedAt)}/>
            </ListItem>
        ))
    }

    renderSecondaryDescription(createdAt,dueAt,assignees,closedBy,closedAt){
        let description = "Created At:- "+createdAt;

        if(dueAt)
            description += " | Due At:- "+dueAt;

        if(typeof assignees!="undefined"&&assignees.length){
            description += " | Assignees:- ";
        
            description += assignees.map(({name})=>name).join(" , ");
        }

        if(closedAt){
            description += " | Closed by "+closedBy.name+" at "+closedAt;
        }

        return description;
    }

    render() {
        const {classes,state} = this.props;

        return (
            <div>
                <Typography variant="h6" align="center">Submited issues</Typography>
                <Divider />
                <AppBar position="relative" >
                    <Toolbar variant="dense" >
                        <Tabs onChange={this.handleTabChange} value={state} >
                            <Tab value="opened" label="Opened" />
                            <Tab value="closed" label="Closed" />
                        </Tabs>
                    </Toolbar>
                </AppBar>
                <div className={classes.issuesWrapper}>
                    <List dense>
                        {this.renderIssues()}
                    </List>
                </div>
            </div>
        );
    }
}

ViewPage.propTypes = {
    onSearch: PropTypes.func,
    state: PropTypes.string,
    onTabChange: PropTypes.func,
    page: PropTypes.number
}

export default connect(mapStateToProps,mapDispatchToProps) (withStyles(style)(ViewPage));