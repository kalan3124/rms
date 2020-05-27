import React, { Component, Fragment } from "react";
import PropTypes from "prop-types";
import classNames from "classnames";
import {connect} from "react-redux";
import Toolbar from "@material-ui/core/Toolbar";
import Chip from "@material-ui/core/Chip";
import Button from "@material-ui/core/Button";
import MDEditor from "rich-markdown-editor";
import Typography from "@material-ui/core/Typography";
import Divider from "@material-ui/core/Divider";
import green from "@material-ui/core/colors/green";
import red from "@material-ui/core/colors/red";
import withStyles from "@material-ui/core/styles/withStyles";
import Theme from "../../constants/textEditorTheme";
import { changeLabel, changeContent } from "../../actions/IssueTracker";
import agent from "../../agent";
import { APP_URL } from "../../constants/config";

import HelpIcon from "@material-ui/icons/Help";

const styles = theme=>({
    editorWrapper:{
        padding:theme.spacing.unit,
        marginLeft:theme.spacing.unit*4,
        position:"relative",
        height:'55vh',
        background:"#e0e0e0",
        overflowY:"auto",
        overflowX:"hidden"
    },
    label:{
        padding:0,
        cursor:"pointer",
        marginLeft:theme.spacing.unit
    },
    grow:{
        flexGrow:1
    },
    green:{
        background:green[500],
        color:theme.palette.common.white,
        "&:hover":{
            background:green[500],
            color:theme.palette.common.white,
        },
        "&:focus":{
            background:green[500],
            color:theme.palette.common.white,
        },
    },
    red:{
        background:red[500],
        color:theme.palette.common.white,
        "&:hover":{
            background:red[500],
            color:theme.palette.common.white,
        },
        "&:focus":{
            background:red[500],
            color:theme.palette.common.white,
        },
    }
});



const mapStateToProps = state=>({
    ...state,
    ...state.IssueTracker
});

const mapDispatchToProps = dispatch=>({
    onChangeLabel:label=>dispatch(changeLabel(label)),
    onChangeContent:content=>dispatch(changeContent(content))
})

class Editor extends Component {

    constructor(props){
        super(props);

        this.handleChangeContent = this.handleChangeContent.bind(this)
    }
    
    async handleImageUpload(file){
        try{
            const {success,token} = await agent.IssueTracker.upload(file);

            if(!success) return 'images/failed_upload.png';

            return APP_URL+'storage/app/'+token;
            
        } catch(e){
            return 'images/failed_upload.png';

        }
    }

    handleChangeLabel(id){
        const {onChangeLabel} = this.props;
        return e=>typeof onChangeLabel!="undefined"? onChangeLabel(id):null;
    }

    renderLabels(){
        const {labels,classes} = this.props;

        const selectedLabel = this.props.label;

        return labels.map(({label,id,color},index)=>(
            <Chip onClick={this.handleChangeLabel(id)} className={classNames(classes.label,id==selectedLabel?classes[color]:undefined)} label={label} key={index}/>
        ));
    }

    handleChangeContent(value){
        const {onChangeContent} = this.props;

        onChangeContent(value())
    }

    handleHelpIconClick(){
        window.open(APP_URL+"images/issue_helper.gif","__blank")
    }

    render() {
        const {classes,resetKey,onSubmit} = this.props;

        return (
            <Fragment>
                <Toolbar variant="dense">
                    <Typography className={classes.grow} variant="h6" align="center">Write a new issue</Typography>
                    <HelpIcon onClick={this.handleHelpIconClick} />
                </Toolbar>
                <Divider />
                <div className={classes.editorWrapper}>
                    <MDEditor
                        key={resetKey}
                        theme={Theme}
                        uploadImage={this.handleImageUpload}
                        defaultValue={"## Issue Title\n description of the issue"}
                        onChange={this.handleChangeContent}
                    />
                </div>
                <Divider />
                <Toolbar >
                    {this.renderLabels()}
                    <div className={classes.grow} />
                    <Button onClick={onSubmit} color="primary" variant="contained" margin="dense">Submit</Button>
                </Toolbar>
            </Fragment>
        );
    }
}

Editor.propTypes = {
    labels:PropTypes.arrayOf(PropTypes.shape({
        id:PropTypes.string,
        label:PropTypes.string,
        color:PropTypes.string
    })),
    label:PropTypes.string,
    onChangeLabel: PropTypes.func,
    resetKey: PropTypes.number,
    onChangeContent: PropTypes.func
}

export default connect(mapStateToProps,mapDispatchToProps)( withStyles(styles) (Editor));