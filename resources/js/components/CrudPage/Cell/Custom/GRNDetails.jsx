import React, { Component } from 'react';
import Launch from '@material-ui/icons/Launch';
import withStyles from '@material-ui/core/styles/withStyles';
import Modal from '@material-ui/core/Modal';
import Paper from '@material-ui/core/Paper';
import Typography from '@material-ui/core/Typography';
import Divider from '@material-ui/core/Divider';
import Table from '@material-ui/core/Table';
import TableHead from '@material-ui/core/TableHead';
import TableRow from '@material-ui/core/TableRow';
import TableCell from '@material-ui/core/TableCell';
import TableBody from '@material-ui/core/TableBody';

const styles = theme => ({
    center: {
        textAlign: 'center'
    },
    icon: {
        cursor: 'pointer',
        color: theme.palette.primary.main
    },
    paper: {
        width: '90vw',
        position: 'absolute',
        left: '5vw',
        top: '10vh',
        padding: theme.spacing.unit
    },
    scrollable: {
        maxHeight: theme.spacing.unit * 50,
        overflowY: 'auto'
    },
    table: {
        marginTop: theme.spacing.unit
    },
    tableHeaderCell: {
        color: theme.palette.common.white,
        background: theme.palette.grey[900],
        border: 'solid 1px ' + theme.palette.common.white
    }
})

class GRNDetails extends Component {

    constructor(props) {
        super(props);

        this.state = {
            open: false
        }
    }

    handleOpen() {
        this.setState({ open: true })
    }

    handleClose() {
        this.setState({ open: false })
    }

    render() {
        const { classes, value } = this.props;

        return (
            <div className={classes.center} >
                <Launch className={classes.icon} onClick={(this.handleOpen).bind(this)}>View</Launch>
                <Modal
                    open={this.state.open}
                    onClose={(this.handleClose).bind(this)}
                >
                    <Paper className={classes.paper}>
                        <Typography align="center" variant="h5">Products for {value.title}</Typography>
                        <Divider />
                        <div className={classes.scrollable}>
                            <Table className={classes.table}>
                                <TableHead>
                                    <TableRow>
                                        <TableCell rowSpan="2" className={classes.tableHeaderCell}>Product</TableCell>
                                        <TableCell colspan="3" className={classes.tableHeaderCell} align="left">Purchase Order</TableCell>
                                        <TableCell colspan="5" className={classes.tableHeaderCell} align="left">Good Received Note</TableCell>
                                        <TableCell colspan="2" className={classes.tableHeaderCell} align="left">Difference</TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell className={classes.tableHeaderCell} align="right">Price</TableCell>
                                        <TableCell className={classes.tableHeaderCell} align="right">Qty</TableCell>
                                        <TableCell className={classes.tableHeaderCell} align="right">Amount</TableCell>
                                        <TableCell className={classes.tableHeaderCell} align="right">Batch</TableCell>
                                        <TableCell className={classes.tableHeaderCell} align="right">Expire Date</TableCell>
                                        <TableCell className={classes.tableHeaderCell} align="right">Price</TableCell>
                                        <TableCell className={classes.tableHeaderCell} align="right">Qty</TableCell>
                                        <TableCell className={classes.tableHeaderCell} align="right">Amount</TableCell>
                                        <TableCell className={classes.tableHeaderCell} align="right">Qty</TableCell>
                                        <TableCell className={classes.tableHeaderCell} align="right">Amount</TableCell>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {value.products.map((item, index) => (
                                        <TableRow key={index}>
                                            <TableCell style={{ display: item.po_rowspan > 0 ? undefined : "none" }} rowSpan={item.po_rowspan} align="left">{item.product.label}</TableCell>
                                            <TableCell style={{ display: item.po_rowspan > 0 ? undefined : "none" }} rowSpan={item.po_rowspan} align="left">{item.po_price}</TableCell>
                                            <TableCell style={{ display: item.po_rowspan > 0 ? undefined : "none" }} rowSpan={item.po_rowspan} align="left">{item.po_qty}</TableCell>
                                            <TableCell style={{ display: item.po_rowspan > 0 ? undefined : "none" }} rowSpan={item.po_rowspan} align="left">{item.po_amount}</TableCell>
                                            <TableCell align="left">{item.grn_batch.label}</TableCell>
                                            <TableCell align="left">{item.grn_expire}</TableCell>
                                            <TableCell align="left">{item.grn_price}</TableCell>
                                            <TableCell align="left">{item.grn_qty}</TableCell>
                                            <TableCell align="left">{item.grn_amount}</TableCell>
                                            <TableCell style={{ display: item.po_rowspan > 0 ? undefined : "none" }} rowSpan={item.po_rowspan} align="left">{item.dif_qty}</TableCell>
                                            <TableCell style={{ display: item.po_rowspan > 0 ? undefined : "none" }} rowSpan={item.po_rowspan} align="left">{item.dif_amount}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    </Paper>
                </Modal>
            </div>
        )
    }

}

export default withStyles(styles)(GRNDetails);
