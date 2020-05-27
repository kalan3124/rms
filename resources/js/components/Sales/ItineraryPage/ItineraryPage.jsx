import React, { Component } from "react";
import { connect } from "react-redux";
import moment from "moment";

import Typography from "@material-ui/core/Typography";
import Toolbar from "@material-ui/core/Toolbar";
import Divider from "@material-ui/core/Divider";
import withStyles from "@material-ui/core/styles/withStyles";
import Button from "@material-ui/core/Button";
import Modal from "@material-ui/core/Modal";

import SaveIcon from "@material-ui/icons/Save";
import CloseIcon from "@material-ui/icons/Close";
import CloudUploadIcon from "@material-ui/icons/CloudUpload"

import Layout from "../../App/Layout";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import CrudForm from "../../CrudPage/CrudForm";
import Calendar from "./Calendar";
import {
    changeArea,
    fetchInformations,
    changeUser,
    selectDate,
    changeMode,
    selectDayType,
    unselectDayType,
    clearDate,
    changeValues,
    confirmChanges,
    cancelChanges,
    submit,
    changeType
} from "../../../actions/Sales/ItineraryPage";
import { SALES_REP_TYPE, DISTRIBUTOR_SALES_REP_TYPE } from "../../../constants/config";
import { AREA_SALES_MANAGER_TYPE } from "../../../constants/config";
import withRouter from "react-router/withRouter";
import Link from "react-router-dom/Link";

const styles = theme => ({
    grow: {
        flexGrow: 1
    },
    button: {
        margin: theme.spacing.unit * 2
    },
    userField: {
        marginLeft: theme.spacing.unit * 2,
        width: 300
    },
    modal: {
        width: "40vw",
        minWidth: "400px",
        marginLeft: "35vw",
        marginTop: "70px",
        padding: theme.spacing.unit
    },
    areaField: {
        marginLeft: theme.spacing.unit * 2,
        width: 300
    }
});

const mapStateToProps = state => ({
    ...state.SalesItineraryPage
});

const mapDispatchToProps = dispatch => ({
    onLoad: (user, year, month) =>
        dispatch(fetchInformations(user, year, month)),
    onChangeDate: yearMonth => dispatch(selectDate(yearMonth, undefined)),
    onChangeUser: user => dispatch(changeUser(user)),
    onChangeMode: mode => dispatch(changeMode(mode)),
    onChangeArea: area => dispatch(changeArea(area)),
    onSelectDayType: (date, dayType) => dispatch(selectDayType(date, dayType)),
    onUnselectDayType: (date, dayType) =>
        dispatch(unselectDayType(date, dayType)),
    onClearDate: date => dispatch(clearDate(date)),
    onChangeValues: values => dispatch(changeValues(values)),
    onConfirmChanges: () => dispatch(confirmChanges()),
    onCancelChanges: () => dispatch(cancelChanges()),
    onSubmit:(user,year,month,dates)=>dispatch(submit(user,year,month,dates)),
    onChangeType:(type)=>dispatch(changeType(type))
});

class ItineraryPage extends Component {
    constructor(props) {
        super(props);

        this.handleSelectDate = this.handleSelectDate.bind(this);
        this.handleChangeUser = this.handleChangeUser.bind(this);
        this.handleChangeMode = this.handleChangeMode.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleCancel = this.handleCancel.bind(this);
        this.handleChangeArea = this.handleChangeArea.bind(this);

        this.props.onChangeType(this.props.match.params.mode);
    }

    componentDidUpdate(prevProps){
        const {match} = this.props;

        if(match.params.mode != prevProps.match.params.mode){
            this.props.onChangeType(match.params.mode);
        }
    }

    handleSelectDate(details) {
        const { onChangeDate, onLoad, user, updatingDate } = this.props;

        if(details.format('YYYY-MM-DD')==updatingDate){
            return null;
        }

        onChangeDate(details.format("YYYY-MM-DD"));

        if (user && user[0] && details.format("YYYY-MM") != updatingDate.substr(0, 7)) {
            onLoad(user, details.format("YYYY"), details.format("MM"));
        }
    }

    handleChangeMode(date, mode) {
        const { onChangeMode } = this.props;

        onChangeMode(mode);
    }

    handleChangeUser(user) {
        const { onLoad, updatingDate, onChangeUser } = this.props;

        onChangeUser(user);

        if(typeof user!=='undefined'&& typeof user[0]!=='undefined')
            onLoad(user[0], updatingDate.substr(0, 4), updatingDate.substr(5, 2));
    }

    handleChangeArea(area){
        const {onChangeArea} = this.props;

        onChangeArea(area);
    }

    handleSubmit(){
        const {dates,user,updatingDate,onSubmit} = this.props;

        if(!user|!updatingDate){
            return null;
        }

        onSubmit(user,updatingDate.substr(0,4),updatingDate.substr(5,2),dates);
    }

    handleCancel(){
        const {user,updatingDate,onLoad} = this.props;

        if(!user|!updatingDate){
            return null;
        }

        if(typeof user!=='undefined'&& typeof user[0]!=='undefined')
            onLoad(user[0], updatingDate.substr(0, 4), updatingDate.substr(5, 2));

    }

    render() {
        const {
            classes,
            user,
            area,
            updatingDate,
            dayTypes,
            dates,
            onSelectDayType,
            onUnselectDayType,
            onClearDate,
            type
        } = this.props;

        return (
            <Layout sidebar>
                <Toolbar variant="dense">
                    <Typography variant="h5">Itinerary</Typography>
                    <div className={classes.grow} />
                    {type=="sales"?
                    <div className={classes.headField}>
                        <AjaxDropdown
                            onChange={this.handleChangeArea}
                            label="Area"
                            link="area"
                            value={area}
                        />
                    </div>
                    :null}
                    <div className={classes.headField}>
                        <AjaxDropdown
                            onChange={this.handleChangeUser}
                            label="User"
                            link={type=="sales"?"area_user_itinerary":"user"}
                            // link="user"
                            value={user}
                            multiple={true}
                            // where={{u_tp_id: type=="sales"? SALES_REP_TYPE:DISTRIBUTOR_SALES_REP_TYPE,dis_id:585,ar_id:'{ar_id}'}}
                            where={{
                                u_tp_id: type=="sales"? SALES_REP_TYPE:DISTRIBUTOR_SALES_REP_TYPE,
                                ar_id:'{ar_id}'
                            }}
                            otherValues={{ar_id:area}}
                        />
                    </div>
                    {type=='sales'?null:
                    <Link to="/sales/other/upload_csv/distributor_itinerary" >
                        <Button
                            className={classes.button}
                            variant="contained"
                        >
                            <CloudUploadIcon />
                            Upload
                        </Button>
                    </Link>
                    }
                    <Button
                        className={classes.button}
                        color="primary"
                        variant="contained"
                        disabled={!user||!updatingDate}
                        onClick={this.handleSubmit}
                    >
                        <SaveIcon />
                        Save
                    </Button>
                    <Button
                        className={classes.button}
                        color="secondary"
                        variant="contained"
                        onClick={this.handleCancel}
                    >
                        <CloseIcon />
                        Cancel
                    </Button>
                </Toolbar>
                <Divider />
                <Calendar
                    onDateSelect={this.handleSelectDate}
                    yearMonth={
                        updatingDate
                            ? updatingDate.substr(0, 7)
                            : moment().format("YYYY-MM")
                    }
                    dayTypes={dayTypes}
                    dates={dates}
                    onSelectDayType={onSelectDayType}
                    onUnselectDayType={onUnselectDayType}
                    onChangeMode={this.handleChangeMode}
                    onClear={onClearDate}
                />
                {this.renderFormModel()}
            </Layout>
        );
    }

    renderFormModel() {
        const {
            onDateCancel,
            user,
            onChangeValues,
            updatingValues,
            onCancelChanges,
            onConfirmChanges,
            updatingMode,
            area,
            type
        } = this.props;
        // console.log(area.value);

        let inputs = {
            mileage: {
                label: "Mileage",
                type: "text"
            },
            bataType: {
                label: "Bata Type",
                type: "ajax_dropdown",
                link: "bata_type",
                where: {
                    users:user
                }
            }
        };

        let structure = [['mileage','bataType']]

        if(updatingMode===2){
            inputs['route'] = {
                label: "Route",
                type: "ajax_dropdown",
                link: "route",
                where: {
                    ar_id : area,
                    users : user,
                    route_type: type=="sales"? 0:1
                }
            }

            structure.push(['route']);

        }

        if(updatingMode ===3){
            inputs['jointFieldWorkers'] = {
                label:"Joint field workers",
                type:'multiple_ajax_dropdown',
                link: 'user',
                where: {
                    u_tp_id : AREA_SALES_MANAGER_TYPE,
                    users : user
                },
                multiple: true
            }

            structure.push(['jointFieldWorkers']);
        }

        return (
            <Modal onClose={onDateCancel} open={updatingMode > 0}>
                <CrudForm
                    title="Change the day"
                    inputs={inputs}
                    structure={structure}
                    onInputChange={onChangeValues}
                    values={updatingValues}
                    onSubmit={onConfirmChanges}
                    onClear={onCancelChanges}
                    mode="create"
                    disableSearch
                />
            </Modal>
        );
    }
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(withStyles(styles)( withRouter (ItineraryPage)));
