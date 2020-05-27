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

import { changeDsrName, changeSiteName, changeCheckedDsr, changeCheckedSite, fetchDsr, fetchSite, save, load, clearPage } from "../../../actions/Distributor/SiteAllocation";

const mapStateToProps = state => ({
     ...state.SiteAllocation
});

const mapDispatchToProps = dispatch => ({
     onChangeDsrName: dsrName => dispatch(changeDsrName(dsrName)),
     onChangeSiteName: siteName => dispatch(changeSiteName(siteName)),
     onSearchDsr: (keyword, delay) => dispatch(fetchDsr(keyword, delay)),
     onSearchSite: (keyword, delay) => dispatch(fetchSite(keyword, delay)),
     onChangeCheckedDsr: dsrChecked => dispatch(changeCheckedDsr(dsrChecked)),
     onChangeCheckedSite: siteChecked => dispatch(changeCheckedSite(siteChecked)),
     onSave: (sr, dsr) => dispatch(save(sr, dsr)),
     onLoad: (site) => dispatch(load(site)),
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

class SiteAllocation extends Component {

     constructor(props) {
          super(props);
          this.state = {
               error: ''
          }

          props.onSearchDsr("", false);
          props.onSearchSite("", false);
          this.handleDSRChange = this.handleDSRChange.bind(this);
          this.handleSiteChange = this.handleSiteChange.bind(this);
          this.handleDSRChecked = this.handleDSRChecked.bind(this);
          this.handleSiteChecked = this.handleSiteChecked.bind(this);
          this.handleSaveButtonClick = this.handleSaveButtonClick.bind(this);
     }

     handleDSRChange(value) {
          const { onChangeDsrName, onSearchDsr } = this.props;

          onChangeDsrName(value);
          onSearchDsr(value)
     }

     handleSiteChange(value) {
          const { onChangeSiteName, onSearchSite } = this.props;

          onChangeSiteName(value);
          onSearchSite(value)
     }

     handleDSRChecked(dsr, checked) {
          const { dsrChecked, onChangeCheckedDsr} = this.props;

          let modedDsrChecked = dsrChecked.filter(({ value }) => value != dsr.value);

          if (checked) {
               modedDsrChecked = [...modedDsrChecked,dsr];
          }
          this.state.error = "";

          onChangeCheckedDsr(modedDsrChecked);
     }

     handleSiteChecked(site, checked) {
          const { siteChecked, onChangeCheckedSite,onLoad  } = this.props;

          let modedSiteChecked = siteChecked.filter(({ value }) => value != site.value);

          if (checked) {
               onLoad(site);
               modedSiteChecked = [site];
          }

          onChangeCheckedSite(modedSiteChecked);
     }

     handleSaveButtonClick() {
          const { dsrChecked, siteChecked, onSave } = this.props;

          onSave(siteChecked, dsrChecked);
     }

     render() {
          const {
               classes,
               siteName,
               dsrName,
               siteResults,
               dsrResults,
               siteChecked,
               dsrChecked,
               onClearPage
          } = this.props;

          return (
               <Layout sidebar >
                    <Toolbar variant="dense" className={classes.zIndex}>
                         <Typography variant="h5" >Distributor Site Allocation</Typography>
                         <div className={classes.dense} />
                         <Button onClick={this.handleSaveButtonClick} variant="contained" color="secondary">
                              <SaveIcon />
                              Submit
                         </Button>
                         <Grid item md="1"></Grid>
                         <Button onClick={onClearPage} margin="dense" className={classes.button} variant="contained" color="secondary">
                              <CloseIcon />
                              Cancel
                         </Button>
                    </Toolbar>
                    <Divider />
                    <Grid container>
                         <Grid item md={5}>
                              <SearchAndCheckPanel
                                   icon={
                                        <SupervisorAccountIcon />
                                   }
                                   label="SITE"
                                   keyword={siteName}
                                   onSearch={this.handleSiteChange}
                                   results={siteResults}
                                   onCheck={this.handleSiteChecked}
                                   checked={siteChecked}
                              />
                         </Grid>
                         <Grid item md={7}>
                              <SearchAndCheckPanel
                                   icon={
                                        <SupervisorAccountIcon />
                                   }
                                   label="DISTRIBUTOR"
                                   keyword={dsrName}
                                   onSearch={this.handleDSRChange}
                                   results={dsrResults}
                                   onCheck={this.handleDSRChecked}
                                   checked={dsrChecked}
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

SiteAllocation.propTypes = {
     classes: PropTypes.shape({
     }),

     onChangeDsrName: PropTypes.func,
     dsrName: PropTypes.string,

     onChangeSiteName: PropTypes.func,
     siteName: PropTypes.string,

     onSearchDsr: PropTypes.func,
     onSearchSite: PropTypes.func,

     siteResults: alloPropType,
     dsrResults: alloPropType,
     siteChecked: alloPropType,
     dsrChecked: alloPropType
}

export default connect(mapStateToProps, mapDispatchToProps)(styler(withRouter(SiteAllocation)));
