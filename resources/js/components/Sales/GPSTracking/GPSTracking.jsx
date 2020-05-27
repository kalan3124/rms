import React, { Component } from "react";
import { connect } from "react-redux";
import Player from "material-ui-gps-player";
import { APP_URL } from '../../../constants/config';

import {
    changeDate,
    changeUser,
    search,
    changeTime
} from "../../../actions/Sales/GPSTracking";
import { GMAP_KEY } from "../../../constants/config";
import { SALES_REP_TYPE } from "../../../constants/config";
import Input from "../../CrudPage/Input/Input";
import Layout from "../../App/Layout";

import Typography from "@material-ui/core/Typography";
import Toolbar from "@material-ui/core/Toolbar";
import withStyles from "@material-ui/core/styles/withStyles";
import Button from "@material-ui/core/Button";
import Paper from "@material-ui/core/Paper";
import blue from "@material-ui/core/colors/blue";
import Tooltip from "@material-ui/core/Tooltip";
import Marker from '@material-ui/icons/Room';
import AccessTimeIcon from '@material-ui/icons/AccessTime';
import Vehicle from '@material-ui/icons/TimeToLeave';

import BatteryAlertIcon from "@material-ui/icons/BatteryAlert";
import Battery20Icon from "@material-ui/icons/Battery20";
import Battery30Icon from "@material-ui/icons/Battery30";
import Battery50Icon from "@material-ui/icons/Battery50";
import Battery60Icon from "@material-ui/icons/Battery60";
import Battery80Icon from "@material-ui/icons/Battery80";
import Battery90Icon from "@material-ui/icons/Battery90";
import BatteryFullIcon from "@material-ui/icons/BatteryFull";

import SignalCellular0BarIcon from "@material-ui/icons/SignalCellular0Bar";
import SignalCellular1BarIcon from "@material-ui/icons/SignalCellular1Bar";
import SignalCellular2BarIcon from "@material-ui/icons/SignalCellular2Bar";
import SignalCellular3BarIcon from "@material-ui/icons/SignalCellular3Bar";
import SignalCellular4BarIcon from "@material-ui/icons/SignalCellular4Bar";
import { Grid } from "@material-ui/core";

const mapStateToProps = state => ({
    ...state.SalesGPSTracking
});

const mapDispatchToProps = dispatch => ({
    onChangeUser: user => dispatch(changeUser(user)),
    onChangeDate: date => dispatch(changeDate(date)),
    onSearch: (user, date) => dispatch(search(user, date)),
    onChangeTime: time => dispatch(changeTime(time))
});

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
        paddingBottom: 60
    }
}));

class GPSTracking extends Component {

    constructor(props) {
        super(props);

        this.handleSearch = this.handleSearch.bind(this);
    }

    calcCrow(lat1, lon1, lat2, lon2) {
        let R = 6371; // km
        let dLat = this.toRad(lat2 - lat1);
        let dLon = this.toRad(lon2 - lon1);
        lat1 = this.toRad(lat1);
        lat2 = this.toRad(lat2);

        let a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.sin(dLon / 2) * Math.sin(dLon / 2) * Math.cos(lat1) * Math.cos(lat2);
        let c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        let d = R * c;
        return d;
    }


    // Converts numeric degrees to radians
    toRad(Value) {
        return Value * Math.PI / 180;
    }

    handleSearch() {
        const { user, date, onSearch } = this.props;
        onSearch(user, date);
    }

    calculateDistance(coords) {

        let distanceTotal = 0;

        for (let i = 0; i < coords.length - 1; i++) {
            distanceTotal += this.calcCrow(coords[i].lat, coords[i].lng, coords[i + 1].lat, coords[i + 1].lng)
        }

        return distanceTotal;
    }

    getPassedCoordinates(coordinates, currentTime) {
        return coordinates.filter(
            coord => coord.time < currentTime
        );

    }

    getLastCoordinate(coordinates) {

        if (coordinates[coordinates.length - 1]) {
            return coordinates[coordinates.length - 1];
        }

        return null;
    }

    getCountByType(coordinates, type) {
        return coordinates.filter(coord => coord.type == type).length;
    }

    renderBatteryIcon(lastCoord) {

        const btry = lastCoord.batry;

        let BatteryIcon;

        if (btry < 20) {
            BatteryIcon = BatteryAlertIcon;
        } else if (btry < 30) {
            BatteryIcon = Battery20Icon;
        } else if (btry < 50) {
            BatteryIcon = Battery30Icon;
        } else if (btry < 60) {
            BatteryIcon = Battery50Icon;
        } else if (btry < 80) {
            BatteryIcon = Battery60Icon;
        } else if (btry < 90) {
            BatteryIcon = Battery80Icon;
        } else if (btry < 100) {
            BatteryIcon = Battery90Icon;
        } else {
            BatteryIcon = BatteryFullIcon;
        }

        return (
            <Tooltip title={"Battery :- " + btry + "%"}>
                <BatteryIcon />
            </Tooltip>
        );
    }

    renderAccuracyIcon(lastCoord) {

        const accurazy = lastCoord.accurazy;

        let AccuracyIcon;

        if (accurazy < 25) {
            AccuracyIcon = SignalCellular0BarIcon;
        } else if (accurazy < 50) {
            AccuracyIcon = SignalCellular1BarIcon;
        } else if (accurazy < 75) {
            AccuracyIcon = SignalCellular2BarIcon;
        } else if (accurazy < 100) {
            AccuracyIcon = SignalCellular3BarIcon;
        } else {
            AccuracyIcon = SignalCellular4BarIcon;
        }

        return (
            <Tooltip title={"Accuracy :- " + accurazy + "%"}>
                <AccuracyIcon />
            </Tooltip>
        );
    }

    render() {
        const {
            classes,
            date,
            onChangeDate,
            onChangeUser,
            user,
            onChangeTime,
            currentTime,
            coordinates,
            checkin,
            checkout
        } = this.props;

        console.log(user);

        const passedCoordinates = this.getPassedCoordinates(coordinates, currentTime);
        const lastCoord = this.getLastCoordinate(coordinates);

        const passedDistance = this.calculateDistance(passedCoordinates);
        const totalDistance = this.calculateDistance(coordinates);

        const passedProductives = this.getCountByType(passedCoordinates, 1);
        const totalProductives = this.getCountByType(coordinates, 1);

        const passedUnproductives = this.getCountByType(passedCoordinates, 0);
        const totalUnproductives = this.getCountByType(coordinates, 0)

        return (
            <Layout sidebar>
                <Toolbar variant="dense">
                    <Typography variant="h6" align="center">
                        GPS Map
                    </Typography>
                    <div className={classes.grow} />
                    <div className={classes.input}>
                        <Input
                            onChange={onChangeUser}
                            value={user}
                            label="User"
                            type="ajax_dropdown"
                            link="user"
                            where={{ u_tp_id: SALES_REP_TYPE }}
                        />
                    </div>
                    <div className={classes.input}>
                        <Input
                            onChange={onChangeDate}
                            value={date}
                            type="date"
                            label="Date"
                        />
                    </div>
                    <Button
                        onClick={this.handleSearch}
                        color="primary"
                        variant="contained"
                    >
                        Search
                    </Button>
                </Toolbar>
                <Toolbar variant="dense" className={classes.topPaper}>
                    <div className={classes.grow} />
                    {lastCoord && lastCoord.accurazy
                        ? this.renderAccuracyIcon(lastCoord)
                        : null}
                    {lastCoord && lastCoord.batry
                        ? this.renderBatteryIcon(lastCoord)
                        : null}
                </Toolbar>
                <div className={classes.mapWrapper}>
                    <Player
                        apiKey={GMAP_KEY}
                        key={(user ? user.value : 0) + date}
                        coordinates={coordinates}
                        language="en"
                        onChangeTime={onChangeTime}
                        zoom={17}
                        iconMarker={{
                            path: "M17.402,0H5.643C2.526,0,0,3.467,0,6.584v34.804c0,3.116,2.526,5.644,5.643,5.644h11.759c3.116,0,5.644-2.527,5.644-5.644 V6.584C23.044,3.467,20.518,0,17.402,0z M22.057,14.188v11.665l-2.729,0.351v-4.806L22.057,14.188z M20.625,10.773 c-1.016,3.9-2.219,8.51-2.219,8.51H4.638l-2.222-8.51C2.417,10.773,11.3,7.755,20.625,10.773z M3.748,21.713v4.492l-2.73-0.349 V14.502L3.748,21.713z M1.018,37.938V27.579l2.73,0.343v8.196L1.018,37.938z M2.575,40.882l2.218-3.336h13.771l2.219,3.336H2.575z M19.328,35.805v-7.872l2.729-0.355v10.048L19.328,35.805z",
                            strokeColor: "white",
                            scale: 1,
                            strokeColor: 'white',
                            strokeWeight: .30,
                            fillOpacity: 1,
                            fillColor: '#ac0878',
                            offset: '5%',
                            anchor: window.google ? new window.google.maps.Point(10, 35) : undefined
                        }}
                        polyLine={{
                            strokeWeight: 4,
                            strokeColor: "#0B5345"
                        }}
                    />
                </div>
                <Paper className={classes.bottomPaper} >
                    <Grid container>
                        <Grid item md={3}>
                            <Vehicle style={{ color: "#ff6161", marginLeft: 135, width: 50, height: 40 }} />
                            <Typography color="inherit" align="center" variant="body1">Distance</Typography>
                            <Typography color="inherit" align="center" variant="h6">{Math.round(passedDistance * 100) / 100} km / {Math.round(totalDistance * 100) / 100} km</Typography>
                        </Grid>
                        <Grid item md={3}>
                            <Marker style={{ color: "#36ac5b", marginLeft: 135, width: 50, height: 40 }} />
                            <Typography color="inherit" align="center" variant="body1">Productives</Typography>
                            <Typography color="inherit" align="center" variant="h6">{passedProductives} / {totalProductives}</Typography>
                        </Grid>
                        <Grid item md={3}>
                            <Marker style={{ color: "#cc1225", marginLeft: 135, width: 50, height: 40 }} />
                            <Typography color="inherit" align="center" variant="body1">Unproductives</Typography>
                            <Typography color="inherit" align="center" variant="h6">{passedUnproductives} / {totalUnproductives}</Typography>
                        </Grid>
                        <Grid item md={3}>
                            <AccessTimeIcon style={{ color: "#1288cc", marginLeft: 135, width: 50, height: 40 }} />
                            <Typography color="inherit" align="center" variant="body1">Check In and Out</Typography>
                            <Typography color="inherit" align="center" variant="h6">{checkin} - {checkout}</Typography>
                        </Grid>
                    </Grid>

                </Paper>
            </Layout>
        );
    }
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(styler(GPSTracking));