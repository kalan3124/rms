import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { toggleSearch } from '../../actions/CrudForm';
import Input from './Input/Input'

import green from '@material-ui/core/colors/green'
import  withStyles from '@material-ui/core/styles/withStyles';
import  Paper from '@material-ui/core/Paper';
import  Typography from '@material-ui/core/Typography';
import  Divider from '@material-ui/core/Divider';
import  Grid from '@material-ui/core/Grid';
import  Switch from '@material-ui/core/Switch';
import  Button from '@material-ui/core/Button';
import  FormControlLabel from '@material-ui/core/FormControlLabel';

import classNames from 'classnames'

const styles = theme => ({
    button: {
        margin: 2,
        float: 'right'
    },
    margin: {
        padding: theme.spacing.unit
    },
    greenButton:{
        background:green[700],
        color:theme.palette.common.white
    },
    input:{
        paddingLeft:theme.spacing.unit,
        paddingRight:theme.spacing.unit,
        paddingTop:theme.spacing.unit*1.5
    },
    paper: {
        width: '40vw',
        minWidth: '400px',
        marginLeft: '30vw',
        marginTop: '40px',
        padding: theme.spacing.unit*2
    },
})

const mapStateToProps = state => ({
    ...state,
    ...state.CrudForm
})

class CrudForm extends Component {
    render() {
        const { title, inputs, searching, structure, classes, search, onClear, mode,disableSearch } = this.props;

        return (
            <Paper className={classes.paper}>
                <form onSubmit={(this.handleSubmit).bind(this)} >
                    <Typography align="center" variant="h6">{(mode[0].toUpperCase() + mode.slice(1)) + " " + title}</Typography>
                    <Divider />
                    {structure.map((group, i) => (
                        <Grid key={i} container>
                            {
                                (typeof group == 'string') ?
                                    this.renderInput(group, inputs[group], 12)
                                    :
                                    group.map(name => (
                                        this.renderInput(name, inputs[name], 12 / group.length)
                                    ))
                            }
                        </Grid>
                    ))}
                    <Divider />
                    <Grid alignContent="flex-end" container>
                        <Grid item md={6}>
                            {!disableSearch?
                            <FormControlLabel
                                control={
                                    <Switch checked={search} onChange={(this.handleSearchToggled).bind(this)} color="primary" />
                                }
                                label={searching ? "Searching..." : "Search"}
                            />
                            :null}
                        </Grid>

                        <Grid item md={6}>
                            {mode != 'search' ? <Button type="submit" variant="contained" className={ classNames(classes.button,classes.greenButton)} margin="dense" >{(mode[0].toUpperCase() + mode.slice(1))}</Button> : null}
                            <Button variant="contained" onClick={onClear} className={classes.button} color="secondary" margin="dense" >Cancel</Button>
                        </Grid>
                    </Grid>
                </form>
            </Paper>
        )
    }


    renderInput(name, props, size) {

        const { values, classes } = this.props;

        return (
            <Grid className={classes.input} key={name} item xs={12} md={size}>
                <Input name={name} value={typeof values[name] == 'undefined' ? '' : values[name]} onChange={(value) => this.hanldeChangeInput(name, value)} {...props} />
            </Grid>
        )
    }

    handleSubmit(e){
        e.preventDefault();

        this.props.onSubmit();
    }

    handleSearchToggled() {
        const { dispatch, values, onSearch } = this.props;
        dispatch(toggleSearch());
        onSearch(values)
    }

    hanldeChangeInput(name, value) {
        const { values, onInputChange, onSearch, search } = this.props;

        let modedValues = { ...values };

        modedValues[name] = value;

        if (search&&onSearch) {
            onSearch(modedValues);
        }

        onInputChange(modedValues)
    }
}

CrudForm.propTypes = {
    values: PropTypes.object.isRequired,
    title: PropTypes.string.isRequired,
    disableSearch:PropTypes.bool,
    structure:PropTypes.array,
    onSubmit: PropTypes.func,
    onInputChange: PropTypes.func
}

export default withStyles(styles)(connect(mapStateToProps)(CrudForm));