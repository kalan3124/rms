import React, { Component, Fragment } from 'react';
import { connect } from 'react-redux';
import {Link} from "react-router-dom";

import Typography from '@material-ui/core/Typography';
import Grid from '@material-ui/core/Grid';
import Divider from '@material-ui/core/Divider';
import Paper from '@material-ui/core/Paper';
import Toolbar from '@material-ui/core/Toolbar';
import withStyles from '@material-ui/core/styles/withStyles';
import Button from '@material-ui/core/Button';
import Table from '@material-ui/core/Table';
import TableHead from '@material-ui/core/TableHead';
import TableRow from '@material-ui/core/TableRow';
import HighlightOffIcon from '@material-ui/icons/HighlightOff';
import AddIcon from '@material-ui/icons/Add';
import SaveIcon from '@material-ui/icons/Save';
import EditIcon from '@material-ui/icons/Edit';
import CancelIcon from '@material-ui/icons/Cancel';
import CloudUploadIcon from '@material-ui/icons/CloudUpload';
import TableCell from '@material-ui/core/TableCell';
import TableBody from '@material-ui/core/TableBody';
import Modal from '@material-ui/core/Modal';

import { changeTeam,changeDivision,loadData, openForm, closeForm, changeFormData, changeData, saveData } from '../../../actions/Medical/StandardItineraryPage';
import { MEDICAL_REP_TYPE, MEDICAL_FIELD_MANAGER_TYPE, PRODUCT_SPECIALIST_TYPE } from '../../../constants/config';
import Layout from '../../App/Layout';
import AjaxDropdown from '../../CrudPage/Input/AjaxDropdown';
import CrudForm from '../../CrudPage/CrudForm';
import MultipleAjaxDropdown from "../../CrudPage/Cell/MultipleAjaxDropdown";

const styles = theme => ({
    grow: {
        flexGrow: 1
    },
    widthThirty: {
        width: '50vw'
    },
    button: {
        float: 'right',
        marginLeft: theme.spacing.unit
    },
    padding: {
        padding: theme.spacing.unit
    },
    buttonIcon: {
        marginRight: theme.spacing.unit
    },
    blackButton:{
        float: 'right',
        marginLeft: theme.spacing.unit,
        background:theme.palette.common.black,
        color:theme.palette.common.white+" !important",
    },
    darkCell: {
        background: theme.palette.common.black,
        color: theme.palette.common.white
    },
    dropdownWrapper: {
        width: "50%",
        padding: theme.spacing.unit
    },
    modal: {
        backgroundColor: "rgba(0, 0, 0, 0.5)",
        paddingBottom: 40,
        overflow: "auto"
    }
})

export const mapStateToDispatch = state => ({
    ...state.StandardItineraryPage
})

export const mapDispatchToProps = dispatch => ({
    onRepChange: rep => dispatch(loadData(rep)),
    onDivisionChange: division => dispatch(changeDivision(division)),
    onTeamChange: team => dispatch(changeTeam(team)),
    onFormOpen: () => dispatch(openForm()),
    onFormClose: () => dispatch(closeForm()),
    onChangeFormData: formData => dispatch(changeFormData(formData)),
    onFormClear: () => dispatch(changeFormData({})),
    onDataChange: data => dispatch(changeData(data)),
    onSave: (rep, data) => dispatch(saveData(rep, data))
})

class StandardItinerary extends Component {

    constructor(props) {
        super(props);

        this.handleSubmitButtonClick = this.handleSubmitButtonClick.bind(this);
        this.handleSaveButtonClick = this.handleSaveButtonClick.bind(this);
        this.handleCancelClick = this.handleCancelClick.bind(this);
    }

    handleSaveButtonClick() {
        const { rep, data, onSave } = this.props;

        onSave(rep, data)
    }

    handleSubmitButtonClick() {
        const { data, formData, onFormClear, onFormClose, onDataChange } = this.props;

        const modedData = { ...data };

        modedData[formData.date_number] = { ...formData };


        onFormClear();
        onFormClose();

        onDataChange(modedData);

    }

    handleEditButtonClick(date) {
        const { onFormOpen, onChangeFormData, data } = this.props;

        onChangeFormData(data[date]);
        onFormOpen();
    }

    renderAreasCell(value) {
        return value.map((area, index) => (
            <Fragment key={index} >
                <span >{area.label}</span>
                <Divider />
            </Fragment>
        ))
    }

    handleDeleteButtonClick(date) {
        const { data, onDataChange } = this.props;

        const modedData = { ...data };

        delete modedData[date];

        onDataChange(modedData);
    }

    handleCancelClick(){
        const {division,rep,onRepChange} = this.props;

        onRepChange(rep);
        onDivisionChange(division);
    }

    renderRows() {

        const { data } = this.props;

        return Object.keys(data).map(date => (
            <TableRow key={date}>
                <TableCell>{date}</TableCell>
                <TableCell>{data[date].description}</TableCell>
                <TableCell><MultipleAjaxDropdown values={data[date].areas} /></TableCell>
                <TableCell><MultipleAjaxDropdown values={data[date].chemists} /></TableCell>
                <TableCell><MultipleAjaxDropdown values={data[date].doctors} /></TableCell>
                <TableCell><MultipleAjaxDropdown values={data[date].otherHospitalStaffs} /></TableCell>
                <TableCell>{data[date].mileage||null}</TableCell>
                <TableCell>{data[date].bata?data[date].bata.label:null}</TableCell>
                <TableCell>
                    <EditIcon onClick={e => this.handleEditButtonClick(date)} />
                </TableCell>
                <TableCell>
                    <HighlightOffIcon onClick={e => this.handleDeleteButtonClick(date)} />
                </TableCell>
            </TableRow>
        ));
    }

    renderTable() {
        const {classes,data} = this.props;

        if(!Object.keys(data).length) return null;

        return (
            <Table>
                <TableHead>
                    <TableRow>
                        <TableCell className={classes.darkCell}>Date</TableCell>
                        <TableCell className={classes.darkCell}>Description</TableCell>
                        <TableCell className={classes.darkCell}>Areas</TableCell>
                        <TableCell className={classes.darkCell}>Chemists</TableCell>
                        <TableCell className={classes.darkCell}>Doctors</TableCell>
                        <TableCell className={classes.darkCell}>Other Hospital Staffs</TableCell>
                        <TableCell className={classes.darkCell}>Mileage</TableCell>
                        <TableCell className={classes.darkCell}>Bata</TableCell>
                        <TableCell className={classes.darkCell}>Update</TableCell>
                        <TableCell className={classes.darkCell}>Delete</TableCell>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {this.renderRows()}
                </TableBody>
            </Table>
        );
    }

    render() {
        const { classes,team,rep,division,onDivisionChange,onTeamChange ,onRepChange, formOpen, onFormClose, onFormOpen, formData, onChangeFormData, onFormClear, data } = this.props;
        return (
            <Layout sidebar>
                <Grid container>
                    <Grid item>
                        <Modal
                            aria-labelledby="simple-modal-title"
                            aria-describedby="simple-modal-description"
                            open={formOpen}
                            onClose={onFormClose}
                            key={rep?rep.value:0}
                            className={classes.modal}
                        >
                                <CrudForm
                                    title="Date"
                                    inputs={{
                                        date_number: {
                                            label: "Date Number",
                                            type: "number"
                                        },
                                        mileage: {
                                            label: "Mileage",
                                            type: "text"
                                        },
                                        bata: {
                                            label: "Bata Type",
                                            type: "ajax_dropdown",
                                            link:"bata_type",
                                            where:{
                                                user:rep
                                            }
                                        },
                                        areas: {
                                            label: "Selected Areas",
                                            type: 'multiple_ajax_dropdown',
                                            link: "sub_town",
                                            multiple: true,
                                            where:{
                                                'u_id':'{u_id}'
                                            },
                                            otherValues:{
                                                'u_id':rep
                                            }
                                        },
                                        chemists: {
                                            label: "Chemists",
                                            type: "multiple_ajax_dropdown",
                                            link: "chemist",
                                            multiple: true
                                        },
                                        doctors: {
                                            label: "Doctors",
                                            type: "multiple_ajax_dropdown",
                                            link: "doctor",
                                            multiple: true
                                        },
                                        otherHospitalStaffs: {
                                            label: "Other Hospital Staffs",
                                            type: "multiple_ajax_dropdown",
                                            link: "other_hospital_staff",
                                            multiple: true
                                        },
                                        description: {
                                            label: "Description",
                                            type: "text"
                                        }
                                    }}
                                    structure={[["date_number", "description"],[ "areas","chemists"],["doctors","otherHospitalStaffs"], ["bata", "mileage"]]}
                                    onInputChange={onChangeFormData}
                                    values={formData}
                                    onSubmit={this.handleSubmitButtonClick}
                                    onClear={onFormClear}
                                    mode="update"
                                    disableSearch
                                />
                        </Modal>
                        <Paper className={classes.padding} >
                            <Typography align="center" variant="h6">Standard Itinerary</Typography>
                            <Divider />
                            <Toolbar>
                                <Grid className={classes.padding} item md={2}>
                                    <AjaxDropdown  value={division} onChange={onDivisionChange} label="Division" link="division" />
                                </Grid>
                                <Grid className={classes.padding} item md={3}>
                                    <AjaxDropdown  value={team} onChange={onTeamChange} where={{ divi_id:'{divi_id}' }} otherValues={{ divi_id:division }} label="Team" link="team" />
                                </Grid>
                                <Grid className={classes.padding} item md={3}>
                                    <AjaxDropdown where={{divi_id:'{division}',tm_id:'{team}',u_tp_id:MEDICAL_REP_TYPE+'|'+MEDICAL_FIELD_MANAGER_TYPE+'|'+PRODUCT_SPECIALIST_TYPE}} otherValues={{ division:division,team:team }} value={rep} onChange={onRepChange} label="Rep/PS or Field Manage" link="user" />
                                </Grid>
                                <div className={classes.grow}/>
                                <Button component={Link} to="/medical/other/upload_csv/standard_itinerary"  className={classes.blackButton} variant="contained" color="default">
                                    <CloudUploadIcon className={classes.buttonIcon} />
                                    Upload
                                </Button>
                                <Button onClick={this.handleCancelClick} className={classes.button} variant="contained" color="secondary">
                                    <CancelIcon className={classes.buttonIcon} />
                                    Cancel
                                </Button>
                                <Button onClick={this.handleSaveButtonClick} className={classes.button} variant="contained" color="primary">
                                    <SaveIcon className={classes.buttonIcon} />
                                    Save
                                </Button>
                                <Button onClick={onFormOpen} className={classes.button} variant="contained" color="default">
                                    <AddIcon className={classes.buttonIcon} />
                                    Add
                                </Button>
                            </Toolbar>
                            <Divider />
                            {this.renderTable()}
                        </Paper>
                    </Grid>
                </Grid>
            </Layout>
        );
    }
}

export default connect(mapStateToDispatch, mapDispatchToProps)(withStyles(styles)(StandardItinerary))
