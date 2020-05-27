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
        background:theme.palette.grey[900]
    }
})

class BonusLines extends Component {

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
        const { classes, value } = this.props;

        if(!value||!value.lines)
            return null;

        return (
            <div className={classes.center} >
                <Launch className={classes.icon} onClick={(this.handleOpen).bind(this)}>View</Launch>
                <Modal
                    open={this.state.open}
                    onClose={(this.handleClose).bind(this)}
                >
                    <Paper className={classes.paper}>
                        <Typography align="center" variant="h5">Ratios</Typography>
                        <Divider />
                        <div className={classes.scrollable}>
                            <Table className={classes.table}>
                                <TableHead>
                                    <TableRow>
                                        <TableCell className={classes.tableHeaderCell} align="right">Min</TableCell>
                                        <TableCell className={classes.tableHeaderCell} align="right">Max</TableCell>
                                        <TableCell className={classes.tableHeaderCell} align="right">Purchase</TableCell>
                                        <TableCell className={classes.tableHeaderCell} align="right">Free</TableCell>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {value.lines.map((item, index) => (
                                        <TableRow key={index}>
                                            <TableCell align="right">{item.min}</TableCell>
                                            <TableCell align="right">{item.max}</TableCell>
                                            <TableCell align="right">{item.purchase}</TableCell>
                                            <TableCell align="right">{item.free}</TableCell>
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

export default withStyles(styles)(BonusLines);