import React , {Component} from 'react';
import { connect } from 'react-redux';
import { Link } from "react-router-dom";
import Typography from '@material-ui/core/Typography';

import Layout from '../../App/Layout';
import Divider from "@material-ui/core/Divider";
import Grid from "@material-ui/core/Grid";
import withStyles from "@material-ui/core/styles/withStyles";
import Toolbar from "@material-ui/core/Toolbar";
import Button from "@material-ui/core/Button"; 
import SearchAndCheckPanel from './SearchAndCheckPanel';
import PersonIcon from '@material-ui/icons/Person';
import AccountBalanceIcon from '@material-ui/icons/AccountBalance';
import CloudUploadIcon from "@material-ui/icons/CloudUpload";
import { addTeam, addUser, removeTeam, removeUser, fetchUsers, fetchTeam, submit, fetchTeamsByUser } from '../../../actions/Medical/UserTeamAllocation';

const mapStateToProps = state =>({
    ...state.UserTeamAllocation
});

const mapDispatchToProps = dispatch=>({
    onAddTeam:(team)=>dispatch(addTeam(team)),
    onAddUser:user=>dispatch(addUser(user)),
    onRemoveTeam:(team)=>dispatch(removeTeam(team)),
    onRemoveUser:(user)=>dispatch(removeUser(user)),
    onSearchUser:(keyword)=>dispatch(fetchUsers(keyword)),
    onSearchTeam:(keyword)=>dispatch(fetchTeam(keyword)),
    onSubmit:(users,teams)=>dispatch(submit(users,teams)),
    onLoadTeamsByUser:userId=>dispatch(fetchTeamsByUser(userId))
});

const styler = withStyles(theme=>({
    padding: {
        padding: theme.spacing.unit*2
    },
    dense:{
        flexGrow:1
    },userField: {
        padding: theme.spacing.unit*2
    },zIndex: {
        zIndex: 1200
    },
    button: {
        margin: theme.spacing.unit
    }
}))

class UserTeamAllocation extends Component {

    constructor(props){
        super(props);
        this.handleCheckTeam = this.handleCheckTeam.bind(this);
        this.handleCheckUser = this.handleCheckUser.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleSearchTeam = this.handleSearchTeam.bind(this);
        this.handleSearchUser = this.handleSearchUser.bind(this);

        this.state = {
            count: 0
        }
    }

    handleCheckTeam(team,checked){
        const {
            onAddTeam,
            onRemoveTeam
        } = this.props;

        if(!checked){
            onRemoveTeam(team);
        } else {
            onAddTeam(team);
        }
    }

    handleCheckUser(user,checked){
        
        const {
            onAddUser,
            onRemoveUser,
            onLoadTeamsByUser
        } = this.props;

        if(!checked){
            onRemoveUser(user);
            this.setState({count: this.state.count - 1})
        } else {
            this.setState({count: this.state.count + 1})
            onAddUser(user);
            onLoadTeamsByUser(user.value);
        }
        
       
    }

    handleSubmit(){
        const {selectedTeams,selectedUsers,onSubmit} = this.props;
        
            onSubmit(selectedUsers,selectedTeams); 
    }

    handleSearchTeam(keyword){
        const {onSearchTeam} = this.props;

        onSearchTeam(keyword);
    }

    handleSearchUser(keyword){
        const {onSearchUser} = this.props;

        onSearchUser(keyword);
    }

    render(){
        const {
            teamKeyword,
            userKeyword,
            teams,
            users,
            selectedTeams,
            selectedUsers,
            classes
        } = this.props;

        return (
            <Layout sidebar >
                <Toolbar variant="dense" className={classes.zIndex}>
                    <Typography variant="h5" >User Team Allocation</Typography>
                    <div className={classes.dense} />
                    <Button variant="contained" onClick={this.handleSubmit} color="secondary">Submit</Button>
                </Toolbar>
                <Divider/>
                <Grid container>
                    <Grid className={classes.padding} item md={6}>
                        <SearchAndCheckPanel 
                            label="USER"
                            icon={<PersonIcon />}
                            keyword={userKeyword}
                            results={users}
                            checked={selectedUsers}
                            onSearch={this.handleSearchUser}
                            onCheck={this.handleCheckUser}
                        />
                    </Grid>
                    <Grid className={classes.padding} item md={6}>
                        <SearchAndCheckPanel 
                            label="Teams"
                            icon={<AccountBalanceIcon />}
                            keyword={teamKeyword}
                            results={teams}
                            checked={selectedTeams}
                            onSearch={this.handleSearchTeam}
                            onCheck={this.handleCheckTeam}
                        />
                    </Grid>
                </Grid>
            </Layout>
        )
    }
}

export default connect(mapStateToProps,mapDispatchToProps) ( styler (UserTeamAllocation));