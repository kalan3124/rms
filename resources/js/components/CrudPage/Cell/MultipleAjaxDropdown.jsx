import React, { Component } from "react";
import Divider from "@material-ui/core/Divider";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import withStyles from "@material-ui/core/styles/withStyles";
import Modal from "@material-ui/core/Modal";
import Paper from "@material-ui/core/Paper";
import Typography from "@material-ui/core/Typography";
// import PropTypes from "prop-types";

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
});

class MultipleAjaxDropdown extends Component {

    constructor(props) {
        super(props);

        this.state = {
            open: false
        };

        this.handleClickOpen = this.handleClickOpen.bind(this);
        this.handleCloseModal = this.handleCloseModal.bind(this);
    }

    handleCloseModal() {
        this.setState({open:false})
    }

    handleClickOpen(){
        this.setState({open:true})
    }

    renderWholeList() {
        const { values, classes } = this.props;
        return (
            <List dense>
                {values.map((value,i) => (
                    <ListItem dense divider key={i}>
                        <ListItemText primary={value.label} />
                    </ListItem>
                ))}
            </List>
        )
    }

    renderMinimizedList() {
        return (
            <div>
                <List dense>
                    {
                        this.renderFewItems()
                    }
                </List>
                {this.renderModel()}
            </div>
        )
    }

    renderModel() {

        const {values,classes} = this.props;

        return (
            <Modal
                open={this.state.open}
                onClose={this.handleCloseModal}
            >
                <Paper className={classes.paper}>
                    <Typography align="center" variant="h5">Selected Records</Typography>
                    <Divider />
                    <div className={classes.scrollable}>
                        <List dense>
                            {values.map((node, i) => (
                                <ListItem divider dense key={i} >
                                    <ListItemText primary={node.label} />
                                </ListItem>
                            ))}
                        </List>
                    </div>
                </Paper>
            </Modal>
        );
    }

    renderFewItems() {
        const { values } = this.props;

        let listItems = values.slice(0, 3).map(value => (
            <ListItem dense divider key={value.value}>
                <ListItemText primary={value.label} />
            </ListItem>
        ));

        listItems.push(
            <ListItem onClick={this.handleClickOpen} dense button divider key={0}>
                <ListItemText secondary={"See all " + values.length + " records."} />
            </ListItem>
        );

        return listItems;
    }

    render() {
        const { values } = this.props;

        if(!values)
            return null;

        return (
            <div>
                {(values.length > 5) ? this.renderMinimizedList() : this.renderWholeList()}
            </div>
        );

    }
}

MultipleAjaxDropdown.propTypes = {

}

export default withStyles(styles)(MultipleAjaxDropdown);
