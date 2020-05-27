import React, { Component } from "react";
import PropTypes from "prop-types";
import withStyles from "@material-ui/core/styles/withStyles";
import Paper from "@material-ui/core/Paper";
import AppBar from "@material-ui/core/AppBar";
import Tabs from "@material-ui/core/Tabs";
import Tab from "@material-ui/core/Tab";
import TextField from "@material-ui/core/TextField";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import Checkbox from "@material-ui/core/Checkbox";

const styles = theme => ({
    paper: {
        position: 'relative',
        margin: theme.spacing.unit,
        background: theme.palette.grey[400]
    },
    paperContents: {
        height: '90vh',
        overflowY:'auto'
    },
    listItemText:{
        background:theme.palette.common.white
    },
    checkbox:{
        width:theme.spacing.unit,
        height:theme.spacing.unit
    }
});

class SearchPanel extends Component {

    constructor(props) {
        super(props);

        this.handleTabChange = this.handleTabChange.bind(this);
        this.handleKeywordChange = this.handleKeywordChange.bind(this);
    }

    renderTabs() {
        const { tabs } = this.props;

        return tabs.map((tab, index) => (
            <Tab key={index} label={tab.label} />
        ));
    }

    handleTabChange(e, tab) {
        const { onChangeTab } = this.props;

        onChangeTab(tab)
    }

    handleKeywordChange({ currentTarget }) {
        const { onKeywordChange } = this.props;

        onKeywordChange(currentTarget.value);
    }

    handlePickItem(type,item){
        const {onSelect} = this.props;

        return ({currentTarget})=>{
            onSelect(type,item,currentTarget.checked)
        }
    }

    renderResults(){
        const {results,classes,activeTab,tabs,values} = this.props;

        let type = tabs[activeTab].type;

        let modedResults = results.map(({value,label})=>({value,label,checked:false})).mapToObject('value');

        const modedValues = values[type].map(({value,label})=>({value,label,checked:true})).mapToObject('value');

        modedResults = {...modedResults,...modedValues};

        let modedResultValues = Object.keys(modedResults).sort((a,b)=>{
            return modedResults[a].checked?1:-1
        });

        return modedResultValues.map(value=>{

            const {label,checked} = modedResults[value];

            return (
                <ListItem divider className={classes.listItemText} dense key={value}>
                    <ListItemText primary={label}/>
                    <Checkbox className={classes.checkbox} onChange={this.handlePickItem(type,{value,label})} checked={checked} />
                </ListItem>
            )
        })
    }

    render() {
        const { classes, activeTab, tabs, keyword } = this.props;

        const activeTabDetails = tabs[activeTab];

        return (
            <Paper className={classes.paper} >
                <AppBar position="relative">
                    <Tabs
                        value={activeTab || 0}
                        onChange={this.handleTabChange}
                        variant="scrollable"
                        scrollButtons="auto"
                    >
                        {this.renderTabs()}
                    </Tabs>
                </AppBar>
                <TextField label={activeTabDetails.label} fullWidth margin="dense" value={keyword} onChange={this.handleKeywordChange} />
                <div className={classes.paperContents} >
                    <List dense >
                        {this.renderResults()}
                    </List>
                </div>
            </Paper>
        );
    }
}

SearchPanel.propTypes = {
    classes: PropTypes.shape({
        paper: PropTypes.string,
        paperContents: PropTypes.string
    }),
    tabs: PropTypes.arrayOf(PropTypes.shape({
        label: PropTypes.string,
        type: PropTypes.string
    })),

    activeTab: PropTypes.number,
    onChangeTab: PropTypes.func,

    keyword: PropTypes.string,
    onKeywordChange: PropTypes.func,

    results: PropTypes.arrayOf(PropTypes.shape({
        label:PropTypes.string,
        value: PropTypes.number
    })),

    values: PropTypes.object,
    onSelect: PropTypes.func
};

export default withStyles(styles)(SearchPanel);