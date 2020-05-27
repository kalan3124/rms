import React, { Component } from 'react';
import { connect } from 'react-redux';
import { closeDialog } from '../../actions/Dialogs';

import withStyles from '@material-ui/core/styles/withStyles'
import IconButton from '@material-ui/core/IconButton'
import Button from '@material-ui/core/Button'
import Snackbar from '@material-ui/core/Snackbar'
import CloseIcon from '@material-ui/icons/Close';
import green from '@material-ui/core/colors/green';
import amber from '@material-ui/core/colors/amber';
import CheckCircleIcon from '@material-ui/icons/CheckCircle';
import ErrorIcon from '@material-ui/icons/Error';
import InfoIcon from '@material-ui/icons/Info';
import HelpIcon from '@material-ui/icons/Help';
import WarningIcon from '@material-ui/icons/Warning';

const variantIcon = {
    success: CheckCircleIcon,
    warning: WarningIcon,
    error: ErrorIcon,
    info: InfoIcon,
    confirm: HelpIcon,
};

const mapStateToProps = state => ({
    ...state,
    ...state.Dialogs
})

const styles = theme => ({
    success: {
        color: green[300],
        display: 'flex',
        alignItems: 'center',
    },
    error: {
        color: theme.palette.error.light,
        display: 'flex',
        alignItems: 'center',
    },
    info: {
        color: theme.palette.primary.light,
        display: 'flex',
        alignItems: 'center',
    },
    warning: {
        color: amber[300],
        display: 'flex',
        alignItems: 'center',
    },
    confirm: {
        display: 'flex',
        alignItems: 'center',
    },
    iconVariant: {
        opacity: 0.9,
        marginRight: theme.spacing.unit,
        fontSize: 20,
    },
    margin:{
        margin: theme.spacing.unit,
    }
})

class Dialogs extends Component {

    handleClose(key) {
        const { dispatch } = this.props;

        dispatch(closeDialog(key))
    }

    handleConfirm(key) {
        const { dispatch, alerts } = this.props;

        let callback = alerts[key].onConfirm;

        if (typeof callback != 'undefined') callback();

        dispatch(closeDialog(key));
    }

    handleCancel(key) {
        const { dispatch, alerts } = this.props;

        let callback = alerts[key].onCancel;

        if (typeof callback != 'undefined') callback();

        dispatch(closeDialog(key));
    }

    renderSnacks(){
        const { alerts, classes } = this.props;

        return alerts.map((alert, key) => {

            let buttons = [];

            let marginBottom = 0;

            for (let index =key==0?1:0 ; index < key; index++) {
                const {message} = alerts[key];

                let charactersPerLine = 82;
                let lines = Math.ceil(message.length/charactersPerLine);
                
                marginBottom += (lines*21)+(lines>1?100:48);
            }

            if (alert.type == 'confirm') {
                buttons = [
                    <Button key="yes" color="primary" size="small" onClick={e => this.handleConfirm(key)}>Yes</Button>,
                    <Button key="no" color="secondary" size="small" onClick={e => this.handleCancel(key)}>No</Button>,
                ];
            } else {
                buttons = [
                    <IconButton
                        key="close"
                        aria-label="Close"
                        color="inherit"
                        onClick={e => this.handleClose(key)}
                    >
                        <CloseIcon />
                    </IconButton>
                ];
            }

            const Icon = variantIcon[alert.type];

            return (<Snackbar
                anchorOrigin={{
                    vertical: 'bottom',
                    horizontal: 'right',
                }}
                aria-describedby={'snack-bar-' + key}
                open={true}
                autoHideDuration={alert.type != 'confirm' ? 6000 : undefined}
                onClose={e => this.handleClose(key)}
                message={<span id={'snack-bar-' + key} className={classes[alert.type]} ><Icon className={classes.iconVariant} />{alert.message}</span>}
                action={buttons}
                key={key}
                onClose={e => (0)}
                disableWindowBlurListener={true}
                className={classes.margin}
                style={{marginBottom}}
            />)
        })
    }

    render() {


        return (
            <div>
                {this.renderSnacks()}
            </div>
        )
    }
}


export default withStyles(styles)(connect(mapStateToProps)(Dialogs));