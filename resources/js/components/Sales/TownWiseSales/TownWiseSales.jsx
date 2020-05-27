import React, { Component } from "react";
import { connect } from 'react-redux';
import PropTypes from "prop-types";
import moment from 'moment';

import Paper from "@material-ui/core/Paper";
import Typography from "@material-ui/core/Typography";
import withStyles from "@material-ui/core/styles/withStyles";
import Divider from "@material-ui/core/Divider";
import Grid from "@material-ui/core/Grid";
import Button from "@material-ui/core/Button";
import Table from "@material-ui/core/Table";
import TableBody from "@material-ui/core/TableBody";
import TableRow from "@material-ui/core/TableRow";
import TableCell from "@material-ui/core/TableCell";
import Toolbar from "@material-ui/core/Toolbar"
import SearchIcon from "@material-ui/icons/Search";
import blue from '@material-ui/core/colors/blue';
import Fab from "@material-ui/core/Fab";
import TableIcon from "@material-ui/icons/TableChart";
import agent from "../../../agent";

import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import DatePicker from "../../CrudPage/Input/DatePicker";
import Layout from "../../App/Layout";
import { changeValue, fetchData } from '../../../actions/Sales/TownWiseSales';
import SegmentTableHeader from './SegmentTableHeader';
import TownWiseTableHeader from './TownWiseTableHeader';
import { SALES_REP_TYPE } from "../../../constants/config";
import RouteWiseTableHeader from "./RouteWiseTableHeader";
import CustomerTableheader from "./CustomerTableheader";
import { APP_URL } from "../../../constants/config";

const styles = theme => ({
     padding: {
          padding: theme.spacing.unit
     },
     button: {
          marginTop: theme.spacing.unit,
          float: "right"
     },
     darkCell: {
          background: theme.palette.grey[600],
          color: theme.palette.common.white,
          border: '1px solid #fff'
     },
     table: {
          marginTop: theme.spacing.unit
     },
     lightCell: {
          border: '1px solid ' + theme.palette.grey[500]
     },
     excelRow: {
          display: 'none'
     },
     summaryTable: {
          width: '150px'
     },
     excelTitle: {
          fontWeight: 'bold',
          display: 'none'
     },
     newCell: {
          background: theme.palette.grey[300],
          color: theme.palette.common.black,
          border: '1px solid ' + theme.palette.grey[500]
     },
     totalCell: {
          background: '#9ce1ff',
          color: theme.palette.common.black,
          border: '1px solid ' + theme.palette.grey[500]
     }
});

const mapStateToProps = state => ({
     ...state.TownWiseSales,
     ...state.App
});

const mapDispatchToProps = dispatch => ({
     onChangeValue: (name, value) => dispatch(changeValue(name, value)),
     onSubmit: (values) => dispatch(fetchData(values))
})

class TownWiseSales extends Component {
     constructor(props) {
          super(props);
          this.handleSubmitForm = this.handleSubmitForm.bind(this);
          this.handleClickDownloadXLSX = this.handleClickDownloadXLSX.bind(this);
     }

     handleSubmitForm() {
          const { onSubmit, values } = this.props;
          onSubmit(values);
     }

     changeFormValue(name) {
          const { onChangeValue } = this.props;
          return value => {
               onChangeValue(name, value);
          }
     }

     handleClickDownloadXLSX() {
          const { values } = this.props;
  
          agent.TownWiseSales.excelSave(values).then(({ file }) => {
              window.open(APP_URL + 'storage/xlsx/' + file);
          })
      }

     render() {
          const { classes, values } = this.props;
          return (
               <Layout sidebar>
                    <Paper className={classes.padding} >
                         <Typography align="center" variant="h6">Town Wise Sales Report</Typography>
                         <Divider />
                         <Toolbar>
                              <Grid container>
                                   <Grid className={classes.padding} item md={2}>
                                        <div className={classes.dropdownWrapper} >
                                             <AjaxDropdown required={true} link="user" label="User" value={values.user} where={{ u_tp_id: SALES_REP_TYPE }} onChange={this.changeFormValue('user')} name="user" />
                                        </div>
                                   </Grid>
                                   <Grid className={classes.padding} item md={3}>
                                        <div className={classes.dropdownWrapper} >
                                             <AjaxDropdown link="route" label="Route" value={values.route} onChange={this.changeFormValue('route')} name="route" />
                                        </div>
                                   </Grid>
                                   <Grid className={classes.padding} item md={2}>
                                        <div className={classes.dropdownWrapper} >
                                             <AjaxDropdown link="sub_town" label="Town" value={values.sub_town} onChange={this.changeFormValue('sub_town')} name="sub_town" />
                                        </div>
                                   </Grid>
                                   <Grid className={classes.padding} item md={3}>
                                        <div className={classes.dropdownWrapper} >
                                             <AjaxDropdown link="chemist" label="Chemist" value={values.chem_id} onChange={this.changeFormValue('chem_id')} name="chem_id" />
                                        </div>
                                   </Grid>
                                   <Grid className={classes.padding} item md={2}>
                                        <div className={classes.dropdownWrapper} >
                                             <DatePicker value={values.month} onChange={this.changeFormValue('month')} label="Month" />
                                        </div>
                                   </Grid>
                              </Grid>
                              <Divider />
                              <Button className={classes.searchButton} onClick={this.handleSubmitForm} variant="contained" color="primary">
                                   <SearchIcon />
                                   Search
                              </Button>
                         </Toolbar>
                         <Divider />
                         <Fab
                              variant="extended"
                              size="small"
                              color="primary"
                              onClick={this.handleClickDownloadXLSX}
                         >
                              <TableIcon fontSize="small" className={classes.icon} />
                              <Typography className={classes.typography} variant="caption">
                                   Download As XLSX
                              </Typography>
                         </Fab>
                    </Paper>
                    {/* {this.renderSegmentTable()} */}
                    {this.renderRouteWiseTable()}
                    {this.renderTownWiseTable()}
                    {this.renderCustomerTable()}
               </Layout>
          );
     }

     renderCustomerTable() {
          const { classes, searched } = this.props;

          if (!searched)
               return null;

          return [
               <div>
                    <Typography align="center" variant="h6">Customer Wise Achievement</Typography>
                    <Table>
                         <CustomerTableheader className={classes.darkCell} />
                         <TableBody>
                              {this.renderCustomerTableData()}
                         </TableBody>
                    </Table>
               </div>
          ];
     }

     renderCustomerTableData() {
          const { classes, rowData3 } = this.props;

          return rowData3.map((row, i) => (
               <TableRow className={classes.padding} key={i} style={{ background: row.special ? blue[200] : '' }}>
                    <TableCell className={classes.lightCell}>
                         {row.chemist}
                    </TableCell>
                    <TableCell className={classes.lightCell}>
                         {row.chemist_name}
                    </TableCell>
                    <TableCell className={classes.lightCell}>
                         {row.target}
                    </TableCell>
                    <TableCell className={classes.lightCell}>
                         {row.achi}
                    </TableCell>
                    <TableCell className={classes.lightCell}>
                         {row.precentage}
                    </TableCell>
                    <TableCell className={classes.lightCell}>
                         {row.balance}
                    </TableCell>
               </TableRow>
          ));
     }

     renderSegmentTable() {
          const { classes, searched } = this.props;

          if (!searched)
               return null;

          return [
               <div>
                    <Typography align="center" variant="h6">Segment Wise Achievement</Typography>
                    <Table>
                         <SegmentTableHeader className={classes.darkCell} />
                         <TableBody>
                              {this.renderSegmentTableData()}
                         </TableBody>
                    </Table>
               </div>
          ];
     }

     renderSegmentTableData() {
          const { classes, rowData3 } = this.props;

          return ['Retailer', 'Wholesale', 'Hospital', 'Stocking Doctor'].map((row, i) => {

               let target_tot = 0;
               let achi_tot = 0;
               let achi = 0;
               let balance = 0;

               rowData3.map((rowNew, i) => {
                    if ((rowNew.type == 1 || rowNew.type == 12) && row == 'Retailer') {
                         target_tot += parseFloat(rowNew.target)
                         achi_tot += parseFloat(rowNew.achi)
                    } else if ((rowNew.type == 5 || rowNew.type == 15) && row == 'Hospital') {
                         target_tot += parseFloat(rowNew.target)
                         achi_tot += parseFloat(rowNew.achi)
                    }
               });

               if (achi_tot > 0 && target_tot > 0) {
                    achi = (achi_tot / target_tot) * 100;
                    balance = achi_tot - target_tot;
               }

               return [
                    <TableRow className={classes.padding} key={i} style={{ background: row.special ? blue[200] : '' }}>
                         <TableCell className={classes.lightCell}>
                              {row}
                         </TableCell>
                         <TableCell className={classes.lightCell}>
                              {target_tot}
                         </TableCell>
                         <TableCell className={classes.lightCell}>
                              {achi_tot}
                         </TableCell>
                         <TableCell className={classes.lightCell}>
                              {achi}
                         </TableCell>
                         <TableCell className={classes.lightCell}>
                              {balance}
                         </TableCell>
                         <TableCell className={classes.lightCell}>
                              0
                         </TableCell>
                    </TableRow>
               ];
          });
     }

     renderTownWiseTable() {
          const { classes, searched } = this.props;

          if (!searched)
               return null;

          return [
               <div>
                    <Typography align="center" variant="h6">Town Wise Achievement</Typography>
                    <Table>
                         <TownWiseTableHeader className={classes.darkCell} />
                         <TableBody>
                              {this.renderTownWiseTableData()}
                         </TableBody>
                    </Table>
               </div>
          ];
     }

     renderTownWiseTableData() {
          const { classes, rowData1 } = this.props;

          return rowData1.map((row, i) => (
               <TableRow className={classes.padding} key={i} style={{ background: row.special ? blue[200] : '' }}>
                    <TableCell className={classes.lightCell}>
                         {row.town_name}
                    </TableCell>
                    <TableCell className={classes.lightCell}>
                         {row.target}
                    </TableCell>
                    <TableCell className={classes.lightCell}>
                         {row.achi}
                    </TableCell>
                    <TableCell className={classes.lightCell}>
                         {row.ach_pra}
                    </TableCell>
                    <TableCell className={classes.lightCell}>
                         {row.balance}
                    </TableCell>
                    <TableCell className={classes.lightCell}>
                         0
                    </TableCell>
               </TableRow>
          ));
     }

     renderRouteWiseTable() {
          const { classes, searched } = this.props;

          if (!searched)
               return null;

          return [
               <div>
                    <Typography align="center" variant="h6">Route Wise Achievement</Typography>
                    <Table>
                         <RouteWiseTableHeader className={classes.darkCell} />
                         <TableBody>
                              {this.renderRouteWiseTableData()}
                         </TableBody>
                    </Table>
               </div>
          ];
     }

     renderRouteWiseTableData() {
          const { classes, rowData2 } = this.props;

          return rowData2.map((row, i) => (
               <TableRow className={classes.padding} key={i} style={{ background: row.special ? blue[200] : '' }}>
                    <TableCell className={classes.lightCell}>
                         {row.route}
                    </TableCell>
                    <TableCell className={classes.lightCell}>
                         {row.target}
                    </TableCell>
                    <TableCell className={classes.lightCell}>
                         {row.achi}
                    </TableCell>
                    <TableCell className={classes.lightCell}>
                         {row.ach_pra}
                    </TableCell>
                    <TableCell className={classes.lightCell}>
                         {row.balance}
                    </TableCell>
                    <TableCell className={classes.lightCell}>
                         0
                    </TableCell>
               </TableRow>
          ));
     }
}
TownWiseSales.propTypes = {
     classes: PropTypes.shape({
          padding: PropTypes.string,
          button: PropTypes.string,
          darkCell: PropTypes.string
     }),
     rowData1: PropTypes.array,
     rowData2: PropTypes.array,
     rowData3: PropTypes.array,
     searched: PropTypes.bool,
     values: PropTypes.object,
     onChangeValue: PropTypes.func,

     resultCount: PropTypes.oneOfType([
          PropTypes.string, PropTypes.number
     ])
}
export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(TownWiseSales));