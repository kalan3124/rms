import React, { Component } from 'react';
import { connect } from 'react-redux';
import Timetable from 'react-timetable-events'
import moment from 'moment';

import Paper from '@material-ui/core/Paper'
import Divider from '@material-ui/core/Divider';
import Typography from '@material-ui/core/Typography';
import Grid from '@material-ui/core/Grid';
import Button from '@material-ui/core/Button';
import withStyles from '@material-ui/core/styles/withStyles';
import green from '@material-ui/core/colors/green'
import Add from '@material-ui/icons/Add';
import Save from '@material-ui/icons/Save';
import HighlightOff from '@material-ui/icons/HighlightOff';
import Modal from '@material-ui/core/Modal';

import Layout from '../../App/Layout';
import AjaxDropdown from '../../CrudPage/Input/AjaxDropdown';
import CrudForm from '../../CrudPage/CrudForm';
import weekDays from '../../../constants/weekDays';
import { doctorChange, addTime, editNewValues, modalClose, changeShedules, fetchTimeTable, saveTimeTable } from '../../../actions/Medical/DoctorTimeTable';

const styles = theme => ({
    padding: {
        padding: theme.spacing.unit
    },
    button: {
        margin: theme.spacing.unit,
        float: 'right',
        marginTop: theme.spacing.unit * 2
    },
    greenButton: {
        margin: theme.spacing.unit,
        float: 'right',
        marginTop: theme.spacing.unit * 2,
        background: green[500]
    },
    modal: {
        width: '40vw',
        minWidth: '400px',
        marginLeft: '30vw',
        marginTop: '70px'
    },
    event:{
        background:theme.palette.primary.dark,
        fontSize:'.75em',
        padding:theme.spacing.unit,
        border:'solid 2px '+theme.palette.grey[500],
        position:'absolute',
        width:'80%'
    },
    eventClose:{
        position:'absolute',
        right:0,
        top:0,
        cursor:'pointer',
        '&:hover':{
            color:theme.palette.grey[600]
        }
    }
})

const mapDispatchToProps = dispatch => ({
    onDoctorChange: doctor => dispatch(fetchTimeTable(doctor)),
    onModalOpen: () => dispatch(addTime()),
    onEditNewValues: newValues => dispatch(editNewValues(newValues)),
    onModalClose: () => dispatch(modalClose()),
    onChangeShedules: (shedules, lastId) => dispatch(changeShedules(shedules, lastId)),
    onSave:(doc,shedules)=>dispatch(saveTimeTable(doc,shedules))
})

const mapStateToProps = state => ({
    ...state,
    ...state.DoctorTimeTable
})

class DoctorTimeTable extends Component {

    constructor(props) {
        super(props);

        this.handleDoctorChange = this.handleDoctorChange.bind(this)
        this.handleModalOpen = this.handleModalOpen.bind(this)
        this.handleChangeInputs = this.handleChangeInputs.bind(this)
        this.handleModalClose = this.handleModalClose.bind(this)
        this.handleAddShedule = this.handleAddShedule.bind(this)
        this.renderEvent = this.renderEvent.bind(this)
        this.handleSave = this.handleSave.bind(this)
    }

    handleDoctorChange(doctor) {
        this.props.onDoctorChange(doctor)
    }

    handleModalOpen() {
        this.props.onModalOpen()
    }

    handleModalClose() {
        this.props.onModalClose()
    }

    handleChangeInputs(newValues) {
        this.props.onEditNewValues(newValues)
    }

    handleAddShedule() {
        const { newValues, shedules, lastId, onChangeShedules } = this.props;

        let modifiedShedules = { ...shedules };

        let dayName = newValues.weekDay.label.toLowerCase();

        modifiedShedules[dayName] = [...modifiedShedules[dayName], {
            id: lastId + 1,
            name: newValues.institution.label,
            value: newValues.institution.value,
            startTime: newValues.startTime ? newValues.startTime : moment(),
            endTime: newValues.endTime ? newValues.endTime : moment(),
            type: 'custom',
            dayName
        }];

        onChangeShedules(modifiedShedules, lastId + 1)
    }

    handleRemoveIconClick(event){
        const {shedules,lastId,onChangeShedules} = this.props;

        let modedShedules = {...shedules};

        modedShedules[event.dayName] = modedShedules[event.dayName].filter(e=>e.id!=event.id);

        onChangeShedules(modedShedules,lastId)
    }

    handleSave(){
        const {onSave,doctor,shedules} = this.props;
        
        onSave(doctor,shedules)
    }

    render() {
        const { classes, doctor, popupOpen, newValues, shedules } = this.props;
        
        return (
            <Layout sidebar >
                <Paper className={classes.padding}>
                    <Typography variant="h6" align="center" >Doctor Time Table</Typography>
                    <Divider />
                    <Grid container>
                        <Grid sm={4} item>
                            <AjaxDropdown onChange={this.handleDoctorChange} label="Doctor" value={doctor} link="doctor" />
                        </Grid>
                        <Grid sm={8} item>
                            <Button onClick={this.handleSave} className={classes.button} variant="contained" color="primary" > <Save /> Save</Button>
                            <Button onClick={this.handleModalOpen} className={classes.greenButton} variant="contained" color="primary" > <Add /> Add</Button>
                        </Grid>
                    </Grid>
                    <Divider />
                    <Timetable
                        events={shedules}
                        renderEvent={this.renderEvent}
                    />
                    <Modal open={popupOpen} >
                        <Paper className={classes.modal}>
                            <CrudForm
                                title={"Shedule "}
                                inputs={{
                                    institution: {
                                        label: "Institution",
                                        link: "institution",
                                        type: "ajax_dropdown"
                                    },
                                    startTime: {
                                        label: "Start Time",
                                        type: 'time'
                                    },
                                    endTime: {
                                        label: "End Time",
                                        type: 'time'
                                    },
                                    weekDay: {
                                        label: "Day",
                                        type: "select",
                                        options: weekDays
                                    }
                                }}
                                disableSearch
                                structure={[
                                    ["weekDay", 'startTime', 'endTime'],
                                    ["institution"]
                                ]}
                                mode="create"
                                values={newValues}
                                onClear={this.handleModalClose}
                                onSubmit={this.handleAddShedule}
                                onInputChange={this.handleChangeInputs}
                            />
                        </Paper>
                    </Modal>
                </Paper>
            </Layout>
        )
    }

    renderEvent(event, defaultAttributes, styles) {
        const {classes} = this.props;

        return (
            <div
                style={defaultAttributes.style}
                className={classes.event}
                title={event.name}
                key={event.id}>
                <div className={classes.eventClose}>
                    <HighlightOff onClick={e=>this.handleRemoveIconClick(event)} />
                </div>
                <span className={styles.event_info}>{event.name}</span><br/>
                <span className={styles.event_info}>
                    {event.startTime.format('HH:mm')} - {event.endTime.format('HH:mm')}
                </span>
            </div>
        )
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(DoctorTimeTable));