import React , {Component} from "react";
import PropTypes from "prop-types";
import {connect} from "react-redux";

import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";

import withStyles from "@material-ui/core/styles/withStyles";
import AppBar from "@material-ui/core/AppBar";
import Tab from "@material-ui/core/Tab";
import Tabs from "@material-ui/core/Tabs";
import Paper from "@material-ui/core/Paper";
import TextField from "@material-ui/core/TextField";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";

import { changeLeftTab, changeSubTown, changeKeyword,fetchResults } from "../../../actions/Medical/UserCustomer";

const styles = theme=>({
    inputRow:{
        marginTop:theme.spacing.unit*6
    },
    textInput:{
        marginTop:theme.spacing.unit
    },
    list:{
        height:'46vh',
        overflowY:'auto'
    },
    paper:{
        background:theme.palette.grey[400],
        margin:theme.spacing.unit,
        position:"relative",
        padding:theme.spacing.unit/2
    },
    listItem:{
        background:theme.palette.common.white,
        cursor:'pointer'
    },
});

const mapStateToProps = state=> ({
    ...state.UserCustomer
})

const mapDispatchToProps = dispatch=>({
    onTabChange: (tab)=>dispatch(changeLeftTab(tab)),
    onSubTownChange: subTown=>dispatch(changeSubTown(subTown)),
    onKeywordChange: (keyword)=>dispatch(changeKeyword(keyword)),
    onSearch: (link,keyword,subTown,delay)=>dispatch(fetchResults(link,keyword,subTown,delay))
})

class SearchPanel extends Component{

    constructor(props){
        super(props);

        this.handleSubTownChange = this.handleSubTownChange.bind(this);
        this.handleTabChange = this.handleTabChange.bind(this);
        this.handleTextChange = this.handleTextChange.bind(this);
        let link = this.getCurrentTabLink();

        props.onSearch(link,"",undefined,false);
    }

    getCurrentTabLink(){
        const {leftTab,tabs} = this.props;

        return tabs[leftTab].link;
    }

    handleSubTownChange(value){
        const {keyword,onSearch,onSubTownChange} = this.props;

        const link = this.getCurrentTabLink();

        onSubTownChange(value);
        onSearch(link,keyword,value,false);
    }

    handleTabChange(e,tab){
        const {keyword,subTown,onTabChange,tabs,onSearch} = this.props;

        let link = tabs[tab].link;

        onTabChange(tab);
        onSearch(link,keyword,subTown,false);
    }

    handleTextChange({currentTarget}){
        const {subTown,onSearch,onKeywordChange} = this.props;

        let link = this.getCurrentTabLink();

        let keyword = currentTarget.value;

        onKeywordChange(keyword);
        onSearch(link,keyword,subTown,true);
    }

    renderTabs(){
        const {tabs} = this.props;

        return tabs.map(({label},index)=>(
            <Tab label={label} key={index}/>
        ));
    }

    renderListItems(){
        const {results,classes,onSelect} = this.props;

        let link = this.getCurrentTabLink();

        return results.map(({label,value})=>(
            <ListItem onClick={e=>onSelect(link,{label,value})} button dense className={classes.listItem} divider key={value} >
                <ListItemText primary={label}/>
            </ListItem>
        ));
    }


    render(){
        const {classes,leftTab,subTown,keyword} = this.props;

        return (
            <Paper className={classes.paper}>
                <AppBar position="absolute">
                    <Tabs
                        value={leftTab}
                        onChange={this.handleTabChange}
                        variant="scrollable"
                        scrollButtons="auto"
                    >
                        {this.renderTabs()}
                    </Tabs>
                </AppBar>
                <div className={classes.inputRow}>
                    <AjaxDropdown value={subTown} onChange={this.handleSubTownChange} label="Sub Town" link="sub_town"/>
                    <TextField margin="dense" onChange={this.handleTextChange} value={keyword} className={classes.textInput} fullWidth label="Customer Name Or Code" variant="outlined" />
                </div>
                <List className={classes.list}>
                    {this.renderListItems()}
                </List>
            </Paper>
        );
    }
}

SearchPanel.propTypes = {
    tabs:PropTypes.arrayOf(PropTypes.shape({
        label:                  PropTypes.string,
        link:                   PropTypes.string
    })),

    classes: PropTypes.shape({
        inputRow:               PropTypes.string,
        textInput:              PropTypes.string,
        list:                   PropTypes.string,
        paper:                  PropTypes.string,
        listItem:               PropTypes.string,
    }),

    onTabChange:                PropTypes.func,
    leftTab:                    PropTypes.number,

    subTown: PropTypes.shape({
        value:                  PropTypes.number,
        label:                  PropTypes.string
    }),
    onSubTownChange:            PropTypes.func,

    keyword:                    PropTypes.string,
    onKeywordChange:            PropTypes.func
}

export default connect(mapStateToProps,mapDispatchToProps) (withStyles(styles) (SearchPanel));