import React, {Component} from "react";
import {connect} from "react-redux";
import Layout from "../App/Layout";
import PropTypes from "prop-types";
import Typography from "@material-ui/core/Typography";
import Divider from "@material-ui/core/Divider";
import Paper from "@material-ui/core/Paper";
import  Grid from "@material-ui/core/Grid";
import  withStyles from "@material-ui/core/styles/withStyles";
import Editor from "./Editor";
import { submitIssue } from "../../actions/IssueTracker";
import ViewPage from "./ViewPage";

const styles = theme=>({
    paper:{
        margin:theme.spacing.unit,
        padding:theme.spacing.unit
    },
})

const mapStateToProps = state=>({
    ...state,
    ...state.IssueTracker
});

const mapDispatchToProps = dispatch=>({
    onSubmit: (content,label)=>dispatch(submitIssue(content,label))
});

class IssueTracker extends Component{

    constructor(props){
        super(props);

        this.handleSubmitEditor = this.handleSubmitEditor.bind(this);
    }

    handleSubmitEditor(){
        const {content,label,onSubmit} = this.props;

        onSubmit(content,label);
    }

    render(){
        const {classes} = this.props;

        return (
            <Layout sidebar>
                <Typography variant="h5">
                    Issue Tracker
                </Typography>
                <Divider/>
                <Grid container>
                    <Grid item md={6}>
                        <Paper className={classes.paper} >
                            <Editor
                                labels={[
                                    {
                                        id:"New Feature",
                                        label:"New Feature",
                                        color:"green"
                                    },
                                    {
                                        id:"Issue",
                                        label:"Issue",
                                        color:"red"
                                    }
                                ]}
                                onSubmit={this.handleSubmitEditor}
                            />
                        </Paper>
                    </Grid>
                    <Grid item md={6}>
                        <Paper className={classes.paper} md={6}>
                            <ViewPage/>
                        </Paper>
                    </Grid>
                </Grid>
            </Layout>
        );
    }
}

IssueTracker.propTypes = {
    classes: PropTypes.object
}

export default connect(mapStateToProps,mapDispatchToProps)( withStyles(styles) (IssueTracker));