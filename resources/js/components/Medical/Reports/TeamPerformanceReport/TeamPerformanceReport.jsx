import React, { Component } from "react";
import { connect } from 'react-redux';
import Paper from "@material-ui/core/Paper";
import Typography from "@material-ui/core/Typography";
import withStyles from "@material-ui/core/styles/withStyles";
import Divider from "@material-ui/core/Divider";
import Grid from "@material-ui/core/Grid";
import Button from "@material-ui/core/Button";
import Layout from "../../../App/Layout";
import AjaxDropdown from "../../../CrudPage/Input/AjaxDropdown";
import PropTypes from "prop-types";
import Table from "@material-ui/core/Table";
import TableBody from "@material-ui/core/TableBody";
import TableRow from "@material-ui/core/TableRow";
import TableCell from "@material-ui/core/TableCell";
import TableHeader from "./TableHeader";
import BottomPanel  from '../../../CrudPage/BottomPanel';
import { changeValue,fetchData,changePage,changePerPage } from "../../../../actions/Medical/TeamPerformanceReport";

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
     },
     newRowColor: {
          color: 'black',
          background: '#add0f0',
          border: '1px solid #e8e8e8'
     },
     new_color : {
          color: 'black',
          background: '#add0f0',
          border: '1px solid #e8e8e8'
     },
     blank_color : {
          color: 'black',
          background: '#d5d7db',
          border: '1px solid #e8e8e8'
     }
 })

 const mapStateToProps = state => ({
     ...state.TeamPerformanceReport,
     ...state.App
 });
 
 const mapDispatchToProps = dispatch => ({
     onChangeValue: (name, value) => dispatch(changeValue(name, value)),
     onSubmit: (values,page,perPage) => dispatch(fetchData(values,page,perPage)),
     onChangePage: (page) => dispatch(changePage(page)),
     onChangePerPage: (perPage) => dispatch(changePerPage(perPage))
 })

 class TeamPerformanceReport extends Component {
     constructor(props) {
          super(props);
          this.handleSubmitForm = this.handleSubmitForm.bind(this);
          this.handleChangePage = this.handleChangePage.bind(this);
          this.handleChangePerPage = this.handleChangePerPage.bind(this);
     }

     render() {
          const { classes,values,perPage,page,resultCount } = this.props;
          
          return (
              <Layout sidebar>
                    <Paper className={classes.padding} >
                         <Typography variant="h5" align="center">Team Performance Reports</Typography>
                         <Divider />
                         <Grid container>
                              <Grid className={classes.padding} item md={6}>
                                   <AjaxDropdown value={values.team}  label="Team" link="team" name="team" onChange={this.changeFormValue('team')} />
                              </Grid>
                              <Grid className={classes.padding} item md={6}>
                                   <AjaxDropdown value={values.principal} label="Principal"  name="principal" link="principal" onChange={this.changeFormValue('principal')} />
                              </Grid>
                              <Grid item md={10}>
                                   <Divider />
                                   <Button onClick={this.handleSubmitForm} className={classes.button} variant="contained" color="primary" >Search</Button>
                              </Grid>
                         </Grid>
                    </Paper>
                    {this.renderMainTable()}
                    {this.renderFullSubTable()}
                    <BottomPanel
                         perPage={perPage}
                         page={page}
                         resultCount={resultCount}
                         onChangePage={this.handleChangePage}
                         onChangeRowCount={this.handleChangePerPage}
                    />
              </Layout>
          );
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

     handleChangePage(e, page) {
          const { onChangePage, onSubmit,values,searched,perPage } = this.props;

          
          if(!searched)
               return null;
          onChangePage(page);
          onSubmit(values,page,perPage);
     }

     handleChangePerPage(perPage) {
          const {
              onChangePerPage,
              onSubmit,
              values,
              page
          } = this.props;
          
          onChangePerPage(perPage);
          onSubmit(values,page,perPage);
     }

     renderMainTable(){
          const {classes,searched} = this.props;

          if(!searched)
               return null;
               
          return(
               <Table className={classes.table}>
                    <TableHeader className={classes.darkCell}/>
                    <TableBody>
                         {this.renderTable()}
                    </TableBody>
               </Table>
          );
     }

     renderFullSubTable(){
          const {classes,searched} = this.props;
          if(!searched)
               return null;
          return(
               <Layout>
                    <Typography variant="h6" align="center">Marcom wise Total</Typography>
                    <Table className={classes.table}>
                         {this.renderSubTable()}
                    </Table>
               </Layout>
          );
     }
     renderSubTable(){
          const {classes,hodData,rowData} = this.props;

          var nf = new Intl.NumberFormat();

          return hodData.map((row, i) => {
               let dec_net_sales_tot = 0;
               let jan_net_sales_tot = 0;
               let last_jan_net_sales_tot = 0;
               rowData.map((val,j) => {
           
                    if(val.hod_id == row.hod_id){
                         dec_net_sales_tot += val.dec_net_sales;
                         jan_net_sales_tot += val.jan_net_sales;
                         last_jan_net_sales_tot += row.last_jan_sale_value;
                    }
              
               });
               let sumOfdecjan = jan_net_sales_tot - dec_net_sales_tot;
               return [
                    <TableRow key={i}>
                         <TableCell
                              align='center'
                              padding='dense'
                              colSpan={4}
                              className={classes.darkCell}
                         >
                         {row.hod_name}|{row.hod_id}
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.new_color}
                         >
                         {dec_net_sales_tot > 0 ? nf.format(dec_net_sales_tot):0}.00
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.blank_color}
                         >
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.new_color}
                         >
                         {jan_net_sales_tot > 0 ? nf.format(jan_net_sales_tot):0}.00
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.blank_color}
                         >
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.new_color}
                         >
                         {sumOfdecjan > 0 ? nf.format(sumOfdecjan):0}.00
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.blank_color}
                         >
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.blank_color}
                         >
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.new_color}
                         >
                         {last_jan_net_sales_tot > 0 ? last_jan_net_sales_tot:0}.00
                         </TableCell>
                    </TableRow>
               ];
          });
     }

     renderTable(){
          const {rowData,classes} = this.props;

          var dec_net_sales_tot = 0;
          var jan_net_sales_tot = 0;
          var value_tot = 0;
          var last_jan_net_sales_tot = 0;
          var last_value_growth_tot = 0;

          var nf = new Intl.NumberFormat();

          return rowData.map((row, i) => {
               var team = rowData[i-1];
               if(i > 0){
                    if(row.tm_id == team.tm_id){
                         dec_net_sales_tot += row.dec_net_sales;
                         jan_net_sales_tot += row.jan_net_sales;
                         value_tot += row.value;
                         last_jan_net_sales_tot += row.last_jan_sale_value;
                         last_value_growth_tot += row.last_jan_sale_value_growth;
                    }
                    return [
                         row.tm_id != team.tm_id?
                              <TableRow>
                                   <TableCell className={classes.newRowColor}>
                                   </TableCell>
                                   <TableCell className={classes.newRowColor}>
                                   </TableCell>
                                   <TableCell className={classes.newRowColor}>
                                        {row.tm_name+' Sub Total'}
                                   </TableCell>
                                   <TableCell className={classes.newRowColor}>
                                   </TableCell>
                                   <TableCell className={classes.newRowColor}>
                                        {nf.format(dec_net_sales_tot)}
                                   </TableCell>
                                   <TableCell className={classes.newRowColor}>
                                   </TableCell>
                                   <TableCell className={classes.newRowColor}>
                                        {jan_net_sales_tot > 0 ? nf.format(jan_net_sales_tot):0}
                                   </TableCell>
                                   <TableCell className={classes.newRowColor}>
                                   </TableCell>
                                   <TableCell className={classes.newRowColor}>
                                        {nf.format(value_tot)}
                                   </TableCell>
                                   <TableCell >
                                   </TableCell>
                                   <TableCell className={classes.newRowColor}>
                                   </TableCell>
                                   <TableCell className={classes.newRowColor}>
                                        {last_jan_net_sales_tot > 0 ? nf.format(last_jan_net_sales_tot):0}
                                   </TableCell>
                                   <TableCell className={classes.newRowColor}>
                                   </TableCell>
                                   <TableCell className={classes.newRowColor}>
                                        {last_value_growth_tot}
                                   </TableCell>
                              </TableRow>
                         :null,
                              <TableRow key={i} className={classes.lightCell}>
                                   <TableCell className={classes.lightCell}>
                                        {row.tm_name}
                                   </TableCell>
                                   <TableCell className={classes.lightCell}>
                                        {row.pro_code}
                                   </TableCell>
                                   <TableCell className={classes.lightCell}>
                                        {row.pro_name}
                                   </TableCell>
                                   <TableCell className={classes.lightCell}>
                                        {row.dec_net_qty}
                                   </TableCell>
                                   <TableCell className={classes.lightCell}>
                                        {nf.format(row.dec_net_sales)}
                                   </TableCell>
                                   <TableCell className={classes.lightCell}>
                                        {row.jan_net_qty}
                                   </TableCell>
                                   <TableCell className={classes.lightCell}>
                                        {nf.format(row.jan_net_sales)}
                                   </TableCell>
                                   <TableCell className={classes.lightCell}>
                                        {row.qty}
                                   </TableCell>
                                   <TableCell className={classes.lightCell}>
                                        {row.value}
                                   </TableCell>

                                   <TableCell>
                                        {row.null_colum}
                                   </TableCell>

                                   <TableCell className={classes.lightCell}>
                                        {row.last_jan_sale_qty}
                                   </TableCell>
                                   <TableCell className={classes.lightCell}>
                                        {row.last_jan_sale_value}
                                   </TableCell>
                                   <TableCell className={classes.lightCell}>
                                        {row.last_jan_sale_qty_growth}
                                   </TableCell>
                                   <TableCell className={classes.lightCell}>
                                        {row.last_jan_sale_value_growth}
                                   </TableCell>
                              </TableRow>
                    ]
               }
               
          });
          
     }
}

TeamPerformanceReport.propTypes = {
     classes: PropTypes.shape({
         padding: PropTypes.string,
         button: PropTypes.string,
         darkCell: PropTypes.string
     }),

     rowData: PropTypes.array,
     values: PropTypes.object,
     onChangeValue: PropTypes.func,
     searched: PropTypes.bool,
     page:PropTypes.number,
     perPage:PropTypes.number,
     hodData: PropTypes.array,

     resultCount: PropTypes.oneOfType([
          PropTypes.string, PropTypes.number
     ])
}

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(TeamPerformanceReport));