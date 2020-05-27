import React, { Component } from 'react';
import Launch from '@material-ui/icons/Launch';
import withStyles from '@material-ui/core/styles/withStyles';
import Modal from '@material-ui/core/Modal';
import  Paper  from '@material-ui/core/Paper';
import Typography from '@material-ui/core/Typography';
import Divider from '@material-ui/core/Divider';
import  List from '@material-ui/core/List';
import  ListItem from '@material-ui/core/ListItem';
import  ListItemText from '@material-ui/core/ListItemText';

const styles = theme => ({
    center: {
        textAlign: 'center'
    },
    icon: {
        cursor: 'pointer',
        color: theme.palette.primary.main
    },
    paper:{
        width:'30vw',
        position:'absolute',
        left:'35vw',
        top:'10vh',
        padding:theme.spacing.unit
    },
    scrollable:{
        maxHeight:theme.spacing.unit*50,
        overflowY:'auto'
    }
})

class TreeSelect extends Component {

    constructor(props){
        super(props);

        this.state = {
            open:false
        }
    }

    handleOpen() {
        this.setState({ open: true })
    }

    handleClose() {
        this.setState({ open: false })
    }

    render() {
        const { classes,value } = this.props;

        return (
            <div className={classes.center} >
                <Launch className={classes.icon} onClick={(this.handleOpen).bind(this)}>View</Launch>
                <Modal
                    open={this.state.open}
                    onClose={(this.handleClose).bind(this)}
                >
                    <Paper className={classes.paper}>
                        <Typography align="center" variant="h5">Selected Records</Typography>
                        <Divider/>
                        <div className={classes.scrollable}>
                            <List>
                                {value.map((node,i)=>(
                                    <ListItem key={i} >
                                        <ListItemText primary={node.name} />
                                    </ListItem>
                                ))}
                                {value.length?null:
                                    <ListItem >
                                        <ListItemText primary="All Selected" />
                                    </ListItem>
                                }
                            </List>
                        </div>
                    </Paper>
                </Modal>
            </div>
        )
    }

}

export default withStyles(styles)(TreeSelect);