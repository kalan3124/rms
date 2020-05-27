import React, { Component } from "react";
import { connect } from "react-redux";
import PropTypes from "prop-types";

import Calendar from "./Calendar";
import Modal from "@material-ui/core/Modal";
import Button from "@material-ui/core/Button";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import Radio from "@material-ui/core/Radio";
import Toolbar from "@material-ui/core/Toolbar";
import withStyles from "@material-ui/core/styles/withStyles";
import Grid from "@material-ui/core/Grid";
import Paper from "@material-ui/core/Paper";
import Typography from "@material-ui/core/Typography";
import Divider from "@material-ui/core/Divider";
import Tooltip from "@material-ui/core/Tooltip";
import CheckIcon from "@material-ui/icons/Check";
import CloseIcon from "@material-ui/icons/Close";
import red from "@material-ui/core/colors/red";
import lightGreen from "@material-ui/core/colors/lightGreen";

import { alertDialog } from "../../../actions/Dialogs";

import Layout from "../../App/Layout";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import AdditionalRoutePlanForm from "./AdditionalRoutePlanForm";
import JoinFieldWorkerForm from "./JoinFieldWorkerForm";
import OtherDayForm from "./OtherDayForm";

import {
    MEDICAL_FIELD_MANAGER_TYPE,
    MEDICAL_REP_TYPE,
    PRODUCT_SPECIALIST_TYPE
} from "../../../constants/config";
import {
    changeMR,
    changeFM,
    fetchYearMonth,
    selectDate,
    cancelDate,
    changeDate,
    fetchDayTypes,
    save,
    fetchDays,
    changeAdditionalValues,
    confirmAdditionalValues,
    clearDate,
    changeJoinFieldWorker,
    changeOtherDay,
    clearItinerary
} from "../../../actions/Medical/ItineraryPage";

const styles = theme => ({
    padding: {
        padding: theme.spacing.unit
    },
    topMargin: {
        marginTop: theme.spacing.unit
    },
    standardItinerayModal: {
        width: "20vw",
        minWidth: "400px",
        marginLeft: "35vw",
        marginTop: "70px",
        padding: theme.spacing.unit,
        maxHeight: theme.spacing.unit * 50,
        overflowY: "auto",
        [theme.breakpoints.down('md')]:{
            width:"80vw",
            minWidth: "unset",
            marginLeft: "5vw",
        }
    },
    additionalForm: {
        width: "40vw",
        [theme.breakpoints.down('md')]:{
            width:"80vw",
            minWidth: "unset",
        }
    },
    button: {
        float: "right",
        margin: theme.spacing.unit
    },
    grow: {
        flexGrow: 1
    },
    jointFieldWorkerModel: {
        width: "40vw",
        [theme.breakpoints.down('md')]:{
            width:"80vw",
            minWidth: "unset",
        }
    },
    otherDayModal: {
        width: "40vw",
        [theme.breakpoints.down('md')]:{
            width:"80vw",
            minWidth: "unset",
        }
    },
    approvedButton: {
        textAlign: "center"
    },
    gray: {
        color: theme.palette.grey[400]
    },
    green: {
        color: lightGreen[700]
    },
    red: {
        color: red[700]
    }
});

const mapStateToProps = state => ({
    ...state,
    ...state.App,
    ...state.ItineraryPage
});

const mapDispatchToProps = dispatch => ({
    onYearMonthChange: (yearMonth, mr, fm) =>
        dispatch(fetchYearMonth(yearMonth, mr, fm)),
    onMRChange: rep => dispatch(changeMR(rep)),
    onFMChange: fm => dispatch(changeFM(fm)),
    onChangeDate: values => dispatch(changeDate(values)),
    onDayTypesLoad: () => dispatch(fetchDayTypes()),
    onSave: (dates, yearMonth, mr, fm) =>
        dispatch(save(dates, yearMonth, mr, fm)),
    onDateSelect: (dateDetails, mode) =>
        dispatch(selectDate(dateDetails, mode)),
    onDaysLoad: (rep, type) => dispatch(fetchDays(rep, type)),
    onDateCancel: () => dispatch(cancelDate()),
    onChangeAdditionalValues: values =>
        dispatch(changeAdditionalValues(values)),
    onConfirmAdditional: (date, additionalValues) =>
        dispatch(confirmAdditionalValues(date, additionalValues)),
    onClearDate: date => dispatch(clearDate(date)),
    onChangeJoinFieldWorker: (date, value) =>
        dispatch(changeJoinFieldWorker(date, value)),
    onOtherDayChange: (date, value) => dispatch(changeOtherDay(date, value)),
    onClear: () => dispatch(clearItinerary()),
    onErrorAppeared: error => dispatch(alertDialog(error, "error"))
});

class ItineraryPage extends Component {
    constructor(props) {
        super(props);

        let actionNames = [
            "MRChange",
            "FMChange",
            "DateChange",
            "CancelItinerary",
            "SaveItinerary",
            "DayTypeChange",
            "OtherDayChange",
            "Clear"
        ];

        actionNames.forEach(actionName => {
            this["handle" + actionName] = this["handle" + actionName].bind(
                this
            );
        });

        props.onDayTypesLoad();
        props.onYearMonthChange(props.yearMonth, props.mr, props.fm);
        this.handleSelectDateChangeItinerary = this.handleSelectDateChangeItinerary.bind(
            this
        );
        this.handleSelectDateCreatePlan = this.handleSelectDateCreatePlan.bind(
            this
        );
        this.handleSelectDateJoinField = this.handleSelectDateJoinField.bind(
            this
        );
        this.handleConfirmAdditional = this.handleConfirmAdditional.bind(this);
        this.handleClearDate = this.handleClearDate.bind(this);
        this.handleChangeJoinFieldWorker = this.handleChangeJoinFieldWorker.bind(
            this
        );
        this.handleSelectOtherDate = this.handleSelectOtherDate.bind(this);
    }

    handleMRChange(value) {
        const {
            yearMonth,
            fm,
            onMRChange,
            onYearMonthChange,
            onDaysLoad
        } = this.props;
        onMRChange(value);
        onYearMonthChange(yearMonth, value, fm);
        onDaysLoad(value, 1);
    }

    handleFMChange(value) {
        const {
            yearMonth,
            mr,
            onFMChange,
            onYearMonthChange,
            onDaysLoad
        } = this.props;
        onFMChange(value);
        onYearMonthChange(yearMonth, mr, value);
        onDaysLoad(value, 2);
    }

    handleYearMonthChange(value) {
        const { fm, mr, onYearMonthChange } = this.props;
        onYearMonthChange(value, mr, fm);
    }

    handleDateChange(value) {
        const { yearMonth } = this.props;

        if (value.format("YYYY-MM") != yearMonth) {
            this.handleYearMonthChange(value.format("YYYY-MM"));
            return;
        }
    }

    handleCancelItinerary() {
        const { yearMonth, fm, mr, onYearMonthChange } = this.props;
        onYearMonthChange(yearMonth, mr, fm);
    }

    handleSaveItinerary() {
        const { dates, onSave, yearMonth, mr, fm } = this.props;
        onSave(dates, yearMonth, mr, fm);
    }

    handleDayTypeChange(dateDetails, typeId) {
        const { onChangeDate } = this.props;

        let modedDateDetails = { ...dateDetails };

        let included = modedDateDetails.types.includes(typeId);

        if (included) {
            modedDateDetails.types = modedDateDetails.types.filter(
                i => i != typeId
            );
        } else {
            modedDateDetails.types = [...modedDateDetails.types, typeId];
        }

        onChangeDate(modedDateDetails);
    }

    handleDescriptionChange(dateDetails, description) {
        const { onChangeDate, onDateCancel } = this.props;

        let modedDateDetails = { ...dateDetails, description };

        onChangeDate(modedDateDetails);

        onDateCancel();
    }

    handleOtherDayChange(value) {
        const { onOtherDayChange, updatingDate } = this.props;

        onOtherDayChange(updatingDate.date, value);
    }

    handleSelectDateCreatePlan(dateDetails) {
        const { onDateSelect, onChangeAdditionalValues } = this.props;

        onChangeAdditionalValues(
            typeof dateDetails.additionalValues != "undefined"
                ? dateDetails.additionalValues
                : {}
        );

        onDateSelect(dateDetails, "cp");
    }

    handleSelectDateChangeItinerary(dateDetails) {
        const { onDateSelect } = this.props;

        onDateSelect(dateDetails, "si");
    }

    handleSelectDateJoinField(dateDetails) {
        const { onDateSelect } = this.props;

        onDateSelect(dateDetails, "jf");
    }

    handleConfirmAdditional() {
        const {
            additionalValues,
            onConfirmAdditional,
            updatingDate,
            onErrorAppeared
        } = this.props;

        if (!additionalValues.areas || !additionalValues.areas.length) {
            onErrorAppeared("Please select at least one area.");
            return;
        }

        onConfirmAdditional(updatingDate.date, additionalValues);
    }

    handleClearDate(details) {
        const { onClearDate, onChangeDate } = this.props;

        if (details.special) {
            let modedDetails = {
                date: details.date,
                special: details.special,
                types: []
            };

            onChangeDate(modedDetails);
        } else {
            onClearDate(details.date);
        }
    }

    handleChangeJoinFieldWorker(value) {
        const { updatingDate, onChangeJoinFieldWorker } = this.props;

        onChangeJoinFieldWorker(updatingDate.date, value);
    }

    handleSelectOtherDate(dateDetails) {
        const { onDateSelect } = this.props;

        onDateSelect(dateDetails, "od");
    }

    handleClear() {
        const { onClear } = this.props;

        onClear();
    }

    renderStandardItineraryModal(key) {
        const { updatingMode, classes, days, onDateCancel } = this.props;

        return (
            <Modal key={key} onClose={onDateCancel} open={updatingMode == "si"}>
                <Paper className={classes.standardItinerayModal}>
                    <Typography align="center" variant="h6">
                        Select a day
                    </Typography>
                    <Divider />
                    <List>
                        {Object.keys(days).map(dayId =>
                            this.renderDay(days[dayId])
                        )}
                    </List>
                    <Toolbar>
                        <div className={classes.grow}>
                            <Button
                                className={classes.button}
                                variant="contained"
                                onClick={onDateCancel}
                                color="secondary"
                            >
                                Cancel
                            </Button>
                        </div>
                    </Toolbar>
                </Paper>
            </Modal>
        );
    }

    renderJoinFieldModal(key) {
        const {
            updatingMode,
            classes,
            updatingDate,
            onDateCancel,
            fm,
            yearMonth,
            mr
        } = this.props;
        if (
            typeof updatingDate == "undefined" ||
            typeof updatingDate.date == "undefined"
        )
            return null;

        return (
            <Modal onClose={onDateCancel} open={updatingMode == "jf"}>
                <JoinFieldWorkerForm
                    onChange={this.handleChangeJoinFieldWorker}
                    value={
                        updatingDate
                            ? updatingDate.joinFieldWorker
                            : undefined
                    }
                    key={key}
                    fm={fm}
                    user={typeof fm == "undefined" ? mr : fm}
                    date={
                        yearMonth +
                        "-" +
                        updatingDate.date.toString().padStart(2, "0")
                    }
                    userType={fm ? 1 : 2}
                />
            </Modal>
        );
    }

    renderOtherDayModal(key) {
        const {
            updatingMode,
            classes,
            updatingDate,
            onDateCancel,
            fm,
            mr
        } = this.props;

        return (
            <Modal onClose={onDateCancel} open={updatingMode == "od"}>
                <OtherDayForm
                    key={key}
                    onChange={this.handleOtherDayChange}
                    value={updatingDate ? updatingDate.otherDay : undefined}
                    userType={fm ? 1 : 2}
                    user={typeof fm == "undefined" ? mr : fm}
                />
            </Modal>
        );
    }

    renderDay(dateDetails) {
        const { updatingDate, dates } = this.props;

        const { date_number, description, id, bata, mileage } = dateDetails;

        let secondary = bata ? "Bata Type :- " + bata.label : "";
        secondary += "| Mileage :- " + mileage;

        let checked = false;

        const dateNumbers = Object.values(dates).map(date=>date.description?date.description.value:null);
        
        if(dateNumbers.includes(id)){
            return null;
        }

        if (
            updatingDate &&
            updatingDate.description &&
            updatingDate.description.value == id
        ) {
            checked = true;
        }

        return (
            <ListItem
                onClick={e =>
                    this.handleDescriptionChange(updatingDate, {
                        value: id,
                        label: description
                    })
                }
                divider
                button
                key={date_number}
            >
                <Radio checked={checked} />
                <ListItemText
                    primary={date_number + " . " + description}
                    secondary={secondary}
                />
            </ListItem>
        );
    }

    renderAdditionalForm(key) {
        const {
            updatingMode,
            classes,
            onDateCancel,
            onChangeAdditionalValues,
            additionalValues,
            fm,
            mr
        } = this.props;

        return (
            <Modal onClose={onDateCancel} open={updatingMode == "cp"}>
                <AdditionalRoutePlanForm
                    key={key}
                    onChange={onChangeAdditionalValues}
                    onClear={e => onChangeAdditionalValues({})}
                    values={additionalValues}
                    user={typeof fm == "undefined" ? mr : fm}
                    userType={fm ? 1 : 2}
                    onSubmit={this.handleConfirmAdditional}
                />
            </Modal>
        );
    }

    renderApprovedButton() {
        const { approved, classes } = this.props;

        if (typeof approved === "undefined") {
            return (
                <Tooltip title={"Approved status of your itinerary"}>
                    <CheckIcon className={classes.gray} />
                </Tooltip>
            );
        }

        if (approved) {
            return (
                <Tooltip title="Your itinerary is approved.">
                    <CheckIcon className={classes.green} />
                </Tooltip>
            );
        } else {
            return (
                <Tooltip title="Your itinerary is not approved yet.">
                    <CloseIcon className={classes.red} />
                </Tooltip>
            );
        }
    }

    render() {
        const { classes, mr, fm, yearMonth, dates, dayTypes } = this.props;

        const key = !mr ? (!fm ? 0 : fm.value) : mr.value;

        return (
            <Layout sidebar>
                <Grid container>
                    <Grid sm={12} item>
                        <Paper>
                            <Typography
                                className={classes.padding}
                                variant="h6"
                                align="center"
                            >
                                Individual Itinerary
                            </Typography>
                            <Divider />
                            <Grid container>
                                <Grid className={classes.padding} md={6} item>
                                    <AjaxDropdown
                                        onChange={this.handleMRChange}
                                        value={mr}
                                        link="user"
                                        label="MR/PS"
                                        where={{ u_tp_id: MEDICAL_REP_TYPE+'|'+PRODUCT_SPECIALIST_TYPE }}
                                    />
                                </Grid>
                                <Grid className={classes.padding} md={6} item>
                                    <AjaxDropdown
                                        onChange={this.handleFMChange}
                                        value={fm}
                                        link="user"
                                        label="FM"
                                        where={{
                                            u_tp_id: MEDICAL_FIELD_MANAGER_TYPE
                                        }}
                                    />
                                </Grid>
                            </Grid>
                        </Paper>
                        <div className={classes.topMargin}>
                            <Grid justify="center" container>
                                <Grid item>
                                    <div className={classes.approvedButton}>
                                        {this.renderApprovedButton()}
                                    </div>
                                    <Calendar
                                        onDateSelect={this.handleDateChange}
                                        yearMonth={yearMonth}
                                        dates={dates}
                                        dayTypes={dayTypes}
                                        onDayTypeChange={
                                            this.handleDayTypeChange
                                        }
                                        onDescriptionFocus={
                                            this.handleSelectDateChangeItinerary
                                        }
                                        onCreateRoutePlan={
                                            this.handleSelectDateCreatePlan
                                        }
                                        onJointFieldWorker={
                                            this.handleSelectDateJoinField
                                        }
                                        onAddMileage={
                                            this.handleSelectOtherDate
                                        }
                                        onClearDate={this.handleClearDate}
                                        type={mr ? "mr" : fm ? "fm" : undefined}
                                    />
                                    <Button
                                        margin="dense"
                                        className={classes.button}
                                        variant="contained"
                                        onClick={this.handleSaveItinerary}
                                        color="primary"
                                    >
                                        Save
                                    </Button>
                                    <Button
                                        margin="dense"
                                        className={classes.button}
                                        variant="contained"
                                        onClick={this.handleClear}
                                        color="secondary"
                                    >
                                        Cancel
                                    </Button>
                                    <Button
                                        margin="dense"
                                        className={classes.button}
                                        variant="contained"
                                        onClick={this.handleCancelItinerary}
                                        color="secondary"
                                    >
                                        Undo
                                    </Button>
                                </Grid>
                            </Grid>
                        </div>
                        {this.renderStandardItineraryModal(key)}
                        {this.renderAdditionalForm(key + 1)}
                        {this.renderJoinFieldModal(key + 2)}
                        {this.renderOtherDayModal(key + 3)}
                    </Grid>
                </Grid>
            </Layout>
        );
    }
}

ItineraryPage.propTypes = {
    // jf = Joint Field work, cp = Additional route plan, si = Standard Itinerary , od = Other day
    updatingMode: PropTypes.oneOf(["jf", "cp", "si", "od"])
};

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(withStyles(styles)(ItineraryPage));
