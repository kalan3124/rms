import React, { Component, lazy } from 'react';
import withStyles from '@material-ui/core/styles/withStyles';
import TextField from '@material-ui/core/TextField';
import Select from './Select';
import Check from './Check';
import AsyncComponent from '../../App/AsyncComponent'

const TreeSelect = lazy(() => import(/* webpackChunkName: "tree-select" */'./TreeSelect'))
const AjaxDropdown = lazy(() => import(/* webpackChunkName: "ajax-dropdown" */'./AjaxDropdown'))
const DatePicker = lazy(() => import(/* webpackChunkName: "date-picker" */'./DatePicker'))
const TimePicker = lazy(() => import(/* webpackChunkName: "time-picker" */'./TimePicker'))
const DateTimePicker = lazy(() => import(/* webpackChunkName: "date-time-picker" */'./DateTimePicker'))
const File = lazy(() => import(/* webpackChunkName: "file-uploader" */'./File'))
const Image = lazy(() => import(/* webpackChunkName: "image-picker" */'./Image'))
const Location = lazy(() => import(/* webpackChunkName: "location-picker" */'./Location'));
const BonusLines = lazy(() => import(/* webpackChunkName: "bonus-lines" */'./BonusLines'));

const styles = theme => ({
    container: {
        display: 'flex',
        flexWrap: 'wrap',
    },
    dense: {
        marginTop: 19,
    },
    menu: {
        width: 200,
    },
});


class Input extends Component {

    constructor(props) {
        super(props);

        this.onDefaultChangeHandler = this.onDefaultChangeHandler.bind(this);
        this.onChangeHandler = this.onChangeHandler.bind(this);
        this.state = {
            changed: false
        }
    }

    onChangeHandler(e) {
        this.setState({ changed: true })
        this.props.onChange(e)
    }

    getValidationMessage() {
        const { value, validations, label } = this.props;

        if (validations == '' || !validations) {
            return undefined;
        }

        const rules = validations.split('||');

        let errorMessage = undefined;

        for (const rule of rules) {
            const parameters = rule.split(/[\,\:]/);

            if (!errorMessage) {
                switch (parameters[0]) {
                    case 'required':
                        if (value == '') {
                            errorMessage = "The " + label + " field is required.";
                        }
                        break;
                    case 'email':
                        if (! /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(value)) {
                            errorMessage = "Invalid email suppield to " + label + '.';
                        }
                        break;
                    case 'min':
                        if (parseInt(parameters[1]) > value.length) {
                            errorMessage = "The " + label + ' field should have ' + parameters[1] + ' characters or more.';
                        }
                        break;
                    case 'max':
                        if (parseInt(parameters[1]) < value.length) {
                            errorMessage = "The " + label + " may not have more than " + parameters[1] + " characters."
                        }
                        break;
                    case 'password':
                        if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/.test(value)) {
                            errorMessage = "The " + label + "field should have Minimum eight characters, at least one uppercase letter, one lowercase letter, one number and one special character" + " .";
                        }
                        break;


                    case 'regex':
                        const regexString = rule.split(/[\:]/, 1);
                        const regex = new RegExp(regexString);

                        if (!regex.test(value)) {
                            errorMessage = "Invalid " + label + ' supplied.'
                        }
                        break;
                    default:
                        break;
                }
            }
        }

        return errorMessage;

    }

    render() {
        const { label, name, type, value, link, options, hierarchy, parent, multiple, where, limit, file_type, otherValues, validations, className, component } = this.props;
        const { changed } = this.state;

        const required = (validations ? validations : "").split('||').includes('required');
        const errorMessage = changed ? this.getValidationMessage() : undefined;

        const inputProps = { multiple, label, name, type, value: value ? value : '', helperText: errorMessage, error: !!errorMessage, fullWidth: true, margin: "dense", variant: "outlined", className, component };

        switch (type) {
            case 'ajax_dropdown':
                return (
                    <AsyncComponent required={required} where={where} otherValues={otherValues} limit={limit} multiple={multiple} RenderModule={AjaxDropdown} link={link} onChange={this.onChangeHandler} {...inputProps} />
                );
            case 'multiple_ajax_dropdown':
                return (
                    <AsyncComponent required={required} where={where} otherValues={otherValues} limit={limit} multiple={multiple} RenderModule={AjaxDropdown} link={link} onChange={this.onChangeHandler} {...inputProps} />
                );
            case 'select':
                return (
                    <Select required={required} onChange={this.onChangeHandler} options={options} {...inputProps} />
                );
            case 'check':
                return (
                    <Check required={required} onChange={this.onChangeHandler} {...inputProps} />
                );
            case 'tree_select':
                return (
                    <AsyncComponent required={required} RenderModule={TreeSelect} onChange={this.onChangeHandler} hierarchy={hierarchy} parent={parent} {...inputProps} />
                );
            case 'date':
                return (
                    <AsyncComponent required={required} RenderModule={DatePicker} onChange={this.onChangeHandler} {...inputProps} />
                );
            case 'time':
                return (
                    <AsyncComponent required={required} RenderModule={TimePicker} onChange={this.onChangeHandler} {...inputProps} />
                );
            case 'date_time':
                return (
                    <AsyncComponent required={required} RenderModule={DateTimePicker} onChange={this.onChangeHandler} {...inputProps} />
                );
            case 'file':
                return (
                    <AsyncComponent required={required} fileType={file_type} RenderModule={File} onChange={this.onChangeHandler} {...inputProps} />
                );
            case 'image':
                return (
                    <AsyncComponent required={required} RenderModule={Image} onChange={this.onChangeHandler} {...inputProps} />
                );
            case 'location':
                return (
                    <AsyncComponent required={required} RenderModule={Location} onChange={this.onChangeHandler} {...inputProps} />
                );
            case 'bonus_lines':
                return (
                    <AsyncComponent required={required} RenderModule={BonusLines} onChange={this.onChangeHandler} {...inputProps} />
                );
            default:
                return (
                    <TextField required={required} onChange={this.onDefaultChangeHandler} {...inputProps} />
                );
        }
    }

    onDefaultChangeHandler({ target }) {
        const { onChange, type, name, upper_case } = this.props;

        let value = target.value;
        this.setState({ changed: true })

        if (
            !["user_name", "email"].includes(name) &&
            !["password", "email"].includes(type) &&
            !upper_case
        ) {
            value = value.toUpperCase();
        }

        onChange(value);
    }
}

export default withStyles(styles)(Input);

