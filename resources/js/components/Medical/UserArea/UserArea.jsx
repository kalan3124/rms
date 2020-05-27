import React, { Component } from 'react';
import { connect } from 'react-redux';
import {Link} from "react-router-dom";
import Layout from '../../App/Layout';

import Grid from '@material-ui/core/Grid';
import Paper from '@material-ui/core/Paper';
import Typography from '@material-ui/core/Typography';
import Divider from '@material-ui/core/Divider';
import withStyles from '@material-ui/core/styles/withStyles';

import LeftPanel from './LeftPanel';
import RightPanel from './RightPanel';
import { addArea, removeArea,removeAll } from '../../../actions/Medical/UserArea';
import Toolbar from '@material-ui/core/Toolbar';
import Button from '@material-ui/core/Button';

const styles = theme => ({
    mainWrapper: {
        padding: theme.spacing.unit
    },
    downSection: {
        marginTop: theme.spacing.unit * 2
    },
    padding:{
        padding:theme.spacing.unit
    },
    grow:{
        flexGrow:1
    },
    marginRight:{
        marginRight: theme.spacing.unit
    }
});

export const mapStateToProps = state => ({
    ...state.UserArea,
    ...state.UserAllocation
});

export const mapDispatchToProps = dispatch => ({
    onAreaAdd:(user,area)=>dispatch(addArea(user,area)),
    onAreaRemove:(user,area)=>dispatch(removeArea(user,area)),
    onRemoveAll: (user)=>dispatch(removeAll(user))
});

class UserArea extends Component {

    constructor(props) {
        super(props);

        this.handleSelectArea = this.handleSelectArea.bind(this);
        this.handleRemoveArea = this.handleRemoveArea.bind(this);
        this.handleRemoveAllClick = this.handleRemoveAllClick.bind(this);
    }

    handleSelectArea(value,label,type){
        const {onAreaAdd,user} = this.props;

        onAreaAdd(user,{value,label,type});
    }

    handleRemoveArea(value,label,type){
        const {onAreaRemove,user} = this.props;

        onAreaRemove(user,{value,label,type});
    }


    handleRemoveAllClick(){
        const {user,onRemoveAll} = this.props;

        onRemoveAll(user);
    }

    render() {

        const { classes,user } = this.props;

        return (
            <Layout sidebar>
                <Grid alignItems="center" container>
                    <Grid item md={11}>
                        <Paper className={classes.mainWrapper}>
                            <Toolbar>
                                <Typography align="center" variant="h5">User Area Allocations</Typography>
                                <div className={classes.grow}/>
                                {user?
                                <Button onClick={this.handleRemoveAllClick} className={classes.marginRight} variant="contained" color="secondary">Delete All</Button>
                                :null}
                                <Button component={ Link } to="/medical/other/upload_csv/user_area" variant="contained" color="primary" >Upload CSV</Button>
                            </Toolbar>
                            <Divider />
                            <Grid className={classes.downSection} container>
                                <Grid className={classes.padding} item md={4}>
                                    <LeftPanel onSelect={this.handleSelectArea}/>
                                </Grid>
                                <Grid className={classes.padding} item md={8}>
                                    <RightPanel onRemove={this.handleRemoveArea} />
                                </Grid>
                            </Grid>
                        </Paper>
                    </Grid>
                </Grid>
            </Layout>
        );
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(UserArea));