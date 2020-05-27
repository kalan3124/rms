import React, { Component, Fragment } from "react";
import { connect } from "react-redux";
import PropTypes from "prop-types";

import Typography from "@material-ui/core/Typography";
import Divider from "@material-ui/core/Divider";
import Grid from "@material-ui/core/Grid";
import withStyles from "@material-ui/core/styles/withStyles";
import SearchIcon from "@material-ui/icons/Search";
import Button from "@material-ui/core/Button";
import List from "@material-ui/core/List";
import Toolbar from "@material-ui/core/Toolbar";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import ListItemSecondaryAction from "@material-ui/core/ListItemSecondaryAction";
import Collapse from "@material-ui/core/Collapse";

import Layout from "../../App/Layout";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import DatePicker from "../../CrudPage/Input/DatePicker";
import CrudForm from "../../CrudPage/CrudForm";
import { changeUser, changeToDate, changeFromDate, loadData, selectToEdit, editDoctor, save, cancel } from "../../../actions/Medical/DoctorApprove";


const styles = theme => ({
    inputWrapper: {
        paddingRight: theme.spacing.unit * 3
    },
    grow: {
        flexGrow: 1
    },
    margin:{
        margin:theme.spacing.unit
    }
});

const mapStateToProps = state => ({
    ...state.DoctorApprove
});

const mapDispatchToProps = dispatch => ({
    onUserChange: user => dispatch(changeUser(user)),
    onToDateChange: date => dispatch(changeToDate(date)),
    onFromDateChange: date => dispatch(changeFromDate(date)),
    onSearch: (user, toDate, fromDate) => dispatch(loadData(user, toDate, fromDate)),
    onSelectToEdit: key=>dispatch(selectToEdit(key)),
    onChangeDoctor:(key,values) => dispatch(editDoctor(key,values)),
    onSubmit:(key,values,callback)=>dispatch(save(key,values,callback)),
    onCancel:(key,callback)=>dispatch(cancel(key,callback))
});

class DoctorApprove extends Component {

    constructor(props) {
        super(props);

        this.handleSearchButtonClick = this.handleSearchButtonClick.bind(this);
        this.handleSelectForEditButtonClick = this.handleSelectForEditButtonClick.bind(this);
        this.handleClearForm = this.handleClearForm.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
    }

    renderSearchForm() {
        const {
            classes,
            onUserChange,
            onToDateChange,
            onFromDateChange,
            toDate,
            fromDate,
            user
        } = this.props;

        return (
            <Toolbar variant="dense" >
                <Grid container>
                    <Grid className={classes.inputWrapper} item md={4} >
                        <AjaxDropdown value={user} link="user" onChange={onUserChange} label="MR/PS or FM" />
                    </Grid>
                    <Grid className={classes.inputWrapper} item md={3} >
                        <DatePicker value={fromDate} label="From" onChange={onFromDateChange} />
                    </Grid>
                    <Grid className={classes.inputWrapper} item md={3} >
                        <DatePicker value={toDate} label="To" onChange={onToDateChange} />
                    </Grid>
                </Grid>
                <div className={classes.grow} />
                <Button variant="contained" margin="dense" color="primary" onClick={this.handleSearchButtonClick} > <SearchIcon /> Search</Button>
            </Toolbar>
        )
    }

    renderFormList() {
        const { data,classes,updatingKey } = this.props;

        return Object.keys(data).map(key => {
            const docAprv = data[key];

            return (
                <Fragment key={key} >
                    {updatingKey!=key?
                    <ListItem dense divider >
                        <ListItemText primary={docAprv.doc_name} secondary={
                            (docAprv.sub_town ? docAprv.sub_town.label : docAprv.doc_code)+" | Created By "+
                            (docAprv.user?docAprv.user.label:"Deleted user")+" At "+docAprv.added_date
                            } />
                        <ListItemSecondaryAction>
                            <Button className={classes.margin} variant="contained" margin="dense" onClick={this.handleSelectForEditButtonClick(key)} color="primary" >Edit and Approve</Button>
                            <Button className={classes.margin} variant="contained" margin="dense" onClick={this.handleCancelApprove(key)} color="secondary" >Delete</Button>
                        </ListItemSecondaryAction>
                    </ListItem>
                    :null}
                    {this.renderForm(docAprv,key)}
                </Fragment>
            );
        })
    }

    renderForm(docAprv,key) {

        const {updatingKey} = this.props;

        return (
            <Collapse timeout="auto" unmountOnExit in={updatingKey==key} >
                <CrudForm
                    key={key}
                    values={docAprv}
                    title="Doctor"
                    mode="create"
                    disableSearch
                    onInputChange={this.handleChangeDoctor(key)}
                    inputs={{
                        'doc_code': {
                            label: "Code",
                            type: "text"
                        },
                        'doc_name': {
                            label: "Name",
                            type: "text"
                        },
                        'slmc_no': {
                            label: "SLMC No",
                            type: "text"
                        },
                        'phone_no': {
                            label: "Phone No",
                            type: "text"
                        },
                        'mobile_no': {
                            label: "Mobile No",
                            type: "text"
                        },
                        'gender': {
                            label: "Gender",
                            type: "select",
                            options: {
                                1: "Male",
                                2: "Female"
                            }
                        },
                        'date_of_birth': {
                            label: "DOB",
                            type: "date"
                        },
                        'doctor_speciality': {
                            label: "Speciality",
                            type: "ajax_dropdown",
                            link: "doctor_speciality"
                        },
                        'doctor_class': {
                            label: "Class",
                            type: "ajax_dropdown",
                            link: "doctor_class"
                        },
                        'sub_town': {
                            label: "Sub Town",
                            type: "ajax_dropdown",
                            link: "sub_town"
                        },
                        'institution': {
                            label: "Institution",
                            type: "ajax_dropdown",
                            link: "institution"
                        },
                    }}
                    structure={[
                        ['doc_name', 'doc_code', 'slmc_no'],
                        ['phone_no', 'mobile_no'],
                        ['gender', 'date_of_birth'],
                        ['doctor_speciality', 'doctor_class'],
                        ['sub_town', 'institution']
                    ]}
                    onClear={this.handleClearForm}
                    onSubmit={this.handleSubmit}
                />
            </Collapse>
        )
    }

    render() {
        return (
            <Layout sidebar>
                <Typography variant="h6">Doctor Approval</Typography>
                <Divider />
                {this.renderSearchForm()}
                <Divider />
                <List dense >
                    {this.renderFormList()}
                </List>
            </Layout>
        );
    }

    handleSubmit(){
        const {updatingKey,data,onSubmit} = this.props;

        onSubmit(updatingKey,data[updatingKey],this.handleClearForm);
    }

    handleClearForm(){
        this.handleSelectForEditButtonClick(0)(null);
        this.handleSearchButtonClick();
    }

    handleSelectForEditButtonClick(key){
        const {onSelectToEdit} = this.props;

        return e=>{
            onSelectToEdit(key);
        }
    }

    handleCancelApprove(key){
        const {onCancel} = this.props;

        return e=>{
            onCancel(key,this.handleClearForm);
        }
    }

    handleSearchButtonClick() {
        const { onSearch, user, toDate, fromDate } = this.props;

        onSearch(user, toDate, fromDate);
    }

    handleChangeDoctor(key){
        const {onChangeDoctor} = this.props;

        return values=>{
            onChangeDoctor(key,values);
        }
    }
}

DoctorApprove.propTypes = {
    classes: PropTypes.shape({
        inputWrapper: PropTypes.string,
        grow: PropTypes.string
    }),
    updatingKey: PropTypes.oneOfType([ PropTypes.number,PropTypes.string]),
    onSubmit: PropTypes.func
}

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(DoctorApprove));