import React, {Component} from "react";
import PropTypes from "prop-types";
import {connect} from "react-redux";
import {Link} from "react-router-dom";


import Paper from "@material-ui/core/Paper";
import Grid from "@material-ui/core/Grid";
import Typography from "@material-ui/core/Typography";
import Divider from "@material-ui/core/Divider";
import withStyles from "@material-ui/core/styles/withStyles";

import Toolbar from "@material-ui/core/Toolbar";
import Button from "@material-ui/core/Button";

import AllocationsPanel from "./AllocationsPanel";
import { addItem,removeItem, removeAll } from "../../../actions/Medical/UserCustomer";
import Layout from "../../App/Layout";
import SearchPanel from "./SearchPanel";

const styles=theme=>({
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

const tabs = [
    {
        label:"Chemists",
        link:"chemist"
    },
    {
        label:"Doctors",
        link:"doctor"
    },
    {
        label:"Other Hospital Staffs",
        link:"other_hospital_staff"
    }
];

const mapStateToProps= state=>({
    ...state.UserCustomer,
    ...state.UserAllocation
})

const mapDispatchToProps = dispatch=>({
    onAddCustomer: (type,customer,user,modedCustomers)=>dispatch(addItem(type,customer,user,modedCustomers)),
    onRemoveCustomer: (type,customer,user,modedCustomers)=>dispatch(removeItem(type,customer,user,modedCustomers)),
    onRemoveAll: (user)=>dispatch(removeAll(user))
});

class UserCustomer extends Component {

    constructor(props){
        super(props);

        this.handleOnRemove = this.handleOnRemove.bind(this);
        this.handleOnSelect = this.handleOnSelect.bind(this);
        this.handleRemoveAllClick = this.handleRemoveAllClick.bind(this);
    }

    handleOnSelect(type,item){
        const {chemists,staffs,doctors,user,onAddCustomer} = this.props;

        if(type=='chemist'){
            let modedChemists = {...chemists};

            modedChemists[item.value] = item;

            onAddCustomer(type,item,user,modedChemists);
        } else if (type=="other_hospital_staff"){
            let modedStaffs = {...staffs}

            modedStaffs[item.value] = item;

            onAddCustomer(type,item,user,modedStaffs);

        } else {
            let modedDoctors = {...doctors};

            modedDoctors[item.value] = item;

            onAddCustomer(type,item,user,modedDoctors);
        }
    }

    handleOnRemove(type,item){
        const {chemists,doctors,staffs,user,onRemoveCustomer} = this.props;

        if(type=='chemist'){
            let modedChemists = {...chemists};

            delete modedChemists[item.value];

            onRemoveCustomer(type,item,user,modedChemists);
        } else if (type=="other_hospital_staff"){
            let modedStaffs = {...staffs}

            delete modedStaffs[item.value];

            onRemoveCustomer(type,item,user,modedStaffs);
            
        }else {
            let modedDoctors = {...doctors};

            delete modedDoctors[item.value];

            onRemoveCustomer(type,item,user,modedDoctors);
        }
    }

    handleRemoveAllClick(){
        const {user,onRemoveAll} = this.props;

        onRemoveAll(user);
    }

    render(){
        const {classes,user} = this.props;

        return(
            <Layout sidebar>
                <Paper className={classes.padding}>
                    <Toolbar>
                        <Typography variant="h5" align="center">User Customer Allocations</Typography>
                        <div className={classes.grow}/>
                        {user?
                        <Button onClick={this.handleRemoveAllClick} className={classes.marginRight} variant="contained" color="secondary">Delete All</Button>
                        :null}
                        <Button component={Link} to="/medical/other/upload_csv/user_customer" variant="contained" color="primary">Upload CSV</Button>
                    </Toolbar>
                    <Divider/>
                    <Grid container>
                        <Grid item md={4}>
                            <SearchPanel 
                                tabs={tabs}
                                onSelect={this.handleOnSelect}
                            />
                        </Grid>
                        <Grid item md={8}>
                            <AllocationsPanel
                                tabs={tabs}
                                onRemove={this.handleOnRemove}
                            />
                        </Grid>
                    </Grid>
                </Paper>
            </Layout>
        );
    }
}

UserCustomer.propTypes = {
    classes:PropTypes.shape({
        padding:        PropTypes.string
    })
};

export default connect(mapStateToProps,mapDispatchToProps) (withStyles(styles)(UserCustomer));