import React, { Component } from "react";
import DropzoneComponent from "react-dropzone-component";
import ReactDOMServer from "react-dom/server";

import UploadIcon from "@material-ui/icons/CloudUpload";
import CheckIcon from "@material-ui/icons/Check";
import CloseIcon from "@material-ui/icons/Close";
import withStyles from "@material-ui/core/styles/withStyles";
import IconButton from "@material-ui/core/IconButton";
import CircularProgress from "@material-ui/core/CircularProgress";
import green from "@material-ui/core/colors/green";
import red from "@material-ui/core/colors/red";
import { APP_URL } from "../../../constants/config";

const djsConfig = {
    previewTemplate: ReactDOMServer.renderToStaticMarkup(<span></span>),
    dictDefaultMessage: "Click above button to upload"
};

const styles = theme => ({
    wrapper: {
        textAlign: "center"
    },
    imageIcon: {
        pointerEvents: "none",
        cursor: "pointer"
    },
    button: {
        pointerEvents: 'none',
        cursor: 'pointer'
    },
    green: {
        color: green[600]
    },
    red: {
        color: red[600]
    },
    thumbnail: {
        width: 150,
        height: 150,
        backgroundSize: "cover",
        cursor:"pointer",
        pointerEvents:"none",
        margin: "auto"
    }
});

class Image extends Component {
    constructor(props) {
        super(props);

        this.handleSuccess = this.handleSuccess.bind(this);
        this.handleError = this.handleError.bind(this);
        this.handleBeforeUpload = this.handleBeforeUpload.bind(this);
        this.handleThumbnail = this.handleThumbnail.bind(this);

        this.state = {
            loading: false,
            error: false,
            success: false,
            thumbnail: null
        };
    }

    handleBeforeUpload() {
        this.setState({
            loading: true
        });
    }

    handleSuccess(param1, response) {
        const { onChange } = this.props;

        onChange(response.token);
        this.setState({
            success: true,
            loading: false,
            error: false
        });
    }

    handleError() {
        this.setState({
            success: false,
            loading: false,
            error: true
        });
    }

    handleThumbnail(file) {
        this.setState({thumbnail:file.dataURL});
    }


    render() {
        const { classes,label,value } = this.props;
        const { thumbnail,error,loading, success } = this.state;
        
        return (
            <div className={classes.wrapper}>
                <DropzoneComponent
                    config={{
                        postUrl: APP_URL + "api/web/upload/image"
                    }}
                    djsConfig={djsConfig}
                    eventHandlers={{
                        success: this.handleSuccess,
                        addedfile: this.handleBeforeUpload,
                        error: this.handleError,
                        thumbnail: this.handleThumbnail
                    }}
                    djsConfig={{
                        dictDefaultMessage:label,
                        previewTemplate:"<div></div>"
                    }}
                >
                    <div
                        style={{
                            backgroundImage: `url(${
                                thumbnail ? thumbnail :( error?
                                    APP_URL+"images/image_error.jpg":
                                    (value?APP_URL+value:APP_URL+"images/image_add.jpg")
                                )
                            })`
                        }}
                        className={classes.thumbnail}
                    >
                        {loading?
                            <CircularProgress />
                        :null}
                    </div>
                </DropzoneComponent>
            </div>
        );
    }
}

export default withStyles(styles)(Image);
