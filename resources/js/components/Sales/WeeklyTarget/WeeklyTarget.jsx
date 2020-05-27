import React, { Component } from "react";
import { connect } from "react-redux";
import PropTypes from "prop-types";

import Paper from "@material-ui/core/Paper";
import { Link } from "react-router-dom";
import Grid from "@material-ui/core/Grid";
import Typography from "@material-ui/core/Typography";
import Divider from "@material-ui/core/Divider";
import withStyles from "@material-ui/core/styles/withStyles";
import TextField from "@material-ui/core/TextField";
import Toolbar from "@material-ui/core/Toolbar";
import Button from "@material-ui/core/Button";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import AppBar from "@material-ui/core/AppBar";
import Tabs from "@material-ui/core/Tabs";
import Tab from "@material-ui/core/Tab";
import Menu from '@material-ui/core/Menu';
import MenuItem from '@material-ui/core/MenuItem';
import MenuList from '@material-ui/core/MenuList';
import ListItemSecondaryAction from "@material-ui/core/ListItemSecondaryAction";
import CloudUploadIcon from "@material-ui/icons/CloudUpload";
import SaveIcon from "@material-ui/icons/Save";
import CloseIcon from "@material-ui/icons/Close";
import AddIcon from '@material-ui/icons/AddCircle';
import Save from '@material-ui/icons/Save';
import CancelIcon from '@material-ui/icons/Cancel';

import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import DatePicker from "../../CrudPage/Input/DatePicker";
import Layout from "../../App/Layout";
import ReactSvgPieChart from "react-svg-piechart";
import { SALES_REP_TYPE } from "../../../constants/config";
import { changeCheckError, pageClear, changeDrop, changeCheckType, errorMsg, changeRep, changeMonth, changePresantage, changeCheckTarget, fetchData, submitData, changePlusTargets, changeTargets, changeCalculation, dataQtyValueLoaded } from "../../../actions/Sales/WeeklyTarget";
import Form from "../../Medical/Report/Form";
import {
     alertDialog
} from "../../../../js/actions/Dialogs";

import moment from 'moment';

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
     grow: {
          flexGrow: 1
     },
     listItem: {
          background: theme.palette.common.white
     },
     listTextFieldAmount: {
          margin: 4,
          maxWidth: 150
     },
     listTextFieldQty: {
          margin: 4,
          maxWidth: 80
     },
     whiteFont: {
          color: theme.palette.white
     },
     inputColor: {
          width: '10px',
          height: '10px',
          // position: 'relative',
     }
});

const mapStateToProps = state => ({
     ...state.WeeklyTarget
});

const mapDispatchToProps = dispatch => ({
     onChangeRep: rep => dispatch(changeRep(rep)),
     onSearchData: (rep, month) => dispatch(fetchData(rep, month)),
     onChangeMonth: month => dispatch(changeMonth(month)),
     onChangeCalculation: calculation => dispatch(changeCalculation(calculation)),
     onChangeDataQtyValueLoaded: (totValue, totQty, totCurrent, ifCheckWeekly) => dispatch(dataQtyValueLoaded(totValue, totQty, totCurrent, ifCheckWeekly)),
     onChangePresantage: presantage => dispatch(changePresantage(presantage)),
     onChangeCheckTarget: target => dispatch(changeCheckTarget(target)),
     onChangeCheckType: (type, month_end) => dispatch(changeCheckType(type, month_end)),
     onChangeDrop: lastId => dispatch(changeDrop(lastId)),
     onChangePlusTargets: (start_week, end_week, week_presantage, value) => dispatch(changePlusTargets(start_week, end_week, week_presantage, value)),
     onChangeTargets: (lastId, start_week, end_week, week_presantage, value) => dispatch(changeTargets(lastId, start_week, end_week, week_presantage, value)),
     onSubmitData: (targetsData, rep, month, type) => dispatch(submitData(targetsData, rep, month, type)),
     onPageClear: () => dispatch(pageClear()),


     // errorMsg
     onAlertBox: (msg, type) => dispatch(errorMsg(msg, type)),
     checkErrorText: (condition) => dispatch(changeCheckError(condition))
});

class WeeklyTarget extends Component {


     constructor(props) {
          super(props);
          this.main_cal = 0;
          this.count = 0;

          this.handleChangeRep = this.handleChangeRep.bind(this);
          this.handleChangeMonth = this.handleChangeMonth.bind(this);
          this.onChangePlus = this.onChangePlus.bind(this);
          this.handleStartWeekChange = this.handleStartWeekChange.bind(this);
          this.handleEndWeekChange = this.handleEndWeekChange.bind(this);
          this.handleTargetChange = this.handleTargetChange.bind(this);
          this.handlePresantageChange = this.handlePresantageChange.bind(this);
     }

     handleChangeRep(rep) {
          const { onChangeRep, onSearchData, month, onPageClear } = this.props;
          onPageClear();
          onChangeRep(rep);
          onSearchData(rep, month);
     }

     handleChangeMonth(month) {
          const { onChangeMonth, onSearchData, rep, onPageClear } = this.props;
          // onPageClear();
          onChangeMonth(month);
          onSearchData(rep, month);
     }

     handleStartWeekChange(lastId) {
          return e => {
               const { onChangeTargets } = this.props;
               const { end_week, week_presantage, value } = this.props.targets[lastId];
               onChangeTargets(lastId, e.target.value, end_week, week_presantage, value);
          }
     }

     handleEndWeekChange(lastId) {
          return e => {
               const { onChangeTargets } = this.props;
               const { start_week, week_presantage, value } = this.props.targets[lastId];
               onChangeTargets(lastId, start_week, e.target.value, week_presantage, value);
          }
     }

     handleTargetChange(lastId) {
          return e => {
               const { onChangeTargets } = this.props;
               const { start_week, end_week, week_presantage } = this.props.targets[lastId];
               onChangeTargets(lastId, start_week, end_week, week_presantage, e.target.value);
          }
     }

     handlePresantageChange(lastId) {

          return e => {
               let cal = 0;
               let totValues = 0;
               let main_tot = 0;


               const { onChangeTargets, onChangeCalculation, targets, totValue, totCurrent, totQty, onChangeDataQtyValueLoaded, checkErrorText } = this.props;
               const { start_week, end_week, value } = this.props.targets[lastId];

               checkErrorText(false);
               cal = ((totValue * e.target.value) / 100);
               totValues = totValues + cal;
               onChangeCalculation(cal);

               main_tot = (totCurrent * e.target.value) / 100;
               onChangeTargets(lastId, start_week, end_week, e.target.value, main_tot);

               onChangeDataQtyValueLoaded(totCurrent, totQty, totCurrent);
          }
     }

     onChangePlus() {
          let start = 0;
          const { onChangePlusTargets, onAlertBox, start_week, end_week, week_presantage, value,lastId } = this.props;

          // if (lastId + 1 != 0) {
          //      if (this.props.targets[parseInt((lastId + 1) - 1)]['end_week'] == 0) {
          //           onAlertBox('Please Enter the Week ' + (lastId + 1) + ' End Date', 'error')
          //           return 0;
          //      } else {
          //           start = 1 + parseInt(this.props.targets[parseInt((lastId + 1) - 1)]['end_week']);
          //      }
          // }


          // onChangePlusTargets(start, end_week, week_presantage, value);
          onChangePlusTargets(start_week, end_week, week_presantage, value);
          this.count++;

     }

     onChangeSave(event) {
          const { targets, onSubmitData, rep, month, totCurrent, onAlertBox, checkErrorText, type, month_end,onPageClear } = this.props;
          console.logh(targets)
          const tar = this.getMainTargets();

          if (tar.valuePre > 100) {
               checkErrorText(true);
               onAlertBox('Monthly Main Target Exceeded', 'error');
          } else if (tar.valuePre < 100) {
               checkErrorText(true);
               onAlertBox('Weekly Targets Less than Monthly Main Target', 'error');
          } else if (tar.valueTarget == 0) {
               checkErrorText(true);
               onAlertBox('Target Value is Zero', 'error');
          } else {

               if (type) {
                    if ((month_end > this.props.targets[this.count - 1]['end_week'] || month_end < this.props.targets[this.count - 1]['end_week'])) {
                         onAlertBox('Target Month End at ' + month_end, 'error');
                    } else {
                         onSubmitData(targets, rep, month, type);
                         onPageClear();
                    }
               } else {
                    onSubmitData(targets, rep, month, type);
                    onPageClear();
               }
          }


     }

     onChangeCancel() {
          const { onPageClear } = this.props;
          onPageClear();
     }

     handleChangeDrop(id) {
          const { onChangeDrop } = this.props;
          return e => {
               onChangeDrop(id);
          }
     }

     getMainTargets() {
          const { targets } = this.props;

          let target = [];
          target = targets;
          let valueTarget = 0;
          let valuePre = 0;

          for (const id of Object.keys(target)) {
               const { week_presantage, value } = target[id];

               if (value) {
                    valueTarget += parseFloat(value);
                    valuePre += parseFloat(week_presantage);
               }
          }

          return { valueTarget, valuePre };
     }

     renderChart() {
          const { targets, classes } = this.props;

          const colors = [
               "#006887",
               "#bc0c55",
               "#ff846d",
               "#0090a0",
               "#fcba03"
          ];

          return [
               <Grid item md={2} key={22}>
                    <Paper className={classes.paper} style={{ backgroundColor: '#dbd7d7', height: 380, width: 300 }}>
                         <div style={{ height: 200, width: 250, marginLeft: 30 }}>
                              <ReactSvgPieChart

                                   data={

                                        Object.values(targets).map(
                                             ({ week_presantage }, key) => ({
                                                  title: 'Week ' + (key + 1) + ' (' + parseFloat(week_presantage) + '%)',
                                                  value: week_presantage
                                                       ? parseFloat(week_presantage)
                                                       : 0,
                                                  color: colors[key]
                                             })
                                        )
                                   }
                                   expandOnHover
                              />
                         </div>
                         <div style={{ marginTop: 28 }}>
                              {
                                   Object.values(targets).map((target) => {
                                        return [
                                             <div key={target.lastId}>
                                                  <div style={{ backgroundColor: colors[target.lastId] }} className={classes.inputColor}></div>Week {target.lastId + 1} ({parseInt(target.week_presantage)}%)
                                             </div>
                                        ];
                                   })
                              }
                         </div>
                    </Paper>
               </Grid >
          ];
     }

     render() {
          const {
               classes,
               rep,
               month,
               totQty,
               totCurrent
          } = this.props;

          return (
               <Layout sidebar>
                    <Grid container>
                         <Grid item md={8}>
                              <Paper className={classes.paper}>
                                   <Typography variant="h5" align="center">Weekly Target Allocations</Typography>
                                   <Divider />
                                   <Grid container>
                                        <Grid className={classes.padding} md={6} item>
                                             <AjaxDropdown value={rep} onChange={this.handleChangeRep} link="user" label="Sales Rep" where={{ u_tp_id: SALES_REP_TYPE }} />
                                        </Grid>
                                        <Grid className={classes.padding} md={6} item>
                                             <DatePicker value={month} onChange={this.handleChangeMonth} label="Month" />
                                        </Grid>
                                   </Grid>
                                   <Grid container>
                                        <Grid className={classes.padding} item md={6}>
                                             <TextField
                                                  label="Total Month Value"
                                                  variant="outlined"
                                                  margin="dense"
                                                  type="number"
                                                  step="0.01"
                                                  fullWidth
                                                  value={totCurrent == undefined ? 0 : totCurrent}
                                                  readOnly
                                             />
                                        </Grid>
                                        <Grid className={classes.padding} item md={6}>
                                             <TextField
                                                  label="Total Month Qty"
                                                  variant="outlined"
                                                  margin="dense"
                                                  type="number"
                                                  fullWidth
                                                  value={totQty == undefined ? 0 : totQty}
                                                  readOnly
                                             />
                                        </Grid>
                                   </Grid>
                              </Paper>
                         </Grid>
                    </Grid>
                    {this.renderWeeklyTarget()}
               </Layout>
          );
     }

     renderWeeklyTarget() {
          const {
               classes, targets, type
          } = this.props;

          const tar = this.getMainTargets();

          return [
               <Grid container key={23}>
                    <Grid item md={8}>
                         <Paper className={classes.paper} style={{ backgroundColor: '#dbd7d7', height: 380 }}>
                              <AppBar position="static">
                                   <Toolbar variant="dense">
                                        <Grid item md={2}>
                                             <Button variant="contained" color="primary" className={classes.button} onClick={this.onChangePlus} >
                                                  <AddIcon />
                                             </Button>
                                        </Grid>
                                        <Grid item md={2}>
                                             <Button type="submit" variant="contained" color="secondary" className={classes.button} onClick={this.onChangeSave.bind(this)}>
                                                  <Save />
                                             </Button>
                                        </Grid>
                                        <Grid item md={2}>
                                             <Button type="submit" variant="contained" className={classes.button} onClick={this.onChangeCancel.bind(this)}>
                                                  <CancelIcon />
                                             </Button>
                                        </Grid>
                                        <Grid className={classes.padding} item md={4}>
                                        </Grid>
                                        <Grid className={classes.padding} item md={2}>
                                             <TextField
                                                  error
                                                  id="tot-value"
                                                  label="%"
                                                  variant="outlined"
                                                  margin="dense"
                                                  type="number"
                                                  step="0.01"
                                                  fullWidth
                                                  // value={tar.valuePre.toFixed(2)}
                                                  value={tar.valuePre}
                                                  InputProps={{
                                                       readOnly: true,
                                                  }}
                                             />
                                        </Grid>
                                        <Grid className={classes.padding} item md={3}>
                                             <TextField
                                                  error
                                                  id="tot-value"
                                                  label="Target"
                                                  variant="outlined"
                                                  margin="dense"
                                                  type="number"
                                                  step="0.01"
                                                  fullWidth
                                                  // value={tar.valueTarget.toFixed(2)}
                                                  value={tar.valueTarget}
                                                  InputProps={{
                                                       readOnly: true,
                                                  }}
                                             />
                                        </Grid>
                                   </Toolbar>
                              </AppBar>
                              <Grid container>
                                   {
                                        this.renderAddtionalFields(targets)
                                   }
                              </Grid>
                         </Paper>
                    </Grid>
                    {this.renderChart()}
                    <Grid item md={1}>
                    </Grid>
               </Grid>
          ];
     }

     renderAddtionalFields(targets) {
          const {
               classes,
               error,
               type
          } = this.props;

          return Object.values(targets).map((target, index) => {

               return [
                    <Grid container key={target}>
                         <Grid className={classes.padding} item md={2}>
                              <TextField
                                   label={"Week " + (index + 1) + " Start"}
                                   variant="outlined"
                                   margin="dense"
                                   type="number"
                                   step="0.01"
                                   fullWidth
                                   value={target.start_week}
                                   onChange={this.handleStartWeekChange(target.lastId)}
                              />
                         </Grid>
                         <Grid className={classes.padding} item md={2}>
                              <TextField
                                   label={"Week " + (index + 1) + " End"}
                                   variant="outlined"
                                   margin="dense"
                                   type="number"
                                   fullWidth
                                   value={target.end_week}
                                   onChange={this.handleEndWeekChange(target.lastId)}
                              />
                         </Grid>
                         <Grid className={classes.padding} item md={4}>
                              <TextField
                                   label="Week Target"
                                   variant="outlined"
                                   margin="dense"
                                   type="number"
                                   fullWidth
                                   value={target.value}
                                   onChange={this.handleTargetChange(target.lastId)}
                                   InputProps={{
                                        readOnly: true,
                                        error: error
                                   }}
                              />
                         </Grid>
                         <Grid className={classes.padding} item md={2}>
                              <TextField
                                   label="%"
                                   variant="outlined"
                                   margin="dense"
                                   type="number"
                                   fullWidth
                                   value={target.week_presantage}
                                   onChange={this.handlePresantageChange(target.lastId)}
                                   InputProps={{
                                        readOnly: false,
                                        error: error
                                   }}
                              />
                         </Grid>
                         <Grid className={classes.padding} item md={1} style={{ display: '' }}>
                              <Button variant="contained" color="secondary" className={classes.button} onClick={this.handleChangeDrop(target.lastId)} disabled={!type}>
                                   <CloseIcon />
                              </Button>
                         </Grid>
                    </Grid>
               ];
          })
     }
}


WeeklyTarget.propTypes = {
     classes: PropTypes.shape({
          paper: PropTypes.string,
          padding: PropTypes.string,
          darkGrey: PropTypes.string,
          button: PropTypes.string,
          grow: PropTypes.string,
          listItem: PropTypes.string,
     }),
     onChangeRep: PropTypes.func,
     onSearchData: PropTypes.func,
     onChangeMonth: PropTypes.func,
     onChangePresantage: PropTypes.func,
     onChangePlusTargets: PropTypes.func,
     onChangeTargets: PropTypes.func,
     onChangeCalculation: PropTypes.func,
     onChangeDataQtyValueLoaded: PropTypes.func,
     onChangeCheckTarget: PropTypes.func,
     checkErrorText: PropTypes.func,
     onAlertBox: PropTypes.func,
     onPageClear: PropTypes.func,
     totQty: PropTypes.oneOfType([
          PropTypes.string,
          PropTypes.number
     ]),
     totValue: PropTypes.oneOfType([
          PropTypes.string,
          PropTypes.number
     ]),
     ifCheckWeekly: PropTypes.array,
     onChangeCheckType: PropTypes.func,
     onChangeDrop: PropTypes.func

}

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(WeeklyTarget));
