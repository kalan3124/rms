import React, { Component } from "react";
import { APP_URL } from "../../../constants/config";
import PropTypes from "prop-types";

import Popover from "@material-ui/core/Popover";
import withStyles from "@material-ui/core/styles/withStyles";

const styles = theme=>({
    imgPopup:{
        maxWidth:"75vw",
        maxHeight:"75vh"
    }
});

class Image extends Component {
    constructor(props) {
        super(props);

        this.state = {
            open: false,
            element: undefined
        }

        this.handleClose = this.handleClose.bind(this);
        this.handleOpen = this.handleOpen.bind(this);
    }

    handleClose(e) {
        this.setState({ open: false, element: undefined });
    }

    handleOpen(e) {
        this.setState({ open: true, element: e.currentTarget })
    }

    render() {
        const { value,classes } = this.props;
        const { element, open } = this.state;

        if(!value)
            return null;

        if(value.substr(0,13)){

        }

        return (
            <div>
                <img width={60} onClick={this.handleOpen} src={APP_URL + value} />
                <Popover
                    open={open}
                    anchorEl={element}
                    onClose={this.handleClose}
                    anchorOrigin={{
                        vertical: 'bottom',
                        horizontal: 'center',
                    }}
                    transformOrigin={{
                        vertical: 'top',
                        horizontal: 'center',
                    }}
                >
                    <a href={APP_URL + (value.substr(0,8)!="storage/"?"storage/image/"+value:value)} target="_blank" >
                        <img className={classes.imgPopup} src={APP_URL + value}/>
                    </a>
                </Popover>
            </div>
        );
    }
}

Image.propTypes = {
    value: PropTypes.string
}


export default withStyles(styles) (Image);