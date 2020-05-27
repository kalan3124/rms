import React, {Component} from "react";
import {connect} from 'react-redux';
import PropTypes from "prop-types";
import withStyles from "@material-ui/core/styles/withStyles";
import SupervisorIcon from "@material-ui/icons/SupervisedUserCircle";
import Typography from "@material-ui/core/Typography";
import Divider from "@material-ui/core/Divider";
import Toolbar from "@material-ui/core/Toolbar";
import Button from "@material-ui/core/Button";
import Paper from "@material-ui/core/Paper";
import ListItem from "@material-ui/core/ListItem";
import List from "@material-ui/core/List";
import ListItemText from "@material-ui/core/ListItemText";
import ListItemSecondaryAction from "@material-ui/core/ListItemSecondaryAction";
import Checkbox from "@material-ui/core/Checkbox";
import Grid from "@material-ui/core/Grid";
import TextField from "@material-ui/core/TextField"; 
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import { changeUser, fetchSections, selectType, changeValues, cloneUser } from "../../../actions/Medical/UserClone";
import Layout from '../../App/Layout';

const styles = theme=>({
    grow:{
        flexGrow:1
    },
    margin:{
        margin:theme.spacing.unit,
        width:'25vw'
    },
    padding:{
        padding:theme.spacing.unit
    },
    input:{
        margin:theme.spacing.unit
    }
});

const mapStateToProps = state=>({
    ...state.UserClone
});

const mapDispatchToProps = dispatch=>({
    onChangeUser: user=>dispatch(changeUser(user)),
    onLoad:()=>dispatch(fetchSections()),
    onSelect:(id)=>dispatch(selectType(id)),
    onChangeValue:(name,value)=>dispatch(changeValues(name,value)),
    onSubmit:(values,sectionIds,id)=>dispatch(cloneUser(values,sectionIds,id))
})

class UserClone extends Component{

    constructor(props){
        super(props);

        this.handleUserChange = this.handleUserChange.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
        props.onLoad();
    }

    handleUserChange(user){
        const {onChangeUser} = this.props;

        onChangeUser(user);
    }

    handleSelect(id){
        const {onSelect} = this.props;
        return e =>{

            onSelect(id);
        }
    }

    handleValueChange(name){

        const {onChangeValue} = this.props;
        return e =>{
            let value = name!='email'&&name!='password'&&name!='userName'?e.currentTarget.value.toUpperCase():e.currentTarget.value;

            onChangeValue(name,value)
        }
    }

    handleSubmit(){
        const {user,values,sectionIds,onSubmit} = this.props;

        onSubmit(values,sectionIds,user?user.value:undefined);
    }

    renderList(){
        const {sections,sectionIds} = this.props;

        return Object.keys(sections).map((sectionId)=>(
            <ListItem divider key={sectionId} >
                <ListItemSecondaryAction>
                    <Checkbox onChange={this.handleSelect(sectionId)} checked={sectionIds.includes(sectionId)} />
                </ListItemSecondaryAction>
                <ListItemText>
                    {sections[sectionId]}
                </ListItemText>
            </ListItem>
        ));
    }

    render(){
        const {classes,user,values,display} = this.props;
        return (
            <Layout sidebar >
                <Toolbar>
                    <Typography variant="h6" align="left">User Clone</Typography>
                    <div className={classes.grow}/>
                    <div className={classes.margin}>
                        <AjaxDropdown value={user} onChange={this.handleUserChange} link="user" label="User" />
                    </div>
                </Toolbar>
                <Divider/>
                {display?
                <Grid className={classes.padding} container>
                    <Grid className={classes.padding} item md={6}>
                        <Paper className={classes.paper} >
                            <List>
                                {this.renderList()}
                            </List>
                        </Paper>
                    </Grid>
                    <Grid className={classes.padding} item md={5}>
                        <Paper className={classes.padding}>
                            <Typography align="center" variant="h5">Informations</Typography>
                            <Divider/>
                            <Grid container>
                                <Grid item md={6}>
                                    <TextField onChange={this.handleValueChange('name')} className={classes.input} value={values.name?values.name:""} label="Name" variant="outlined" margin="dense" />
                                </Grid>
                                <Grid item md={6}>
                                    <TextField onChange={this.handleValueChange('empCode')} className={classes.input} value={values.empCode?values.empCode:""} label="Employee Code" variant="outlined" margin="dense" />
                                </Grid>
                            </Grid>
                            <Grid container>
                                <Grid item md={6}>
                                    <TextField onChange={this.handleValueChange('userName')} className={classes.input} value={values.userName?values.userName:""} label="User Name" variant="outlined" margin="dense" />
                                </Grid>
                                <Grid item md={6}>
                                    <TextField onChange={this.handleValueChange('password')} className={classes.input} type="password" value={values.password?values.password:""} label="Password" variant="outlined" margin="dense" />
                                </Grid>
                            </Grid>
                            <Grid container>
                                <Grid item md={6}>
                                    <TextField onChange={this.handleValueChange('email')} className={classes.input} value={values.email?values.email:""} label="Email" variant="outlined" margin="dense" />
                                </Grid>
                                <Grid item md={6}>
                                    <TextField onChange={this.handleValueChange('contact')} className={classes.input} value={values.contact?values.contact:""} label="Contact Number" variant="outlined" margin="dense" />
                                </Grid>
                            </Grid>
                            <Toolbar variant="dense">
                                <div className={classes.grow}/>
                                <Button onClick={this.handleSubmit} color="secondary" variant="contained">
                                    Clone
                                    <SupervisorIcon />
                                </Button>
                            </Toolbar>
                        </Paper>
                    </Grid>
                </Grid>
                :null}
            </Layout>
        );
    }
}

UserClone.propTypes = {
    classes:PropTypes.shape({
        grow:PropTypes.string
    })
}

export default connect(mapStateToProps,mapDispatchToProps)( withStyles(styles) (UserClone));