import React, {Component} from "react";
import {connect} from 'react-redux';

import Typography from "@material-ui/core/Typography";
import Paper from "@material-ui/core/Paper";
import Toolbar from "@material-ui/core/Toolbar";
import Grid from "@material-ui/core/Grid";
import Divider from "@material-ui/core/Divider";
import Button from "@material-ui/core/Button";
import withStyles from "@material-ui/core/styles/withStyles";
import SearchPanel from "./SearchPanel";
import Link from "react-router-dom/Link";
import { th } from "date-fns/esm/locale";
// import PropTypes from "prop-types";

import Layout from "../../App/Layout";
import { fetchDoctors, fetchTowns, selectTown, selectDoctor, fetchTownsByDoctor, saveDoctorsAndTowns, clearSelections } from "../../../actions/Medical/DoctorTown";

const mapStateToProps = state =>({
    ...state.DoctorTown
});

const mapDispatchToProps = dispatch =>({
    onSearchDoctors:(keyword)=>dispatch(fetchDoctors(keyword)),
    onSearchTowns:(keyword)=>dispatch(fetchTowns(keyword)),
    onSelectTown:(town)=>dispatch(selectTown(town)),
    onSelectDoctor:(doctor)=>dispatch(selectDoctor(doctor)),
    onLoadTownsByDoctor:(doctor)=>dispatch(fetchTownsByDoctor(doctor)),
    onSave:(doctors,towns)=>dispatch(saveDoctorsAndTowns(doctors,towns)),
    onClear:()=>dispatch(clearSelections())
})

const styles = theme=>({
    grow:{
        flexGrow:1
    },
    button:{
        marginLeft:theme.spacing.unit*3
    }
});

class DoctorTown extends Component{

    componentDidMount(){

        this.props.onSearchDoctors("");
        this.props.onSearchTowns("");
        this.handleSelectDoctor = this.handleSelectDoctor.bind(this);
        this.handleSave = this.handleSave.bind(this);
    }

    handleSelectDoctor(doctor){
        const {onSelectDoctor,onLoadTownsByDoctor,doctors} = this.props;

        onSelectDoctor(doctor);

        if(typeof doctors[doctor.value]!=='undefined'){
            onLoadTownsByDoctor(doctor);
        }
    }

    handleSave(){
        const {selectedDoctors,selectedTowns,onSave} = this.props;

        onSave(selectedDoctors,selectedTowns);
    }

    render(){
        const {doctors,towns,selectedDoctors,selectedTowns,classes,onSearchDoctors , onSearchTowns,onSelectTown,onClear} = this.props;

        return (
            <Layout sidebar>
                <Paper className={classes.padding}>
                    <Toolbar>
                        <Typography variant="h5" align="center">Doctor Town Allocations</Typography>
                        <div className={classes.grow}/>
                        <Button className={classes.button} component={Link} to="/medical/other/upload_csv/doctor_sub_town" variant="contained" color="secondary">Upload CSV</Button>
                        <Button className={classes.button} onClick={this.handleSave} variant="contained" color="primary">Save</Button>
                        <Button className={classes.button} variant="contained" onClick={onClear} color="secondary">Cancel</Button>
                    </Toolbar>
                    <Divider/>
                    <Grid container>
                        <Grid item md={6}>
                            <SearchPanel
                                title="Doctors"
                                items={doctors}
                                selectedItems={selectedDoctors}
                                onCheck={this.handleSelectDoctor}
                                onSearch={onSearchDoctors}
                            />
                        </Grid>
                        <Grid item md={6}>
                            <SearchPanel
                                title="Sub Towns"
                                items={towns}
                                selectedItems={selectedTowns}
                                onCheck={onSelectTown}
                                onSearch={onSearchTowns}
                            />
                        </Grid>
                    </Grid>
                </Paper>
            </Layout>
        );
    }
}

export default withStyles(styles)( connect(mapStateToProps,mapDispatchToProps) (DoctorTown));