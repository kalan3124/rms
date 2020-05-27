import React, { Component } from "react";
import { connect } from 'react-redux';
import PropTypes from "prop-types";
import ReactHTMLTableToExcel from 'react-html-table-to-excel';

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

import AjaxDropdown from "../../../CrudPage/Input/AjaxDropdown";
import DatePicker from "../../../CrudPage/Input/DatePicker";
import { fetchTypes, changeValue, fetchData } from "../../../../actions/Medical/FmLevelReport";
import Layout from "../../../App/Layout";
import { MEDICAL_FIELD_MANAGER_TYPE } from '../../../../constants/config';
import TableHeader from "./TableHeader";
import DayPlanTableHeader from "./DayPlanTableHeader";
import JfwTableHeader from "./JfwTableHeader";

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
     }
 })


 const mapStateToProps = state => ({
     ...state.FmLevelReport
 });
 
 const mapDispatchToProps = dispatch => ({
     onChangeValue: (name, value) => dispatch(changeValue(name, value)),
     onSubmit: (values) => dispatch(fetchData(values))
 })
 

class FmLevelReport extends Component{
    

     constructor(props) {
          super(props);

          
          this.handleSubmitForm = this.handleSubmitForm.bind(this);
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

     render() {
          const { classes, values } = this.props;
  
          return (
              <Layout sidebar>
                  <Paper className={classes.padding} >
                      <Typography variant="h5" align="center">FM Level Report</Typography>
                      <Divider />
                      <Grid container>
                        <Grid className={classes.padding} item md={5}>
                            <AjaxDropdown value={values.team} label="Team" onChange={this.changeFormValue('team')} link="team" name="tm_id" />
                        </Grid>
                        <Grid className={classes.padding} item md={5}>
                            <AjaxDropdown where={{ tm_id:'{tm_id}',divi_id:'{divi_id}',u_tp_id:MEDICAL_FIELD_MANAGER_TYPE}} otherValues={{ tm_id: values.team,divi_id:values.division }} value={values.user} label="Field Manager" onChange={this.changeFormValue('user')}  link="user" name="u_id" />
                        </Grid>
                        <Grid className={classes.padding} item md={5}>
                            <AjaxDropdown value={values.division} label="Division" onChange={this.changeFormValue('division')}  name="division" link="division" />
                        </Grid>
                        <Grid className={classes.padding} item md={5}>
                            <DatePicker value={values.e_date} label="Month" onChange={this.changeFormValue('e_date')}  name="e_date" />
                        </Grid>
                        <Grid item md={10}>
                            <Divider />
                            <Button onClick={this.handleSubmitForm} className={classes.button} variant="contained" color="primary" >Search</Button>
                        </Grid>
                    </Grid>
                  </Paper>
                  {this.renderTable()}
              </Layout>
              
          );
     }

     renderTable() {
          const { searched, classes, resultCount } = this.props;
  
          if (!searched)
              return null;
  
          return (
              <div>
                  <Table id="table-to-xls" className={classes.table} padding="dense">
                      <TableHeader className={classes.darkCell} />
                      <TableBody>
                          {this.renderValues()}
                      </TableBody>
                      {this.renderDayPlanTable()}<br></br>
                      <JfwTableHeader className={classes.darkCell}/>
                      {this.renderJFWTable()} 
                  </Table>
  
                  <ReactHTMLTableToExcel
                      id="test-table-xls-button"
                      className="download-table-xls-button"
                      table="table-to-xls"
                      filename="expences_statement"
                      sheet="expences_statement"
                      buttonText="Download as XLS"
                  />
              </div>
          )
      }

      renderValues() {
          const { rowData, classes } = this.props;
          return rowData.map((row, i) => (
              <TableRow key={i} >
                  {row.map((cell, j) => (
                      <TableCell className={classes.lightCell} key={j}>
                          {cell}
                      </TableCell>
                  ))}
              </TableRow>
          ))
      }

      renderDayPlanTable(){
          var km = 0;
          var wk = 0;
          var spm = 0;
          var leave = 0;
          var tour = 0;
          var office = 0;
          var traveling = 0;
          var JFW = 0;

          const { rowData,classes } = this.props;
          rowData.forEach(element => {
              if(parseInt(element[4]) != 0 & element[4] != null){
                km +=parseInt(element[4]);
              }

              if(element[2] != "-"){
                JFW++;
              }
              if(element[1] == "WORKING DAY" && !element[2]){
                  wk++;
              }
              if(element[1] == "SPM"){
                  spm++;
              }
              if(element[1] == "LEAVE"){
                leave++;
              }
              if(element[1] == "OFFICE TOURS"){
                tour++;
              }
              if(element[1] == "OFFICE"){
                office++;
              }
              if(element[1] == "TRAVELING"){
                traveling++;
              }
              
          });
           return (
            
               <div className={classes.table} padding="dense">
                    <DayPlanTableHeader className={classes.darkCell} />
                    <TableBody>
                        <TableRow>
                            <TableCell className={classes.lightCell} >
                                Total Km(s)
                            </TableCell>
                            <TableCell className={classes.lightCell} >
                                {km}
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell className={classes.lightCell} >
                                JFW Work Day
                            </TableCell>
                            <TableCell className={classes.lightCell} >
                                {JFW}
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell className={classes.lightCell} >
                                Individual Work Day
                            </TableCell>
                            <TableCell className={classes.lightCell} >
                                {wk}
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell className={classes.lightCell} >
                                SPM
                            </TableCell>
                            <TableCell className={classes.lightCell} >
                                {spm}
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell className={classes.lightCell} >
                                LEAVE
                            </TableCell>
                            <TableCell className={classes.lightCell} >
                                {leave}
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell className={classes.lightCell} >
                                TOUR
                            </TableCell>
                            <TableCell className={classes.lightCell} >
                                {tour}
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell className={classes.lightCell} >
                                OFFICE
                            </TableCell>
                            <TableCell className={classes.lightCell} >
                                {office}
                            </TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell className={classes.lightCell} >
                                TRAVELING
                            </TableCell>
                            <TableCell className={classes.lightCell} >
                                {traveling}
                            </TableCell>
                        </TableRow>
                    </TableBody>
               </div>
           );
      }

    renderJFWTable(){
        const { rowData,classes,jfw } = this.props;
        console.log(jfw);
        
        return jfw.map((row, i) => (
            <Table>
                {row[2] != "" ?(
                    <TableRow>
                        <TableCell className={classes.lightCell} key={i}>
                            {row.name}
                        </TableCell>
                    </TableRow>
                ):''}
            </Table>
        ));
    }

}

FmLevelReport.propTypes = {
     classes: PropTypes.shape({
         padding: PropTypes.string,
         button: PropTypes.string,
         darkCell: PropTypes.string
     }),
 
     rowData: PropTypes.array,
     searched: PropTypes.bool,
 
     values: PropTypes.object,
     onChangeValue: PropTypes.func,
 
     resultCount: PropTypes.oneOfType([
         PropTypes.string, PropTypes.number
     ]),
 }
 
 export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(FmLevelReport));