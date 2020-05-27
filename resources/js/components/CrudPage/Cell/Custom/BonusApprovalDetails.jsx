import React, { Component } from 'react';
import Launch from '@material-ui/icons/Launch';
import withStyles from '@material-ui/core/styles/withStyles';
import Modal from '@material-ui/core/Modal';
import Paper from '@material-ui/core/Paper';
import Typography from '@material-ui/core/Typography';
import Divider from '@material-ui/core/Divider';
import Table from '@material-ui/core/Table';
import TableHead from '@material-ui/core/TableHead';
import  TableRow from '@material-ui/core/TableRow';
import  TableCell from '@material-ui/core/TableCell';
import  TableBody from '@material-ui/core/TableBody';
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import ListItemSecondaryAction from "@material-ui/core/ListItemSecondaryAction";
import TextField from "@material-ui/core/TextField";
import Toolbar from '@material-ui/core/Toolbar';
import Button from '@material-ui/core/Button';
import agent from '../../../../agent';


const styles = theme => ({
    center: {
        textAlign: 'center'
    },
    icon: {
        cursor: 'pointer',
        color: theme.palette.primary.main
    },
    paper: {
        width: '60vw',
        position: 'absolute',
        left: '25vw',
        top: '10vh',
        padding: theme.spacing.unit
    },
    scrollable: {
        maxHeight: theme.spacing.unit * 50,
        overflowY: 'auto'
    },
    table:{
        marginTop:theme.spacing.unit
    },
    tableHeaderCell:{
        color:theme.palette.common.white,
        background:theme.palette.grey[600]
    },
    darkTitle:{
        color:theme.palette.common.white,
        background:theme.palette.grey[900]
    },
    grow: {
        flexGrow: 1
    },
    margin: {
        margin: theme.spacing.unit
    }
})

class BonusApprovalDetails extends Component {

    constructor(props){
        super(props);

        this.state = {
            open:false,
            saved:false,
            bonusLines: props.value?props.value.bonusLines.mapToObject('id'):{}
        }

        this.handleClickReset = this.handleClickReset.bind(this);
        this.handleClickSave = this.handleClickSave.bind(this);
    }

    handleOpen() {
        this.setState({ open: true })
    }

    handleClose() {
        this.setState({ open: false })
    }

    handleChangeBonusQty(id,productId){
        return e=>{
            const {bonusLines} = this.state;
            let qty = parseInt(e.target.value);

            if(isNaN(qty)||qty<0)
                return;

            this.setState({bonusLines:{
                ...bonusLines,
                [id]:{
                    ...bonusLines[id],
                    products:{
                        ...bonusLines[id].products,
                        [productId]:{
                            ...bonusLines[id].products[productId],
                            qty
                        }
                    }
                }
            }})
        }
    }

    handleClickSave(){
        const {value,onDialog} = this.props;
        const {bonusLines} = this.state;

        agent.BonusApproval.approve(value.invoiceId,bonusLines).then(({success,message})=>{
            if(success){
                onDialog(message,'success');
                this.setState({open:false,saved:true});
            }
        }).catch(({response})=>{
            onDialog(response.data.message,'error');
        })
    }

    handleClickReset(){
        const {value} = this.props;
        

        this.setState({
            bonusLines: value.bonusLines.mapToObject('id')
        });
    }

    render() {
        const { classes, value } = this.props;

        if(!value)
            return null;

        const {
            invoiceNumber,
            invoiceId,
            lines
        } = value;

        const {bonusLines,saved} = this.state;

        if(saved)
            return null;

        // Get already filled bonus ids
        let existBonusIds = [];

        Object.values(bonusLines).map((line,lineKey)=>{
            Object.values(line.products).map((product,productKey)=>{
                if(product.qty&&!existBonusIds.includes(line.id))
                    existBonusIds.push(line.id)
            });
        });

        return (
            <div className={classes.center} >
                <Launch className={classes.icon} onClick={(this.handleOpen).bind(this)}>Approve</Launch>
                <Modal
                    open={this.state.open}
                    onClose={(this.handleClose).bind(this)}
                >
                    <Paper className={classes.paper}>
                        <Toolbar variant="dense" >
                            <Typography variant="h5">{invoiceNumber}</Typography>
                            <div className={classes.grow}/>
                            <Button variant="contained" className={classes.margin} onClick={this.handleClickReset} color="secondary">Reset</Button>
                            <Button variant="contained" className={classes.margin} onClick={this.handleClickSave} color="primary">Approve</Button>
                        </Toolbar>
                        <Divider />
                        <div className={classes.scrollable}>
                            <Typography className={classes.darkTitle} variant="h6">Purchased Products</Typography>
                            <Table className={classes.table}>
                                <TableHead>
                                    <TableRow>
                                        <TableCell className={classes.tableHeaderCell}>Product</TableCell>
                                        <TableCell className={classes.tableHeaderCell}>Batch</TableCell>
                                        <TableCell className={classes.tableHeaderCell} align="right">Discount</TableCell>
                                        <TableCell className={classes.tableHeaderCell} align="right">Qty</TableCell>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {lines.map((item, index) => (
                                        <TableRow key={index}>
                                            <TableCell align="left">{item.product.label}</TableCell>
                                            <TableCell align="left">{item.batch.label}</TableCell>
                                            <TableCell align="left">{item.discount}</TableCell>
                                            <TableCell align="right">{item.qty}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                            <Typography className={classes.darkTitle} variant="h6">Bonus Products</Typography>
                            {Object.values(bonusLines).map((line,lineKey)=>(
                                <div className={classes.margin} key={lineKey}>
                                    <Divider />
                                    <Typography variant="h6">{line.label} ({line.qty} Free)</Typography>
                                    <List variant="dense" >
                                        {Object.values(line.products).map((product,key)=>(
                                            <ListItem divider={true} button={true} key={key} >
                                                <ListItemText>{product.label}</ListItemText>
                                                <ListItemSecondaryAction>
                                                    <TextField
                                                        disabled={existBonusIds.filter(id=>line.excludes.includes(id)).length>0}
                                                        label="Qty"
                                                        variant="outlined"
                                                        margin="dense"
                                                        type="number"
                                                        value={product.qty}
                                                        onChange={this.handleChangeBonusQty(line.id,product.value)}
                                                    />
                                                </ListItemSecondaryAction>
                                            </ListItem>
                                        ))}
                                    </List>
                                </div>
                            ))}
                        </div>
                    </Paper>
                </Modal>
            </div>
        )
    }

}

export default withStyles(styles)(BonusApprovalDetails);
