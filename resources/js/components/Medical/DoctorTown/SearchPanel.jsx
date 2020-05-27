import React , {Component} from 'react';
import PropTypes from 'prop-types';

import Paper from "@material-ui/core/Paper";
import AppBar from "@material-ui/core/AppBar";
import Typography from "@material-ui/core/Typography";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import List from "@material-ui/core/List";
import withStyles from "@material-ui/core/styles/withStyles";
import ListItemSecondaryAction from "@material-ui/core/ListItemSecondaryAction";
import Checkbox from "@material-ui/core/Checkbox";
import TextField from "@material-ui/core/TextField";

const styles = theme=>({
    paperContents:{
        height: '80vh',
        overflowY:'auto',
        paddingTop:60
    },
    paper: {
        position: 'relative',
        margin: theme.spacing.unit,
        background: theme.palette.grey[400]
    },
    whiteBackground:{
        background: theme.palette.common.white
    },
    whiteText:{
        color: theme.palette.common.white
    }
})

class SearchPanel extends Component {

    constructor(props){
        super(props);
        this.state = {
            keyword:""
        }

        this.handleChangeText = this.handleChangeText.bind(this);
    }

    handleCheck(item){
        const {onCheck} = this.props;
        return e=>{
            onCheck(item);
        }
    }

    handleChangeText(e){
        const {onSearch} = this.props;

        this.setState({keyword:e.target.value});
        onSearch(e.target.value);
    }

    renderItems(){
        const {items,selectedItems,classes} = this.props;

        const modedItems = {...items,...selectedItems};

        return Object.values(modedItems).map((item,key)=>{
            if(item){
                return (
                    <ListItem className={classes.whiteBackground} divider key={key} >
                        <ListItemText>{item.label}</ListItemText>
                        <ListItemSecondaryAction>
                            <Checkbox onChange={this.handleCheck(item)} checked={!!selectedItems[item.value]} />
                        </ListItemSecondaryAction>
                    </ListItem>
                )
            } else {
                return null;
            }
        });
    }

    render(){
        const {title,classes} = this.props;
        const {keyword} = this.state;

        return (
            <div>
                <Paper className={classes.paper} >
                    <AppBar position="absolute" >
                        <Typography className={classes.whiteText} variant="h6" align="center" >{title}</Typography>
                    </AppBar>
                    <div className={classes.paperContents} >
                        <TextField value={keyword} label={title} onChange={this.handleChangeText} variant="outlined" fullWidth margin="dense" />
                        <List>
                            {this.renderItems()}
                        </List>
                    </div>
                </Paper>
            </div>
        )
    }
}

SearchPanel.propTypes = {
    title:PropTypes.string.isRequired,
    items: PropTypes.object,
    selectedItems: PropTypes.object,
    onCheck: PropTypes.func
};

export default withStyles(styles) (SearchPanel);