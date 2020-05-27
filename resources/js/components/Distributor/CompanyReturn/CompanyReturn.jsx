import React,{Component} from "react";
import Layout from "../../App/Layout";
import withStyles from "@material-ui/core/styles/withStyles";
import Toolbar from "@material-ui/core/Toolbar";
import Typography from "@material-ui/core/Typography";
import TextField from "@material-ui/core/TextField";
import Button from "@material-ui/core/Button";
import Divider from "@material-ui/core/Divider";
import Table from "@material-ui/core/Table";
import TableHead from "@material-ui/core/TableHead";
import TableBody from "@material-ui/core/TableBody";
import TableRow from "@material-ui/core/TableRow";
import {
 changeNumber,
 fetchInfo,
 changeReason,
 changeSalable,
 changeRemark,
 clearPage,
 save,
 changeQty
} from "../../../actions/Distributor/CompanyReturn";
import CompanyReturnLine from "./CompanyReturnLine";
import { connect } from "react-redux";
import { TableCell } from "@material-ui/core";


const styler = withStyles(theme=>({
    grow: {
        flexGrow: 1
    },
    margin: {
        margin: theme.spacing.unit
    },
    darkCell: {
        background: theme.palette.grey[700],
        color: theme.palette.common.white
    }
}))

const mapStateToProps = state =>({
    ...state.CompanyReturn
});

const mapDispatchToProps = dispatch =>({
    onChangeNumber: grnNumber => dispatch(changeNumber(grnNumber)),
    onSearch: grnNumber => dispatch(fetchInfo(grnNumber)),
    onChangeQty: (id,qty)=>dispatch(changeQty(id,qty)),
    onChangeReason: (id,reason)=>dispatch(changeReason(id, reason)),
    onChangeSalable: (id,salable)=>dispatch(changeSalable(id,salable)),
    onChangeRemark: (remark)=>dispatch(changeRemark(remark)),
    onClear: ()=>dispatch(clearPage()),
    onSave: (grnNumber, lines, remark)=>dispatch(save(grnNumber, lines, remark))
})

class CompanyReturn extends Component {

    constructor(props){
        super(props);

        this.handleChangeNumber = this.handleChangeNumber.bind(this);
        this.handleClickSearchButton = this.handleClickSearchButton.bind(this);
        this.handleClickSaveButton = this.handleClickSaveButton.bind(this);
        this.handleChangeRemark = this.handleChangeRemark.bind(this);
    }

    handleChangeNumber(e){
        this.props.onChangeNumber(e.target.value)
    }

    handleClickSearchButton(e){
        const {grnNumber, onSearch} = this.props;

        onSearch(grnNumber);
    }

    handleClickSaveButton(e){
        const {onSave, grnNumber, lines, remark} = this.props;

        onSave(grnNumber,lines,remark);
    }

    handleChangeRemark(e){
        const {onChangeRemark} = this.props;

        onChangeRemark(e.target.value);
    }


    render(){

        const {
            classes,
            grnNumber,
            onClear,
            lines,
            onChangeQty,
            onChangeReason,
            onChangeSalable,
            returnNumber,
            remark
        } = this.props;

        return (
            <Layout sidebar={true} >
                <Toolbar
                    variant="dense"
                >
                    <Typography variant="h5">Company Return</Typography>
                    <div className={classes.grow}/>
                    <TextField
                        variant="outlined"
                        label="GRN Number"
                        className={classes.margin}
                        margin="dense"
                        value={grnNumber}
                        onChange={this.handleChangeNumber}
                    />
                    <Button
                        variant="contained"
                        color="default"
                        className={classes.margin}
                        onClick={this.handleClickSearchButton}
                    >
                        Search
                    </Button>
                    <Button
                        variant="contained"
                        color="secondary"
                        className={classes.margin}
                        onClick={onClear}
                    >
                        Cancel
                    </Button>
                    <Button
                        variant="contained"
                        color="primary"
                        className={classes.margin}
                        onClick={this.handleClickSaveButton}
                    >
                        Save
                    </Button>
                </Toolbar>
                <Divider />
                {Object.keys(lines).length?[
                <Table key={1} >
                    <TableHead>
                        <TableRow>
                            <TableCell className={classes.darkCell} >
                                #
                            </TableCell>
                            <TableCell className={classes.darkCell} >
                                Product
                            </TableCell>
                            <TableCell className={classes.darkCell} >
                                Batch
                            </TableCell>
                            <TableCell className={classes.darkCell} >
                                Price
                            </TableCell>
                            <TableCell className={classes.darkCell} >
                                Expire
                            </TableCell>
                            <TableCell className={classes.darkCell} >
                                Reason
                            </TableCell>
                            <TableCell className={classes.darkCell} >
                                Salable
                            </TableCell>
                            <TableCell className={classes.darkCell} >
                                Received Qty
                            </TableCell>
                            <TableCell className={classes.darkCell} >
                                Qty
                            </TableCell>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {Object.values(lines).map((line,key)=>(
                           <CompanyReturnLine
                                {...line}
                                index={key+1}
                                key={key}
                                onChangeReason={onChangeReason}
                                onChangeQty={onChangeQty}
                                onChangeSalable={onChangeSalable}
                           />
                        ))}
                    </TableBody>
                </Table>,
                <Toolbar
                    variant="dense"
                    key={2}
                >
                    <TextField
                        label="Return Number"
                        variant="outlined"
                        margin="dense"
                        value={returnNumber}
                    />
                    <TextField
                        label="Please Type Your Remark"
                        variant="outlined"
                        margin="dense"
                        onChange={this.handleChangeRemark}
                        value={remark}
                    />

                    <div className={classes.grow} />
                    <TextField
                        label="Total"
                        variant="outlined"
                        margin="dense"
                        value={Object.values(lines).map(line => line.qty * line.price).reduce((a, b) => a + b, 0).toFixed(2)}
                    />
                </Toolbar>
                ]
                :
                <Typography variant="body1" align="center">Please search for a GRN</Typography>
                }
            </Layout>
        )
    }
}

export default connect(mapStateToProps,mapDispatchToProps) (styler (CompanyReturn));
