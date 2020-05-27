import React, {Component} from "react";
import Modal from "@material-ui/core/Modal";
import Paper from "@material-ui/core/Paper";
import withStyles from "@material-ui/core/styles/withStyles";
import Typography from "@material-ui/core/Typography";
import Divider from "@material-ui/core/Divider";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import ListItemSecondaryAction from "@material-ui/core/ListItemSecondaryAction";
import TextField from "@material-ui/core/TextField";
import Toolbar from "@material-ui/core/Toolbar";
import Button from "@material-ui/core/Button";

const styler = withStyles(theme=>({
    paper: {
        width: "30%",
        marginLeft: "35%",
        marginTop: 100,
        padding: theme.spacing.unit
    },
    textField: {
        width: 60
    },
    scrollArea: {
        maxHeight: 340,
        overflowY: "auto"
    },
    grow: {
        flexGrow: 1
    },
    margin: {
        margin: theme.spacing.unit
    }
}))

class BatchEditForm extends Component {

    constructor(props){
        super(props);

        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleConfirm = this.handleConfirm.bind(this);
    }

    handleChangeQty(id){
        return e=>{

            const {qty, details} = this.props;

            let modQty = 0;

            for (const detail of Object.values(details)){
                if(id!=detail.id){
                    modQty += detail.qty;
                }
            }
            
            let newQty = parseInt(e.target.value);

            if(isNaN(newQty)){
                this.props.onChange(id, "");
                return;
            }

            let stock = details[id].stock;

            if(stock<newQty)
                return;

            modQty += newQty;

            if(modQty>qty)
                return;

            this.props.onChange(id, newQty);
        }
    }

    handleConfirm(){
        const {onConfirm, qty, details} = this.props;

        let modQty = 0;

        for (const detail of Object.values(details)){
            modQty += detail.qty;
        }

        if(modQty<qty)
            return;

        onConfirm();
    }

    render(){

        const {open, details, onClose, classes} = this.props;

        return (
            <Modal open={open} onClose={onClose} >
                <Paper className={classes.paper} >
                    <Typography align="center" variant="h5" >Batch Wise Details</Typography>
                    <Divider />
                    <div className={classes.scrollArea} >
                        <List >
                            {Object.values(details).map(({code, stock, expire, qty, id},key)=>(
                                <ListItem key={key} dense={true} divider={true} >
                                    <ListItemText primary={code} secondary={"Stock:- "+stock+" | Expire:- "+expire} />
                                    <ListItemSecondaryAction>
                                        <TextField
                                            label="Qty"
                                            variant="outlined"
                                            className={classes.textField}
                                            margin="dense"
                                            value={qty}
                                            onChange={this.handleChangeQty(id)}
                                        />
                                    </ListItemSecondaryAction>
                                </ListItem>
                            ))}
                        </List>
                    </div>
                    <Toolbar>
                        <div className={classes.grow} />
                        <Button className={classes.margin} onClick={this.handleConfirm} color="primary" variant="contained">Confirm</Button>
                        <Button className={classes.margin} onClick={onClose} color="secondary" variant="contained">Cancel</Button>
                    </Toolbar>
                </Paper>
            </Modal>
        )
    }
}

export default styler (BatchEditForm);
