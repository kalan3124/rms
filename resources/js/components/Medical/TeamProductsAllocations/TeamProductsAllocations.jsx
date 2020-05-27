import React, { Component } from 'react';
import { connect } from 'react-redux';
import {Link} from "react-router-dom";

import AjaxDropdown from '../../CrudPage/Input/AjaxDropdown';
import Layout from '../../App/Layout'

import Typography from '@material-ui/core/Typography';
import Divider from '@material-ui/core/Divider';
import withStyles from '@material-ui/core/styles/withStyles';
import Paper from '@material-ui/core/Paper';
import Grid from '@material-ui/core/Grid';
import Button from '@material-ui/core/Button';
import Card from '@material-ui/core/Card';
import Toolbar from '@material-ui/core/Toolbar';

import Save from '@material-ui/icons/Save';
import CloudUploadIcon from "@material-ui/icons/CloudUpload";

import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';

import { fetchTeam, changeData, submitData } from '../../../actions/Medical/TeamProductAllocations';
import { alertDialog } from '../../../actions/Dialogs';

import classnames from 'classnames'


const mapStateToProps = state => ({
    ...state,
    ...state.TeamProductAllocations
})

const mapDispatchToProps = dispatch => ({
    onTeamChange: team => dispatch(fetchTeam(team)),
    onChange: (allocated, unallocated) => dispatch(changeData(allocated, unallocated)),
    onMount: (message) => dispatch(alertDialog(message, "info")),
    onSubmit: (team, allocated) => dispatch(submitData(team, allocated))
})

const styles = theme => ({
    padding: {
        padding: theme.spacing.unit
    },
    grow: {
        flexGrow:1
    },
    smallButton :{
        marginLeft: theme.spacing.unit
    },
    button: {
        margin: theme.spacing.unit,
        float: 'right',
        marginTop: theme.spacing.unit * 2,
    },
    textWhite:{
        color: theme.palette.common.white
    },
    darkCard: {
        height: '65vh',
        minWidth: '100%',
        background: theme.palette.grey[400],
        paddingTop: theme.spacing.unit,
        overflowY:"auto"
    },
    dragOver: {
        background: theme.palette.grey[300]
    },
    smallCard: {
        height: '47vh',
        scrollY: 'auto'
    },
    padding: {
        padding: theme.spacing.unit
    },
    margin: {
        margin: theme.spacing.unit
    },
    placeholder: {
        display: 'none'
    },
    scrollWrapper: {
        overflowX: "auto",
        overflowY: "hidden",
        whiteSpace: "nowrap"
    },
    scrollChild: {
        display: 'inline-block',
        minWidth: '260px',
        verticalAlign: 'top'
    }
})

class TeamProductsAllocations extends Component {

    constructor(props) {
        super(props);

        this.handleTeamChange = this.handleTeamChange.bind(this);
        this.handleDragEnd = this.handleDragEnd.bind(this);
        this.handleCancel = this.handleCancel.bind(this);
        this.handleSave = this.handleSave.bind(this);

        this.props.onMount("Drag and drop your  products from left side to right side members.")
    }

    handleTeamChange(value) {
        this.props.onTeamChange(value)
    }

    handleDragEnd(info) {
        const { unallocated, allocated, onChange } = this.props;

        if (!info.source || !info.destination) return;

        let modUnallocated = [...unallocated];
        let modAllocated = { ...allocated };

        let draggable = info.draggableId.split('-')[1];
        let source = info.source.droppableId.split('-')[1];
        let destination = info.destination.droppableId.split('-')[1];

        let item;

        if (source == '0') {
            item = modUnallocated.filter(item => item.value == draggable)[0];
            // modUnallocated = modUnallocated.filter(item => item.value != draggable);
        } else {
            item = modAllocated[source].filter(item => item.value == draggable)[0];
            modAllocated[source] = modAllocated[source].filter(item => item.value != draggable).uniqueWith("value");
        }

        if (destination == '0') {
            modUnallocated = [...modUnallocated, item].uniqueWith("value");
        } else {
            modAllocated[destination] = [...modAllocated[destination], item].uniqueWith("value");
        }

        onChange(modAllocated, modUnallocated);
    }

    handleCancel() {
        const { team, onTeamChange } = this.props;

        onTeamChange(team)
    }

    handleSave() {
        const { team, allocated, onSubmit } = this.props;

        onSubmit(team, allocated);
    }

    handleAddAllProducts(memberId){

        return e=>{
            const {allocated,unallocated,onChange} = this.props;


            onChange({...allocated,[memberId]:unallocated},unallocated);
        }
    }

    handleClearAllProducts(memberId){

        return e=>{
            const {allocated,unallocated,onChange} = this.props;


            onChange({...allocated,[memberId]:[]},unallocated);
        }
    }

    renderUnallocatedBlock(provided, snapshot) {
        const { unallocated, classes } = this.props;

        return (
            <div
                ref={provided.innerRef}>
                <Paper className={classnames(classes.darkCard, snapshot.isDraggingOver ? classes.dragOver : undefined)} >
                    {unallocated.map((item, index) => this.renderDraggableProduct(item, "0", index))}
                    <div className={classes.placeholder}>{provided.placeholder}</div>
                </Paper>
            </div>
        )
    }

    renderMember(member, index) {
        const { classes } = this.props;

        return (
            <div className={classnames(classes.margin, classes.scrollChild)} key={index} md={4}>
                <Paper className={classes.padding}>
                    <Toolbar variant="dense" >
                        <Typography variant="h6">{member.label}</Typography>
                        <div className={classes.grow}/>
                        <Button className={classes.smallButton} onClick={this.handleAddAllProducts(member.value)}  color="primary" variant="contained" size="small">All</Button>
                        <Button className={classes.smallButton} onClick={this.handleClearAllProducts(member.value)}  color="secondary" variant="contained" size="small">Clear</Button>
                    </Toolbar>
                    <Divider />
                    <Droppable droppableId={'drop-' + member.value}>
                        {(provided, snapshot) => this.renderMemberDropableArea(provided, snapshot, member.value)}
                    </Droppable>
                </Paper>
            </div>
        )
    }

    renderMemberDropableArea(provided, snapshot, memberId) {

        const { classes, allocated } = this.props;

        return (
            <div
                ref={provided.innerRef}>
                <Paper className={classnames(classes.darkCard, classes.smallCard, snapshot.isDraggingOver ? classes.dragOver : undefined)}>
                    {allocated[memberId].map((item, index) => this.renderDraggableProduct(item, memberId, index))}
                </Paper>
                <div className={classes.placeholder}>{provided.placeholder}</div>
            </div>
        )
    }

    renderDraggableProduct(item, sourceId, index) {
        return (
            <Draggable
                key={item.value}
                draggableId={sourceId + '-' + item.value}
                index={index}>
                {(provided) => this.renderProduct(provided, item.label)}
            </Draggable>
        )
    }

    renderProduct(provided, label) {
        const { classes } = this.props;

        return (
            <div
                ref={provided.innerRef}
                {...provided.draggableProps}
                {...provided.dragHandleProps}
                style={provided.draggableProps.style}>
                <Card className={classnames(classes.margin, classes.padding)}>
                    <Typography >{label}</Typography>
                </Card>
            </div>
        )
    }


    render() {
        const { classes, team, members } = this.props;

        return (
            <Layout sidebar >
                <Paper className={classes.padding}>
                    <Typography variant="h6" align="center" >Member Products Allocations</Typography>
                    <Divider />
                    <Grid container>
                        <Grid sm={4} item>
                            <AjaxDropdown value={team} onChange={this.handleTeamChange} label="Team" link="team" />
                        </Grid>
                        <Grid className={classes.textWhite} sm={8} item>
                            <Button onClick={this.handleSave} className={classes.button} variant="contained" color="primary" > <Save /> Save</Button>
                            <Button onClick={this.handleCancel} className={classes.button} variant="contained" color="secondary" > <Save /> Cancel</Button>
                            <Button component={Link} to="/medical/other/upload_csv/team_member" margin="dense" className={classes.button} variant="contained" color="secondary">
                                <CloudUploadIcon />
                                Members
                            </Button>
                            <Button component={Link} to="/medical/other/upload_csv/team_member_product" margin="dense" className={classes.button} variant="contained" color="secondary">
                                <CloudUploadIcon />
                                Member Products
                            </Button>
                        </Grid>
                    </Grid>
                    <Divider />
                    <Grid container>
                        <DragDropContext onDragEnd={this.handleDragEnd}>
                            <Grid className={classes.padding} item md={3}>
                                <Droppable droppableId="drop-0">
                                    {(provided, snapshot) => this.renderUnallocatedBlock(provided, snapshot)}
                                </Droppable>
                            </Grid>
                            <Grid className={classes.padding} item md={9}>
                                <Paper className={classnames(classes.darkCard, classes.scrollWrapper)}>
                                    {members.map((member, m) => this.renderMember(member, m))}
                                </Paper>
                            </Grid>
                        </DragDropContext>
                    </Grid>
                </Paper>
            </Layout>
        )
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(TeamProductsAllocations));