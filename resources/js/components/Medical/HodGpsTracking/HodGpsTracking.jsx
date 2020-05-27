import React, { Component } from "react";
import { connect } from "react-redux";
import { GMAP_KEY } from "../../../constants/config";
import Input from "../../CrudPage/Input/Input";
import Layout from "../../App/Layout";

import Typography from "@material-ui/core/Typography";
import Toolbar from "@material-ui/core/Toolbar";
import withStyles from "@material-ui/core/styles/withStyles";
import Button from "@material-ui/core/Button";
import Paper from "@material-ui/core/Paper";
import blue from "@material-ui/core/colors/blue";
import Tooltip from "@material-ui/core/Tooltip";
import { Grid } from "@material-ui/core";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import DatePicker from "../../CrudPage/Input/DatePicker";
import { HEAD_OF_DEPARTMENT_TYPE } from "../../../constants/config";
import {
     changeDate,
     changeUser,
     search,
     loadStop,
     clearPage,
     alert
} from "../../../actions/Medical/HodGpsTracking";

import { Map, InfoWindow, Marker, GoogleApiWrapper } from 'google-maps-react';

const styler = withStyles(theme => ({
     input: {
          maxWidth: 260,
          marginLeft: theme.spacing.unit,
          marginRight: theme.spacing.unit
     },
     paper: {
          height: 400
     },
     grow: {
          flexGrow: 1
     },
     topPaper: {
          background: blue[400],
          color: theme.palette.common.white,
          padding: 0,
          minHeight: 32
     },
     bottomPaper: {
          background: theme.palette.grey[800],
          color: theme.palette.common.white,
          padding: theme.spacing.unit * 2
     },
     mapWrapper: {
          position: "relative",
          paddingBottom: 60,
          height: 400
     }
}));

const mapStateToProps = state => ({
     ...state.HodGpsTracking
});

const mapDispatchToProps = dispatch => ({
     onChangeUser: user => dispatch(changeUser(user)),
     onChangeDate: date => dispatch(changeDate(date)),
     onSearch: (user, date) => dispatch(search(user, date)),
     onStop: (stop) => dispatch(loadStop(stop)),
     onClearPage: () => dispatch(clearPage()),
     Alert: (msg) => dispatch(alert(msg))
});

class HodGpsTracking extends Component {
     constructor(props) {
          super(props);

          this.state = {
               showingInfoWindow: false,
               activeMarker: {},
               selectedPlace: {},
               click: false
          };

          this.onMouseoverMarker = this.onMouseoverMarker.bind(this);
          this.handleChangeHode = this.handleChangeHode.bind(this);
          this.handleChangeDate = this.handleChangeDate.bind(this);
          this.handleSearch = this.handleSearch.bind(this);
     }

     componentWillUnmount() {
          const { stop } = this.props;
          clearInterval(stop);
     }

     handleChangeHode(user) {
          const { onChangeUser, stop, onClearPage } = this.props;
          onClearPage();
          onChangeUser(user);
          clearInterval(stop);
          this.setState({
               click: false
          });
     }

     handleChangeDate(date) {
          const { onChangeDate } = this.props;
          onChangeDate(date);
     }

     handleSearch() {
          const { onSearch, onStop, user, date, Alert } = this.props;
          if (user) {
               let stop = setInterval(() => {
                    onSearch(user, date);
               }, 4000);
               onStop(stop);
          } else {
               Alert('User Field is Required');
          }
          this.setState({
               click: true
          });
     }

     onMouseoverMarker(props, marker, e) {
          this.setState({
               selectedPlace: props,
               activeMarker: marker,
               showingInfoWindow: true
          });
     }

     render() {
          const { classes, user, date, coordinates, hodUsers, stop } = this.props;

          return (
               <Layout sidebar>
                    <Toolbar variant="dense">
                         <Typography variant="h6" align="center">
                              HOD GPS Map
                         </Typography>
                         <div className={classes.grow} />
                         <div className={classes.input}>
                              <AjaxDropdown value={user} onChange={this.handleChangeHode} link="user" label="Hod" where={{ u_tp_id: HEAD_OF_DEPARTMENT_TYPE }} />
                         </div>
                         <div style={{ display: 'none' }}>
                              <DatePicker value={date} onChange={this.handleChangeDate} label="Date" />
                         </div>
                         <Button
                              onClick={this.handleSearch}
                              color="primary"
                              variant="contained"
                              disabled={this.state.click?true:false}
                         >
                              Search
                    </Button>
                    </Toolbar>
                    <Paper className={classes.mapWrapper}>
                         <Map google={this.props.google}
                              zoom={hodUsers.length > 0 ? 8 : 15}
                              style={{
                                   width: '100%',
                                   height: '100%'
                              }}
                              initialCenter={{
                                   lat: 6.8964,
                                   lng: 79.9181
                              }}>
                              {
                                   Object.values(hodUsers).map((row, index) => {
                                        return (
                                             <Marker
                                                  key={index}
                                                  onMouseover={this.onMouseoverMarker}
                                                  title={'The marker'}
                                                  name={row}
                                                  position={{ lat: coordinates[row] != undefined ? coordinates[row].lat : 0.00000, lng: coordinates[row] != undefined ? coordinates[row].lng : 0.00000 }}
                                             />
                                        );
                                   })
                              }
                              {
                                   this.state.selectedPlace.name && coordinates[this.state.selectedPlace.name] != undefined ?
                                        <InfoWindow
                                             marker={this.state.activeMarker}
                                             visible={this.state.showingInfoWindow}
                                        >
                                             <div>
                                                  <h5>Code: {coordinates[this.state.selectedPlace.name].code}</h5>
                                                  <h5>Name: {coordinates[this.state.selectedPlace.name].name}</h5>
                                                  <h5>Battery: {coordinates[this.state.selectedPlace.name].btry}</h5>
                                                  <h5>Time: {coordinates[this.state.selectedPlace.name].time}</h5>
                                             </div>
                                        </InfoWindow> : null
                              }
                         </Map>
                    </Paper>
               </Layout>
          );
     }
}

export default connect(
     mapStateToProps,
     mapDispatchToProps
)(GoogleApiWrapper({
     apiKey: GMAP_KEY
})(styler(HodGpsTracking)));