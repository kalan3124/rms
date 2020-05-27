import React from 'react';

import  withStyles from '@material-ui/core/styles/withStyles';
import  Toolbar from '@material-ui/core/Toolbar';
import  Typography from '@material-ui/core/Typography';
import  Button from '@material-ui/core/Button';
import Search from '@material-ui/icons/Search';
import Add from '@material-ui/icons/Add';
import green from '@material-ui/core/colors/green'
import classNames from 'classnames'

const styles = theme=>({
    title: {
        flexGrow: 1
    },

    actionButton: {
        marginRight: theme.spacing.unit
    },
    greenButton:{
        background:green[700],
        color:theme.palette.common.white,
        '&:hover':{
            background:green[700]
        }
    },
    blackButton:{
        background:'#000',
        color:theme.palette.common.white,
        '&:hover':{
            background:'#000'
        }
    }
})

const TopPanel = ({classes, title, onSearchClik, onCreateClick,create }) => (
    <Toolbar className={classes.headToolbar}>
        <Typography className={classes.title} variant="h6">{title}</Typography>
        <Button onClick={onSearchClik} color="secondary" className={ classNames(classes.actionButton,classes.blackButton)} variant="contained" ><Search />Search</Button>
        {create?
        <Button onClick={onCreateClick} color="primary" className={ classNames(classes.actionButton,classes.greenButton)} variant="contained" ><Add />Create</Button>
        :null}
    </Toolbar>
);

export default withStyles(styles)(TopPanel)