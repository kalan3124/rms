import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from "prop-types";
import Typography from '@material-ui/core/Typography';

import Layout from '../../App/Layout';
import Divider from "@material-ui/core/Divider";
import Grid from "@material-ui/core/Grid";
import withStyles from "@material-ui/core/styles/withStyles";
import Toolbar from "@material-ui/core/Toolbar";
import Button from "@material-ui/core/Button";
import SearchAndCheckPanel from './SearchAndCheckPanel';
import DirectionsIcon from '@material-ui/icons/Directions';
import AccountBalanceIcon from '@material-ui/icons/AccountBalance';
import SupervisorAccountIcon from "@material-ui/icons/SupervisorAccount";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import { withRouter } from 'react-router-dom';
import SaveIcon from "@material-ui/icons/Save";
import CloseIcon from "@material-ui/icons/Close";
import CloudUploadIcon from "@material-ui/icons/CloudUpload";
import { Link } from "react-router-dom";

import { changeDsrName, changeSrName, changeCheckedDsr, changeCheckedSr, fetchDsr, fetchSr, save, load, clearPage } from "../../../actions/Distributor/SrAllocation";

const mapStateToProps = state => ({
     ...state.SrAllocation
});

const mapDispatchToProps = dispatch => ({
     onChangeDsrName: dsrName => dispatch(changeDsrName(dsrName)),
     onChangeSrName: srName => dispatch(changeSrName(srName)),
     onSearchDsr: (keyword, delay) => dispatch(fetchDsr(keyword, delay)),
     onSearchSr: (keyword, delay) => dispatch(fetchSr(keyword, delay)),
     onChangeCheckedDsr: dsrChecked => dispatch(changeCheckedDsr(dsrChecked)),
     onChangeCheckedSr: srChecked => dispatch(changeCheckedSr(srChecked)),
     onSave: (sr, dsr) => dispatch(save(sr, dsr)),
     onLoad: (dsr) => dispatch(load(dsr)),
     onClearPage: () => dispatch(clearPage())
});

const styler = withStyles(theme => ({
     padding: {
          padding: theme.spacing.unit * 2
     },
     dense: {
          flexGrow: 1
     }, userField: {
          padding: theme.spacing.unit * 2
     }, zIndex: {
          zIndex: 1200
     }
}))

class SrAllocation extends Component {

     constructor(props) {
          super(props);
          this.state = {
               error: ''
          }

          props.onSearchDsr("", false);
          props.onSearchSr("", false);
          this.handleDSRChange = this.handleDSRChange.bind(this);
          this.handleSRChange = this.handleSRChange.bind(this);
          this.handleDSRChecked = this.handleDSRChecked.bind(this);
          this.handleSRChecked = this.handleSRChecked.bind(this);
          this.handleSaveButtonClick = this.handleSaveButtonClick.bind(this);
     }

     handleDSRChange(value) {
          const { onChangeDsrName, onSearchDsr } = this.props;

          onChangeDsrName(value);
          onSearchDsr(value)
     }

     handleSRChange(value) {
          const { onChangeSrName, onSearchSr } = this.props;

          onChangeSrName(value);
          onSearchSr(value)
     }

     handleDSRChecked(dsr, checked) {
          const { dsrChecked, onChangeCheckedDsr, onLoad } = this.props;

          let modedDsrChecked = dsrChecked.filter(({ value }) => value != dsr.value);

          if (checked) {
               onLoad(dsr);
               modedDsrChecked = [dsr];

          }
          this.state.error = "";

          onChangeCheckedDsr(modedDsrChecked);
     }

     handleSRChecked(sr, checked) {
          const { srChecked, onChangeCheckedSr } = this.props;

          let modedSrChecked = srChecked.filter(({ value }) => value != sr.value);

          if (checked) {
               modedSrChecked = [...modedSrChecked, sr];
          }

          onChangeCheckedSr(modedSrChecked);
     }

     handleSaveButtonClick() {
          const { dsrChecked, srChecked, onSave } = this.props;

          onSave(srChecked, dsrChecked);
     }

     render() {
          const {
               classes,
               srName,
               dsrName,
               srResults,
               dsrResults,
               srChecked,
               dsrChecked,
               onClearPage
          } = this.props;

          return (
               <Layout sidebar >
                    <Toolbar variant="dense" className={classes.zIndex}>
                         <Typography variant="h5" >SR Distributor Allocation</Typography>
                         <div className={classes.dense} />
                         <Button onClick={this.handleSaveButtonClick} variant="contained" color="secondary">
                              <SaveIcon />
                              Submit
                         </Button>
                         {/* <Grid item md="1"></Grid> */}
                         <Button onClick={onClearPage} margin="dense" className={classes.button} variant="contained" color="secondary">
                              <CloseIcon />
                              Cancel
                         </Button>
                         <Button
                              variant="contained"
                              color="secondary"
                              className={classes.button}
                              component={Link}
                              to="/sales/other/upload_csv/dsr_distributor"
                         >
                              <CloudUploadIcon />
                              Upload
                         </Button>
                    </Toolbar>
                    <Divider />
                    <Grid container>
                         <Grid item md={7}>
                              <SearchAndCheckPanel
                                   icon={
                                        <SupervisorAccountIcon />
                                   }
                                   label="DSR"
                                   keyword={dsrName}
                                   onSearch={this.handleDSRChange}
                                   results={dsrResults}
                                   onCheck={this.handleDSRChecked}
                                   checked={dsrChecked}
                              />
                         </Grid>
                         <Grid item md={5}>
                              <SearchAndCheckPanel
                                   icon={
                                        <SupervisorAccountIcon />
                                   }
                                   label="DISTRIBUTOR"
                                   keyword={srName}
                                   onSearch={this.handleSRChange}
                                   results={srResults}
                                   onCheck={this.handleSRChecked}
                                   checked={srChecked}
                              />
                         </Grid>
                    </Grid>
               </Layout>
          )
     }
}

const alloPropType = PropTypes.arrayOf(PropTypes.shape({
     value: PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
     label: PropTypes.string
}));

SrAllocation.propTypes = {
     classes: PropTypes.shape({
     }),

     onChangeDsrName: PropTypes.func,
     dsrName: PropTypes.string,

     onChangeSrName: PropTypes.func,
     srName: PropTypes.string,

     onSearchDsr: PropTypes.func,
     onSearchSr: PropTypes.func,

     srResults: alloPropType,
     dsrResults: alloPropType,
     srChecked: alloPropType,
     dsrChecked: alloPropType
}

export default connect(mapStateToProps, mapDispatchToProps)(styler(withRouter(SrAllocation)));
