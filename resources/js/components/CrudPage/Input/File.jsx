import React, { Component } from 'react';
import DropzoneComponent from 'react-dropzone-component';
import ReactDOMServer from "react-dom/server"

import UploadIcon from "@material-ui/icons/CloudUpload"
import CheckIcon from "@material-ui/icons/Check";
import CloseIcon from "@material-ui/icons/Close";
import withStyles from '@material-ui/core/styles/withStyles';
import IconButton from '@material-ui/core/IconButton';
import CircularProgress from '@material-ui/core/CircularProgress';
import green from '@material-ui/core/colors/green';
import red from '@material-ui/core/colors/red';
import { APP_URL } from '../../../constants/config';

const djsConfig = {
    previewTemplate: ReactDOMServer.renderToStaticMarkup(
        <span></span>
    ),
    dictDefaultMessage: "Click above button to upload"
}

const styles = theme => ({
    wrapper: {
        textAlign: "center"
    },
    button: {
        pointerEvents: 'none',
        cursor: 'pointer'
    },
    green:{
        color: green[600]
    },
    red:{
        color: red[600]
    }
})

class File extends Component {

    constructor(props){
        super(props);

        this.handleSuccess = this.handleSuccess.bind(this);
        this.handleError = this.handleError.bind(this);
        this.handleBeforeUpload = this.handleBeforeUpload.bind(this);

        this.state = {
            loading: false,
            error: false,
            success:false
        }
    }

    handleBeforeUpload() {
        this.setState({
            loading: true
        });
    }

    handleSuccess(param1,response){
        const {onChange} = this.props;

        onChange(response.token);
        this.setState({
            success:true,
            loading:false,
            error:false
        })
    }

    handleError(){
        this.setState({
            success:false,
            loading:false,
            error:true
        })
    }

    renderButton() {
        const { loading,success,error } = this.state;

        const {classes} = this.props;

        if(success){
            return (
                <IconButton className={classes.green}>
                    <CheckIcon/>
                </IconButton>
            );
        }

        if(error){
            return (
                <IconButton className={classes.red}>
                    <CloseIcon/>
                </IconButton>
            );
        }

        if (loading) {
            return (
                <CircularProgress />
            );
        } else {
            return (
                <IconButton className={classes.button} color="primary" variant="contained" >
                    <UploadIcon />
                </IconButton>
            );
        }
    }

    render() {

        const { classes,fileType} = this.props;


        return (
            <div className={classes.wrapper}>
                <DropzoneComponent
                    config={{
                        postUrl: APP_URL+'api/web/upload/'+fileType
                    }}
                    djsConfig={djsConfig}
                    eventHandlers={{
                        success:this.handleSuccess,
                        addedfile:this.handleBeforeUpload,
                        error:this.handleError
                    }}
                >
                    {this.renderButton()}
                </DropzoneComponent>
            </div>
        )
    }
}

export default withStyles(styles)(File);