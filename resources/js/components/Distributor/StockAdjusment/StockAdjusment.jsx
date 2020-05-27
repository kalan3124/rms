import React, { Component } from "react";
import { connect } from "react-redux";
import Layout from "../../App/Layout";
import SearchIcon from "@material-ui/icons/Search";
import SaveIcon from "@material-ui/icons/Save";
import Typography from "@material-ui/core/Typography";
import Toolbar from "@material-ui/core/Toolbar";
import withStyles from "@material-ui/core/styles/withStyles";
import TextField from "@material-ui/core/TextField";
import Button from "@material-ui/core/Button";
import Divider from "@material-ui/core/Divider";
import Table from "@material-ui/core/Table";
import TableHead from "@material-ui/core/TableHead";
import TableRow from "@material-ui/core/TableRow";
import TableCell from "@material-ui/core/TableCell";
import TableBody from "@material-ui/core/TableBody";
import Select from "../../CrudPage/Input/Select";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import Grid from "@material-ui/core/Grid";
import Tooltip from '@material-ui/core/Tooltip';
import AddIcon from '@material-ui/icons/Add';
import Fab from '@material-ui/core/Fab';
import IconButton from '@material-ui/core/IconButton';
import RemoveIcon from '@material-ui/icons/Remove';
import red from "@material-ui/core/colors/red";
import blue from "@material-ui/core/colors/blue";
import yellow from "@material-ui/core/colors/yellow";

import {
    changeAdjType, loadAdjNumber, load, changeDis, fetchData, changeData, changeAjuQty, submitData, alert, pageClear
} from "../../../actions/Distributor/StockAdjusment";


const styler = withStyles(theme => ({
    grow: {
        flexGrow: 1
    },
    field: {
        width: 240
    },
    margin: {
        margin: theme.spacing.unit
    },
    darkCell: {
        background: theme.palette.grey[600],
        color: theme.palette.common.white,
        border: 'solid 1px ' + theme.palette.common.white
    },
    select: {
        width: 200,
        // background:theme.palette.common.white
    },
    lightCell: {
        border: '1px solid ' + theme.palette.grey[500]
    },
    red: {
        background: red[400]
    },
    blue: {
        background: blue[400]
    },
    yellow: {
        background: yellow[400]
    }
})); load

export const mapStateToProps = state => ({
    ...state,
    ...state.StockAdjusment
});

export const mapDispatchToProps = dispatch => ({
    onChangeAdjType: type => dispatch(changeAdjType(type)),
    onchangeDis: dis_id => dispatch(changeDis(dis_id)),
    onLoad: (number, dis_id) => dispatch(load(number, dis_id)),
    onfecthData: data => dispatch(fetchData(data)),
    onchangeAjuQty: aju_new_qty => dispatch(changeAjuQty(aju_new_qty)),
    onChangeData: (lastId, product, pro_name, ava_qty, bt_id, aju_qty, batch, reason, total, pro_name_rowspan) => dispatch(changeData(lastId, product, pro_name, ava_qty, bt_id, aju_qty, batch, reason, total, pro_name_rowspan)),
    onsubmitData: (data, type, dis_id, adjNumber, reason) => dispatch(submitData(data, type, dis_id, adjNumber, reason)),
    onAlert: msg => dispatch(alert(msg)),
    onPageCleart: () => dispatch(pageClear()),
});

class StockAdjusment extends Component {
    constructor(props) {
        super(props);

        this.onHandleDisChange = this.onHandleDisChange.bind(this);
        this.handleAdjTypeChange = this.handleAdjTypeChange.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.onHandleSubmit = this.onHandleSubmit.bind(this);
        this.handleChangeReason = this.handleChangeReason.bind(this);
    }

    handleAdjTypeChange(e) {
        const { onChangeAdjType, onLoad, dis_id, onAlert, onfecthData } = this.props;

        if (!dis_id) {
            onAlert('Please select the distributor');
        } else {
            onChangeAdjType(e);
            onLoad(e.value, dis_id);
            onfecthData(dis_id);
        }
    }

    onHandleDisChange(dis_id) {
        const { onchangeDis, onAlert, onfecthData, adjType } = this.props;
        onchangeDis(dis_id);
        if (!adjType) {
            onAlert('Adjusment Type is Required');
        } else {
            onfecthData(dis_id);
        }
    }

    handleChangeQty(lastId) {
        return e => {
            const { onChangeData, adjType } = this.props;
            const { pro_id, pro_name, ava_qty, bt_id, batch, reason, total, pro_name_rowspan } = this.props.rowData[lastId];

            let tot = 0;
            let input = e.target.value;

            if (input < 0) {
                input = 0;
            }

            if (adjType.value == 1) {
                tot = parseInt(input) + parseInt(ava_qty);
            } else {
                tot = parseInt(ava_qty) - parseInt(input);
                if (0 > tot) {
                    input = 0;
                }
            }

            onChangeData(lastId, pro_id, pro_name, ava_qty, bt_id, input, batch, reason, isNaN(tot) || input == 0 ? 0 : tot, pro_name_rowspan);
        }
    }

    handleChangeReason(lastId) {
        return e => {
            const { onChangeData, adjType } = this.props;
            const { pro_id, pro_name, ava_qty, bt_id, aju_qty, batch, reason, total, pro_name_rowspan } = this.props.rowData[lastId];

            onChangeData(lastId, pro_id, pro_name, ava_qty, bt_id, aju_qty, batch, e.target.value, total, pro_name_rowspan);
        }
    }

    onHandleSubmit() {
        const { rowData, onsubmitData, adjType, dis_id, adjNumber, onAlert, onPageCleart } = this.props;

        if (adjNumber) {
            onsubmitData(rowData, adjType, dis_id, adjNumber);
            // setTimeout(onPageCleart, 1000);
        } else {
            onAlert('Type and Order No Required');
        }
    }

    render() {

        const { classes, adjType, adjNumber, dis_id, rowData } = this.props;

        return (
            <Layout sidebar={true}>
                <Toolbar>
                    <Typography variant="h5">Stock Adjustment</Typography>
                    <div className={classes.grow} />
                    <div className={classes.field}>
                        <AjaxDropdown required={true} onChange={this.onHandleDisChange} value={dis_id} link="user" label="Distributor" where={{ u_tp_id: 14 }} />
                    </div>
                    <div className={classes.grow} />
                    <div className={classes.field}>
                        <Select readOnly={true} required={true} className={classes.select} options={{ 1: 'adjusment', 2: 'writeoff' }} label={'Adjusment Type'} margin="dense" value={adjType} name={'adj_type'} onChange={this.handleAdjTypeChange} />
                    </div>
                    <div className={classes.field}>
                        <TextField readOnly={true} value={adjNumber} label="Adjusment Number" fullWidth={true} margin="dense" variant="outlined" />
                    </div>
                    <Button onClick={this.onHandleSubmit} className={classes.margin} variant="contained" color="primary">
                        <SaveIcon />
                        Save
                    </Button>
                </Toolbar><br />
                <Divider /><br /><br />
                {this.renderTable()}
            </Layout>
        )
    }

    renderTable() {
        const { classes, rowData, searched, adjType } = this.props;

        if (searched)
            return (
                <Table>
                    <TableHead>
                        <TableRow>
                            <TableCell
                                align='left'
                                padding='dense'
                                className={classes.darkCell}
                                style={{ width: 20 }}
                            >
                                #
                        </TableCell>
                            <TableCell
                                align='left'
                                padding='dense'
                                className={classes.darkCell}
                            >
                                Product
                        </TableCell>
                            <TableCell
                                align='left'
                                padding='dense'
                                className={classes.darkCell}
                            >
                                Batch
                        </TableCell>
                            <TableCell
                                align='left'
                                padding='dense'
                                className={classes.darkCell}
                            >
                                Available Stock
                        </TableCell>
                            <TableCell
                                align='left'
                                padding='dense'
                                className={classes.darkCell}
                            >
                                Adjust Qty
                        </TableCell>
                            <TableCell
                                align='left'
                                padding='dense'
                                className={classes.darkCell}
                            >
                                Adjustted Stock
                        </TableCell>
                            <TableCell
                                align='left'
                                padding='dense'
                                className={classes.darkCell}
                            >
                                Reason
                        </TableCell>
                        </TableRow>
                    </TableHead>
                    <TableBody className={classes.yellow}>
                        {Object.values(rowData).map((data, key) => (
                            console.log(data.pro_name_rowspan),

                            <TableRow key={key} className={adjType.value == 1 & data.aju_qty != 0 ?
                                parseInt(data.ava_qty) > parseInt(data.total) ? classes.red : classes.yellow
                                : adjType.value == 2 ?
                                    parseInt(data.ava_qty) < parseInt(data.total) ? classes.red : classes.yellow
                                    : null
                            }>
                                <TableCell
                                    align="left"
                                    padding="dense"
                                    style={{ width: 20 }}
                                    className={classes.lightCell}
                                >
                                    {key + 1}
                                </TableCell>
                                {
                                    data.pro_name_rowspan ?
                                        <TableCell
                                            align="left"
                                            padding="dense"
                                            className={classes.lightCell}
                                            rowSpan={data.pro_name_rowspan}
                                        >
                                            {data.pro_name}
                                        </TableCell> : null
                                }
                                {/* <TableCell
                                    align="left"
                                    padding="dense"
                                    className={classes.lightCell}
                                    // rowSpan={data.pro_name_rowspan}
                                >
                                    {data.pro_name}
                                </TableCell> */}
                                <TableCell
                                    align="left"
                                    padding="dense"
                                    className={classes.lightCell}
                                >
                                    {data.bt_id}
                                </TableCell>
                                <TableCell
                                    align="left"
                                    padding="dense"
                                    className={classes.lightCell}
                                >
                                    {data.ava_qty}
                                </TableCell>
                                <TableCell
                                    align="left"
                                    padding="dense"
                                    className={classes.lightCell}
                                >
                                    <TextField type='number' label="Qty" helperText={
                                        adjType.value == 1 & data.aju_qty != 0 ?
                                            parseInt(data.ava_qty) > parseInt(data.total) ? 'You can not enter qty less than Available stock qty in Ajusment type' : ''
                                            : adjType.value == 2 ?
                                                parseInt(data.ava_qty) < parseInt(data.total) ? 'You can not enter qty greater than Available stock qty in Write off type' : ''
                                                : null
                                    } fullWidth={true} margin="dense" variant="outlined"
                                        value={
                                            adjType.value == 1 ? data.aju_qty == 0 ? 0 : data.aju_qty : data.aju_qty == 0 ? data.aju_qty : data.aju_qty || ""
                                        } onChange={this.handleChangeQty(key)}
                                    />
                                </TableCell>
                                <TableCell
                                    align="left"
                                    padding="dense"
                                    className={classes.lightCell}
                                >
                                    {data.total}
                                </TableCell>
                                <TableCell
                                    align="left"
                                    padding="dense"
                                    className={classes.lightCell}
                                >
                                    <TextField type='text' margin="dense" variant="outlined" value={data.reason || ''} onChange={this.handleChangeReason(key)} />
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            );
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(styler(StockAdjusment));