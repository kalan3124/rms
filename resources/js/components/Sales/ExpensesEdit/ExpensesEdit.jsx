import React, { Component } from "react";
import { connect } from "react-redux";
import PropTypes from "prop-types";
import withStyles from "@material-ui/core/styles/withStyles";
import Paper from "@material-ui/core/Paper";
import { Link } from "react-router-dom";
import Grid from "@material-ui/core/Grid";
import Typography from "@material-ui/core/Typography";
import Divider from "@material-ui/core/Divider";
import TextField from "@material-ui/core/TextField";
import Toolbar from "@material-ui/core/Toolbar";
import Button from "@material-ui/core/Button";
import Table from "@material-ui/core/Table";
import AppBar from "@material-ui/core/AppBar";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import DatePicker from "../../CrudPage/Input/DatePicker";
import Layout from "../../App/Layout";
import TableHead from "@material-ui/core/TableHead";
import TableCell from "@material-ui/core/TableCell";
import TableRow from "@material-ui/core/TableRow";
import TableBody from "@material-ui/core/TableBody";
import Save from '@material-ui/icons/Save';
import CancelIcon from '@material-ui/icons/Cancel';
import Checkbox from '@material-ui/core/Checkbox';
import Modal from "@material-ui/core/Modal";

import { SALES_REP_TYPE, AREA_SALES_MANAGER_TYPE } from "../../../constants/config";

import { changeRep, changeMonth, fetchData, changedPlusData, changedData, submitData, openModal, changeValue, saveAsmExp, fetchRoll } from "../../../actions/Sales/ExpensesEdit";

import ExpensesTableheader from "./ExpensesTableheader";

const styles = theme => ({
     paper: {
          padding: theme.spacing.unit,
          margin: theme.spacing.unit
     },
     padding: {
          padding: theme.spacing.unit
     },
     darkGrey: {
          background: theme.palette.grey[400],
          height: '48vh',
          overflowY: "auto",
          border: "solid 1px " + theme.palette.grey[400],
          borderTop: "solid 0px"
     },
     button: {
          margin: theme.spacing.unit
     },
     lightCell: {
          border: '1px solid ' + theme.palette.grey[500]
     },
     backdrop: {
          right: 24,
          background: "unset"
     },
     modal: {
          backgroundColor: "rgba(0, 0, 0, 0.5)",
          paddingBottom: 40,
          overflow: "auto"
     },
     paperModal: {
          width: '40vw',
          minWidth: '400px',
          marginLeft: '30vw',
          marginTop: '40px',
          padding: theme.spacing.unit * 2
     }
});

const mapStateToProps = state => ({
     ...state.ExpensesEdit
});

const mapDispatchToProps = dispatch => ({
     onChangeRep: rep => dispatch(changeRep(rep)),
     onChangeMonth: month => dispatch(changeMonth(month)),
     onSearchData: (rep, month) => dispatch(fetchData(rep, month)),
     onChangedPlusData: (date, bataType, stationery, parking, user, remark, app) => dispatch(changedPlusData(date, bataType, stationery, parking, user, remark, app)),
     onChangedData: (lastId, date, bataType, stationery, parking, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage) => dispatch(changedData(lastId, date, bataType, stationery, parking, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage)),
     onSubmitData: expenses => dispatch(submitData(expenses)),
     onOpenModal: open => dispatch(openModal(open)),
     onChangeValue: (name, value) => dispatch(changeValue(name, value)),
     onSaveAsmExp: values => dispatch(saveAsmExp(values)),
     onRoll: () => dispatch(fetchRoll())
});

class ExpensesEdit extends Component {
     constructor(props) {
          super(props);

          this.handleChangeRep = this.handleChangeRep.bind(this);
          this.handleChangeMonth = this.handleChangeMonth.bind(this);
          this.onHanleChangeDate = this.onHanleChangeDate.bind(this);
          this.onHanleChangeBataType = this.onHanleChangeBataType.bind(this);
          this.onHanleChangeStationery = this.onHanleChangeStationery.bind(this);
          this.onHanleChangeParking = this.onHanleChangeParking.bind(this);
          this.onHanleChangeRemark = this.onHanleChangeRemark.bind(this);
          this.onHanleChangeUser = this.onHanleChangeUser.bind(this);
          this.onHanleChangeApp = this.onHanleChangeApp.bind(this);
          this.onHanleChangeMileage = this.onHanleChangeMileage.bind(this);
          this.handleChangeApproval = this.handleChangeApproval.bind(this);
          this.onHanleChangeActualMileage = this.onHanleChangeActualMileage.bind(this);
          this.onHanleChangeMileageAmount = this.onHanleChangeMileageAmount.bind(this);
          this.handleOpenModal = this.handleOpenModal.bind(this);

          // ASM CRUD MODAL
          this.changeFormValue = this.changeFormValue.bind(this);
          this.changeFormValueText = this.changeFormValueText.bind(this);
          this.handleSubmitForm = this.handleSubmitForm.bind(this);
     }

     componentDidMount() {
          const { onRoll } = this.props;
          onRoll();
     }

     handleOpenModal() {
          const { onOpenModal } = this.props;
          onOpenModal(true);
     }

     handleModalClose() {
          const { onOpenModal } = this.props;
          onOpenModal(false);
     }

     PlusIndexData() {
          const { rowData, onChangedPlusData, date, bataType, stationery, parking, user, remark, app } = this.props;
          onChangedPlusData(date, bataType, stationery, parking, user, remark, app);
     }

     handleChangeRep(rep) {
          const { onChangeRep, onSearchData, month } = this.props;
          onChangeRep(rep);
          onSearchData(rep, month);
     }

     handleChangeMonth(month) {
          const { onChangeMonth, onSearchData, rep } = this.props;
          onChangeMonth(month);
          onSearchData(rep, month);
     }

     onHanleChangeBataType(lastId) {
          return e => {
               const { onChangedData } = this.props;
               const { date, bataType, stationery, parking, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage } = this.props.rowData[lastId];
               onChangedData(lastId, date, e, stationery, parking, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage);
          }
     }

     onHanleChangeDate(lastId) {
          return e => {
               const { onChangedData } = this.props;
               const { date, bataType, stationery, parking, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage } = this.props.rowData[lastId];
               onChangedData(lastId, e.target.value, bataType, stationery, parking, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage);
          }
     }

     onHanleChangeStationery(lastId) {
          return e => {
               const { onChangedData } = this.props;
               const { date, bataType, stationery, parking, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage } = this.props.rowData[lastId];
               onChangedData(lastId, date, bataType, e.target.value, parking, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage);
          }
     }

     onHanleChangeParking(lastId) {
          return e => {
               const { onChangedData } = this.props;
               const { date, bataType, stationery, parking, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage } = this.props.rowData[lastId];
               onChangedData(lastId, date, bataType, stationery, e.target.value, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage);
          }
     }

     onHanleChangeRemark(lastId) {
          return e => {
               const { onChangedData } = this.props;
               const { date, bataType, stationery, parking, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage } = this.props.rowData[lastId];
               onChangedData(lastId, date, bataType, stationery, parking, user, e.target.value, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage);
          }
     }

     onHanleChangeUser(lastId) {
          return e => {
               const { onChangedData } = this.props;
               const { date, bataType, stationery, parking, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage } = this.props.rowData[lastId];
               onChangedData(lastId, date, bataType, stationery, parking, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage);
          }
     }

     onHanleChangeApp(lastId) {
          return e => {
               const { onChangedData } = this.props;
               const { date, bataType, stationery, parking, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage } = this.props.rowData[lastId];
               onChangedData(lastId, date, bataType, stationery, parking, user, remark, e.target.value, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage);
          }
     }

     onHanleChangeMileage(lastId) {
          return e => {
               const { onChangedData } = this.props;
               const { date, bataType, stationery, parking, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage } = this.props.rowData[lastId];
               onChangedData(lastId, date, bataType, stationery, parking, user, remark, app, e.target.value, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage);
          }
     }

     handleChangeApproval(lastId) {
          return (e, value) => {
               const { onChangedData } = this.props;
               const { date, bataType, stationery, parking, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage } = this.props.rowData[lastId];
               onChangedData(lastId, date, bataType, stationery, parking, user, remark, app, mileage, exp_id, value ? true : false, actual_mileage, mileage_amount, vht_rate, def_actual_mileage);
          }
     }

     onHanleChangeActualMileage(lastId) {
          return (e, value) => {
               const { onChangedData } = this.props;
               const { date, bataType, stationery, parking, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage } = this.props.rowData[lastId];

               let total = e.target.value * vht_rate;
               onChangedData(lastId, date, bataType, stationery, parking, user, remark, app, mileage, exp_id, status, e.target.value, total, vht_rate, def_actual_mileage);
          }
     }

     onHanleChangeMileageAmount(lastId) {
          return (e, value) => {
               const { onChangedData } = this.props;
               const { date, bataType, stationery, parking, user, remark, app, mileage, exp_id, status, actual_mileage, mileage_amount, vht_rate, def_actual_mileage } = this.props.rowData[lastId];
               onChangedData(lastId, date, bataType, stationery, parking, user, remark, app, mileage, exp_id, status, actual_mileage, e.target.value, vht_rate, def_actual_mileage);
          }
     }


     changeFormValue(name) {
          const { onChangeValue } = this.props;
          return e => {
               onChangeValue(name, e);
          }
     }

     changeFormValueText(name) {
          const { onChangeValue } = this.props;
          return e => {
               onChangeValue(name, e.target.value);
          }
     }

     handleSubmitForm() {
          const { onSaveAsmExp, values } = this.props;

          onSaveAsmExp(values);
     }

     onChangeSubmit() {
          const { rowData, onSubmitData } = this.props;
          onSubmitData(rowData);
     }

     render() {
          const { classes, rep, month, searched, roll } = this.props;

          return (
               <Layout sidebar>
                    <Paper className={classes.padding} >
                         <Typography variant="h5" align="center">Expenses Edit Report</Typography>
                         <Divider />
                         <Grid container>
                              <Grid className={classes.padding} md={6} item>
                                   <AjaxDropdown value={rep} onChange={this.handleChangeRep} link="user" label="Sales Rep" where={{ u_tp_id: SALES_REP_TYPE }} />
                              </Grid>
                              <Grid className={classes.padding} md={6} item>
                                   <DatePicker value={month} onChange={this.handleChangeMonth} label="Month" />
                              </Grid>
                              {
                                   roll == AREA_SALES_MANAGER_TYPE ?
                                        <Button
                                             className={classes.button}
                                             variant="contained"
                                             color="primary"
                                             onClick={this.handleOpenModal}
                                        >
                                             Add Expenses
                                        </Button>
                                        :null
                              }
                         </Grid>
                    </Paper><br />
                    {
                         searched ?
                              <Paper className={classes.padding} style={{ width: 2600 }} >
                                   <AppBar position="static">
                                        <Grid container>
                                             <Grid item md={2}>
                                                  <Button type="submit" variant="contained" color="secondary" onClick={this.onChangeSubmit.bind(this)} className={classes.button}>
                                                       <Save /> Update
                                                  </Button>
                                             </Grid>
                                             <Grid item md={2}>
                                                  <Button type="submit" variant="contained" className={classes.button}>
                                                       <CancelIcon /> Cancel
                                                  </Button>
                                             </Grid>
                                        </Grid>
                                   </AppBar><br />
                                   {this.renderTable()}
                              </Paper>
                              : null
                    }
                    {this.renderModal()}
               </Layout>
          );
     }

     renderTable() {
          const { rowData, classes } = this.props;

          return (
               <Table>
                    <ExpensesTableheader />
                    <TableBody>
                         {
                              Object.values(rowData).map((row, index) => {

                                   return [
                                        <TableRow key={index}>
                                             <TableCell className={classes.lightCell}>
                                                  <TextField
                                                       type="text"
                                                       value={row.date}
                                                       onChange={this.onHanleChangeDate(index)}
                                                       InputProps={{
                                                            readOnly: true,
                                                       }}
                                                       style={{ width: 100 }}
                                                  />
                                             </TableCell>
                                             <TableCell className={classes.lightCell}>
                                                  <TextField
                                                       type="text"
                                                       value={row.dayType}
                                                       // onChange={this.onHanleChangeDate(index)}
                                                       InputProps={{
                                                            readOnly: true,
                                                       }}
                                                       style={{ width: 150 }}
                                                  />
                                             </TableCell>
                                             <TableCell className={classes.lightCell}>
                                                  <TextField
                                                       type="text"
                                                       value={row.route}
                                                       // onChange={this.onHanleChangeDate(index)}
                                                       InputProps={{
                                                            readOnly: true,
                                                       }}
                                                       style={{ width: 200 }}
                                                  />
                                             </TableCell>
                                             <TableCell className={classes.lightCell}>
                                                  <AjaxDropdown value={row.bataType} onChange={this.onHanleChangeBataType(index)} where={{ bt_type: 3 }} link="bataType" style={{ width: 150 }} />
                                             </TableCell>
                                             <TableCell className={classes.lightCell}>
                                                  <TextField
                                                       type="number"
                                                       step="0.01"
                                                       value={row.stationery}
                                                       onChange={this.onHanleChangeStationery(index)}
                                                  />
                                             </TableCell>
                                             <TableCell className={classes.lightCell}>
                                                  <TextField
                                                       type="number"
                                                       value={row.def_actual_mileage != 0 ? row.def_actual_mileage : 0}
                                                       // onChange={this.onHanleChangeActualMileage(index)}
                                                       InputProps={{
                                                            readOnly: true,
                                                       }}
                                                       style={{ width: 100 }}
                                                  />
                                             </TableCell>
                                             <TableCell className={classes.lightCell}>
                                                  <TextField
                                                       type="number"
                                                       value={row.actual_mileage != 0 ? row.actual_mileage : 0}
                                                       onChange={this.onHanleChangeActualMileage(index)}
                                                       InputProps={{
                                                            readOnly: false,
                                                       }}
                                                       style={{ width: 100 }}
                                                  />
                                             </TableCell>
                                             <TableCell className={classes.lightCell}>
                                                  <TextField
                                                       type="number"
                                                       value={row.mileage_amount}
                                                       onChange={this.onHanleChangeMileageAmount(index)}
                                                       InputProps={{
                                                            readOnly: true,
                                                       }}
                                                       style={{ width: 100 }}
                                                  />
                                             </TableCell>
                                             <TableCell className={classes.lightCell}>
                                                  <TextField
                                                       type="number"
                                                       step="0.01"
                                                       value={row.parking}
                                                       onChange={this.onHanleChangeParking(index)}
                                                       style={{ width: 100 }}
                                                  />
                                             </TableCell>
                                             <TableCell className={classes.lightCell}>
                                                  <TextField
                                                       type="number"
                                                       step="0.01"
                                                       value={row.mileage}
                                                       onChange={this.onHanleChangeMileage(index)}
                                                       style={{ width: 100 }}
                                                  />
                                             </TableCell>
                                             <TableCell className={classes.lightCell}>
                                                  <TextField
                                                       type="text"
                                                       step="0.01"
                                                       value={row.remark}
                                                       onChange={this.onHanleChangeRemark(index)}
                                                       style={{ width: 100 }}
                                                  />
                                             </TableCell>
                                             <TableCell className={classes.lightCell}>
                                                  <TextField
                                                       type="text"
                                                       value={row.app != undefined ? row.app : '-'}
                                                       onChange={this.onHanleChangeApp(index)}
                                                       InputProps={{
                                                            readOnly: true,
                                                       }}
                                                       style={{ width: 50 }}
                                                  />
                                             </TableCell>
                                             <TableCell className={classes.lightCell}>
                                                  <Checkbox
                                                       checked={row.status}
                                                       onChange={this.handleChangeApproval(index)}
                                                       inputProps={{
                                                            'aria-label': 'primary checkbox',
                                                       }}
                                                  />
                                             </TableCell>
                                        </TableRow>
                                   ];
                              })
                         }
                    </TableBody>
               </Table>
          );
     }

     renderModal() {

          const {
               classes,
               open,
               values
          } = this.props;

          return (
               <Modal
                    aria-labelledby="simple-modal-title"
                    aria-describedby="simple-modal-description"
                    open={open}
                    onClose={this.handleModalClose.bind(this)}
                    BackdropProps={{
                         className: classes.backdrop
                    }}
                    className={classes.modal}
               >
                    <Paper className={classes.paperModal}>
                         <Typography variant="h6" align="center">ASM Expenses</Typography>
                         <Divider />
                         <Grid container>
                              <Grid className={classes.padding} item md={4}>
                                   <AjaxDropdown required={true} where={{ bt_type: 3 }} value={values.bata} label="Bata Type" onChange={this.changeFormValue('bata')} link="bataType" name="bata" />
                              </Grid>
                              <Grid className={classes.padding} item md={4}>
                                   <TextField label="Stationery" value={values.stationary || ""} onChange={this.changeFormValueText('stationary')} name="stationary" variant="outlined" margin="dense" />
                              </Grid>
                              <Grid className={classes.padding} item md={4}>
                                   <TextField label="Parking" value={values.parking || ""} onChange={this.changeFormValueText('parking')} name="parking" variant="outlined" margin="dense" />
                              </Grid>
                              <Grid className={classes.padding} item md={4}>
                                   <TextField label="Mileage" value={values.mileage || ""} onChange={this.changeFormValueText('mileage')} name="mileage" variant="outlined" margin="dense" />
                              </Grid>
                              <Grid className={classes.padding} item md={4}>
                                   <TextField label="Remark" value={values.remark || ""} onChange={this.changeFormValueText('remark')} name="remark" variant="outlined" margin="dense" />
                              </Grid>
                         </Grid>
                         <Divider />
                         <Grid item md={10}>
                              <Button onClick={this.handleSubmitForm} className={classes.button} variant="contained" color="secondary" >Save</Button>
                              <Button onClick={this.handleModalClose.bind(this)} className={classes.button} variant="contained" color="secondary" >Cancel</Button>
                         </Grid>
                    </Paper>
               </Modal>
          );
     }
}

ExpensesEdit.propTypes = {
     classes: PropTypes.shape({
          paper: PropTypes.string,
          padding: PropTypes.string,
          darkGrey: PropTypes.string,
          button: PropTypes.string,
          grow: PropTypes.string,
          listItem: PropTypes.string,
     }),
     onOpenModal: PropTypes.func,
     onChangeRep: PropTypes.func,
     onChangeMonth: PropTypes.func,
     onSearchData: PropTypes.func,
     onChangedPlusData: PropTypes.func,
     onChangedData: PropTypes.func,
     onSubmitData: PropTypes.func
}

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(ExpensesEdit));