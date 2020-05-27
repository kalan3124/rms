import React, { Component } from "react";
import { withRouter } from "react-router-dom";
import ReactDOMServer from "react-dom/server"
import classNames from "classnames";
import Layout from "../../App/Layout";
import { connect } from "react-redux";
import { fetchInfo, getParams, downloadFormat, submitFile, clearPage, fetchStatus, changeStatus } from "../../../actions/UploadCSV";
import Typography from "@material-ui/core/Typography"
import Divider from "@material-ui/core/Divider"
import Grid from "@material-ui/core/Grid"
import Paper from "@material-ui/core/Paper"
import withStyles from "@material-ui/core/styles/withStyles"
import IconButton from "@material-ui/core/IconButton"
import CircularProgress from "@material-ui/core/CircularProgress"
import LinearProgress from "@material-ui/core/LinearProgress"
import Button from "@material-ui/core/Button"
import Toolbar from "@material-ui/core/Toolbar"
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import ListItemIcon from "@material-ui/core/ListItemIcon";
import PropTypes from "prop-types";
import DownloadIcon from "@material-ui/icons/CloudDownload";
import UploadIcon from "@material-ui/icons/CloudUpload";
import CheckIcon from "@material-ui/icons/Check";
import CloseIcon from "@material-ui/icons/Close";
import InfoIcon from "@material-ui/icons/Info";
import orange from "@material-ui/core/colors/orange";
import blue from "@material-ui/core/colors/blue";
import red from "@material-ui/core/colors/red";
import green from "@material-ui/core/colors/green";
import DropzoneComponent from 'react-dropzone-component';
import { APP_URL } from "../../../constants/config";

const styles = theme => ({
    paper: {
        margin: theme.spacing.unit,
        padding: theme.spacing.unit,
    },
    icon: {
        width: theme.spacing.unit * 20,
        height: theme.spacing.unit * 20
    },
    iconButton: {
        margin: theme.spacing.unit
    },
    orange: {
        color: orange[500]
    },
    blue: {
        color: blue[500]
    },
    red: {
        color: red[500]
    },
    green: {
        color: green[500]
    },
    center: {
        textAlign: "center"
    },
    grow:{
        flexGrow:1
    },
    marginTop:{
        marginTop:theme.spacing.unit*3
    }
})

const mapStateToProps = state => ({
    ...state.UploadCSV
});

const mapDispatchToProps = dispatch => ({
    onChangeType: (name, formName) => dispatch(fetchInfo(name, formName)),
    onChangeFile: (uploadedFile,name,formName) => dispatch(submitFile(uploadedFile,name,formName)),
    onClear: ()=>dispatch(clearPage()),
    onCheckStatus:timeout=>dispatch(fetchStatus(timeout))
});

const djsConfig = {
    previewTemplate: ReactDOMServer.renderToStaticMarkup(
        <span></span>
    ),
    dictDefaultMessage: ""
}

class UploadCSV extends Component {

    constructor(props) {
        super(props);
        const { name, formName } = props.match.params;
        props.onChangeType(name, formName);

        this.handleDownloadClick = this.handleDownloadClick.bind(this);
        this.handleSuccess = this.handleSuccess.bind(this);
        this.handleError = this.handleError.bind(this);
        this.handleBeforeUpload = this.handleBeforeUpload.bind(this);

        this.state = {
            loading: false,
            error: false,
            success: false
        }
    }

    componentWillUpdate(nxtProps) {
        const { name, formName } = this.props;
        if (typeof name == 'undefined' && typeof formName == 'undefined') return null;
        this.changeType(nxtProps.name, nxtProps.formName);
    }

    handleBeforeUpload() {
        this.setState({
            loading: true,
            success:false,
            error:false
        });
    }

    handleSuccess(param1, response) {
        const { onChangeFile,name,formName } = this.props;

        onChangeFile(response.token,name,formName);

        this.checkStatus(false)

        this.setState({
            success: true,
            loading: false,
            error: false
        })
    }

    checkStatus(now=true){
        const {onCheckStatus,status,totalLines}= this.props;

        if(status=="success"||status=="error") return;

        let timeout = window.setTimeout(()=>{
            this.checkStatus()
        },1000);

        if(!totalLines) return;

        if(now)
            onCheckStatus(timeout);
    }

    handleError() {
        this.setState({
            success: false,
            loading: false,
            error: true
        })
    }

    changeType(nxtName, nxtFormName) {
        const { name, formName, onChangeType } = this.props;

        if (name != nxtName || nxtFormName != formName) {
            onChangeType(nxtName, nxtFormName);
            this.setState({success:false,loading:false,error:false});
        }
    }

    componentWillUnmount(){
        const {timeout} = this.props;

        if(typeof timeout!="undefined") window.clearTimeout(timeout)
    }

    handleDownloadClick() {
        const { name, formName } = this.props;

        downloadFormat(name, formName);
    }

    handleClearButtonClick(){
        const {timeout, onClear}= this.props;

        if(typeof timeout!="undefined") window.clearTimeout(timeout)
    }

    renderDownloadBox() {
        const { submited, classes } = this.props;

        if (submited) return null;

        return (
            <Grid item md={6}>
                <Paper className={classes.paper} >
                    <Typography variant="h6" align="center">Download format</Typography>
                    <Divider />
                    <div className={classes.center} >
                        <IconButton onClick={this.handleDownloadClick} >
                            <DownloadIcon className={classNames(classes.icon, classes.orange)} />
                        </IconButton>
                    </div>
                    <Typography variant="caption" align="center">
                        Please download the CSV file by clicking below button and fill it down. Do not remove headers. Headers ends with "*" are required fields.
                    </Typography>
                </Paper>
            </Grid>
        );
    }

    renderUploadButton() {
        const { loading, success, error } = this.state;

        const { classes } = this.props;

        if (success) {
            return (
                <IconButton disabled >
                    <CheckIcon className={classNames(classes.icon, classes.green)} />
                </IconButton>
            );
        }

        if (error) {
            return (
                <IconButton disabled >
                    <CloseIcon className={classNames(classes.icon, classes.red)} />
                </IconButton>
            );
        }

        if (loading) {
            return (
                <CircularProgress thickness={2} size={160} />
            );
        } else {
            return (
                <IconButton disabled>
                    <UploadIcon className={classNames(classes.icon, classes.blue)} />
                </IconButton>
            );
        }
    }

    renderUploadBox() {
        const { submited, classes } = this.props;

        if (submited) return null;

        return (
            <Grid item md={6}>

                <Paper className={classes.paper}>
                    <Typography variant="h6" align="center">Upload format</Typography>
                    <Divider />
                    <div className={classes.center}>
                        <DropzoneComponent
                            config={{
                                postUrl: APP_URL + 'api/web/upload/csv'
                            }}
                            djsConfig={djsConfig}
                            eventHandlers={{
                                success: this.handleSuccess,
                                addedfile: this.handleBeforeUpload,
                                error: this.handleError
                            }}
                        >
                            {this.renderUploadButton()}
                        </DropzoneComponent>
                    </div>
                    <Typography variant="caption" align="center">
                        Please click the above button to upload the filled csv file. Please make sure you have filled all required values.
                    </Typography>
                </Paper>
            </Grid>
        );
    }

    renderProgress() {
        const { totalLines, currentLine, status, message, submited,classes,onClear } = this.props;

        if (!submited) return null;

        return (
            <Grid item md={8}>
                <Paper className={classes.paper}>
                    <Typography variant="h6" align="center">Please wait progressing!</Typography>
                    <Divider/>
                    <Toolbar>
                        {this.renderIcon(status)}
                        <Typography>{message}...</Typography>
                        <div className={classes.grow}/>
                        <Typography variant="caption" >{currentLine}/{totalLines} Lines</Typography>
                    </Toolbar>
                    <LinearProgress variant="determinate" value={Math.round((currentLine/totalLines)*100)} />
                    <Divider className={classes.marginTop} />
                    <Toolbar margin="dense" >
                        <div className={classes.grow}/>
                        <Button onClick={onClear} variant="contained" color="secondary">Cancel</Button>
                    </Toolbar>
                </Paper>
            </Grid>
        );
    }

    renderIcon(status){

        const {classes} = this.props;

        switch (status) {
        case "success":
            return (
                <CheckIcon className={classes.green}/>
            )
        case "error":
            return (
                <CloseIcon className={classes.red}/>
            );
        default:
            return (
                <InfoIcon className={classes.blue}/>
            );
        }
    }

    renderTips(){
        const {tips,classes} = this.props;

        return tips.map((tip,index)=>(
            <ListItem dense divider key={index} >
                <ListItemIcon>
                    <InfoIcon className={classes.blue} />
                </ListItemIcon>
                <ListItemText primary={tip}/>
            </ListItem>
        ))
    }

    render() {
        const { title,classes } = this.props;
        return (
            <Layout sidebar>
                <Typography variant="h5" align="center">Upload CSV Files for {title}</Typography>
                <Divider />
                <Grid alignItems="center" container>
                    {this.renderDownloadBox()}
                    {this.renderUploadBox()}
                    {this.renderProgress()}
                    <Grid item md={10}>
                        <Paper className={classes.paper} >
                            <Typography variant="h6">Tips and rules</Typography>
                            <Divider/>
                            <List>
                                {this.renderTips()}
                            </List>
                        </Paper>
                    </Grid>
                </Grid>
            </Layout>
        );
    }
}

UploadCSV.propTypes = {
    onChangeType: PropTypes.func,
    onChangeFile: PropTypes.func,
    name: PropTypes.string,
    formName: PropTypes.string,
    title: PropTypes.string,
    submited: PropTypes.bool,
    classes: PropTypes.object,
    tips: PropTypes.arrayOf(PropTypes.string)
}

export default connect(mapStateToProps, mapDispatchToProps)(withRouter(withStyles(styles)(UploadCSV)));