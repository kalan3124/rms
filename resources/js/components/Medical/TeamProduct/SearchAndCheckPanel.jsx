import React, { Component } from "react";
import PropTypes from "prop-types";
import withStyles from "@material-ui/core/styles/withStyles";
import Paper from "@material-ui/core/Paper";
import SearchHeader from "./SearchHeader";
import List from '@material-ui/core/List';
import ListItem from '@material-ui/core/ListItem';
import ListItemText from '@material-ui/core/ListItemText';
import Checkbox from '@material-ui/core/Checkbox';

const styles = theme=>({
    paper: {
        margin: theme.spacing.unit
    },
    list:{
        height:'70vh',
        overflowY:'auto'
    },
    checkbox:{
        width:theme.spacing.unit*3,
        height:theme.spacing.unit*3,
    }
})

class SearchAndCheckPanel extends Component {

    renderResults(){
        const {results,classes,checked} = this.props;

        let modedResults = results.map(({value,label})=>({value,label,checked:false})).mapToObject('value');
        
        let modedChecked = checked.map(({value,label})=>({value,label,checked:true})).mapToObject('value');

        let modedItems = {...modedResults,...modedChecked};

        let modedItemsValues = Object.keys(modedItems).sort((a,b)=>{
            return modedItems[a].checked?1:-1
        });

        return modedItemsValues.map((value,index)=>{
            const {label,checked} = modedItems[value];

            return (
                <ListItem dense button divider key={index}>
                    <ListItemText primary={label}/>
                    <Checkbox checked={checked} className={classes.checkbox} onChange={this.handleCheck({value,label})} />
                </ListItem>
            );
        });
    }

    handleCheck(item){
        const {onCheck} = this.props;
        return (e,checked)=>{
            onCheck(item,checked);
        }
    }

    render() {
        const {icon,label,classes,keyword,onSearch} = this.props;
        return (
            <Paper className={classes.paper} >
                <SearchHeader
                    icon={icon}
                    label={label}
                    value={keyword}
                    onChange={onSearch}
                />
                <List dense className={classes.list} >
                    {this.renderResults()}
                </List>
            </Paper>
        )
    }
}

SearchAndCheckPanel.propTypes = {
    icon: PropTypes.node,
    classes: PropTypes.shape({
        paper: PropTypes.string
    }),
    label: PropTypes.string,
    keyword:PropTypes.string,
    onSearch: PropTypes.func,
    onCheck: PropTypes.func,
    checked: PropTypes.arrayOf(PropTypes.shape({
        value:PropTypes.oneOfType([PropTypes.number,PropTypes.string]),
        label:PropTypes.string
    })),
    results: PropTypes.arrayOf(PropTypes.shape({
        value:PropTypes.oneOfType([PropTypes.number,PropTypes.string]),
        label:PropTypes.string
    }))
}

export default withStyles(styles)(SearchAndCheckPanel);