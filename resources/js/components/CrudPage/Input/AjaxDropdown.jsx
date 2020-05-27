
import React, { Component } from 'react';
import classNames from 'classnames';
import Async from 'react-select/lib/Async';
import withStyles  from '@material-ui/core/styles/withStyles';
import Typography from '@material-ui/core/Typography';
import NoSsr from '@material-ui/core/NoSsr';
import TextField from '@material-ui/core/TextField';
import Paper from '@material-ui/core/Paper';
import Chip from '@material-ui/core/Chip';
import MenuItem from '@material-ui/core/MenuItem';
import CancelIcon from '@material-ui/icons/Cancel';
import ListItem from '@material-ui/core/ListItem';
import { emphasize } from '@material-ui/core/styles/colorManipulator';
import agent from '../../../agent';
import { APP_URL } from '../../../constants/config';

const styles = theme => ({
    root: {
        flexGrow: 1
    },
    input: {
        display: 'block',
        paddingTop:6,
        paddingBottom:6,
        maxHeight:120,
        overflowY:"auto",
        minWidth: 160,
        background:theme.palette.common.white,
        [theme.breakpoints.down('md')]:{
            minWidth:200
        }
    },
    valueContainer: {
        display: 'flex',
        flexWrap: 'wrap',
        flex: 1,
        alignItems: 'center',
        overflow: 'hidden',
    },
    chip: {
        margin: `${theme.spacing.unit / 2}px ${theme.spacing.unit / 4}px`,
    },
    chipFocused: {
        backgroundColor: emphasize(
            theme.palette.type === 'light' ? theme.palette.grey[300] : theme.palette.grey[700],
            0.08,
        ),
    },
    noOptionsMessage: {
        padding: `${theme.spacing.unit}px ${theme.spacing.unit * 2}px`,
    },
    singleValue: {
        fontSize: 16,
    },
    placeholder: {
        position: 'absolute',
        left: 2,
        fontSize: '.85em',
    },
    paper: {
        position: 'absolute',
        zIndex: 2,
        marginTop: theme.spacing.unit,
        left: 0,
        right: 0,
    },
    divider: {
        height: theme.spacing.unit,
    },
    label:{
        marginTop: 4
    }
});

function NoOptionsMessage(props) {
    return (
        <Typography
            color="textSecondary"
            className={props.selectProps.classes.noOptionsMessage}
            {...props.innerProps}
        >
            {props.children}
        </Typography>
    );
}

function inputComponent({ inputRef, ...props }) {
    return <div ref={inputRef} {...props} />;
}

function Control(props) {
    return (
        <TextField
            fullWidth
            variant="outlined"
            margin="dense"
            required={props.selectProps.required}
            helperText={props.selectProps.helperText}
            error={props.selectProps.error}
            label={props.selectProps.placeholder}
            InputLabelProps={{
                className:props.selectProps.classes.label,
                shrink:!!(props.selectProps.value||props.selectProps.inputValue)
            }}
            InputProps={{
                inputComponent,
                inputProps: {
                    className: props.selectProps.classes.input,
                    inputRef: props.innerRef,
                    children: props.children,
                    ...props.innerProps,
                }
            }}
            {...props.selectProps.textFieldProps}
        />
    );
}

function Option(props) {
    return (
        <MenuItem
            buttonRef={props.innerRef}
            selected={props.isFocused}
            component="div"
            divider={true}
            style={{
                fontWeight: props.isSelected ? 500 : 400,
                fontSize:12,
                whiteSpace: 'normal',
                height: "auto"
            }}
            {...props.innerProps}
        >
            {props.children}
        </MenuItem>
    );
}

function Placeholder(props) {
    return null;
}

function SingleValue(props) {
    return (
        <Typography className={props.selectProps.classes.singleValue} {...props.innerProps}>
            {props.children}
        </Typography>
    );
}

function ValueContainer(props) {
    return <div className={props.selectProps.classes.valueContainer}>{props.children}</div>;
}

function MultiValue(props) {
    return (
        <Chip
            tabIndex={-1}
            label={props.children}
            className={classNames(props.selectProps.classes.chip, {
                [props.selectProps.classes.chipFocused]: props.isFocused,
            })}
            onDelete={props.removeProps.onClick}
            deleteIcon={<CancelIcon {...props.removeProps} />}
        />
    );
}

function Menu(props) {
    return (
        <Paper square className={props.selectProps.classes.paper} {...props.innerProps} >
            {props.children}
        </Paper>
    );
}

const components = {
    Control,
    Menu,
    MultiValue,
    NoOptionsMessage,
    Option,
    Placeholder,
    SingleValue,
    ValueContainer,
};

class AjaxSelect extends Component {

    constructor(props){
        super(props);

        this.state = {
            key:0
        };
    }

    componentWillReceiveProps(nextProps){
        const {where,otherValues} = this.props;

        
        const oldFormatedWhere = this.transformWhereClause(where,otherValues);
        const newFormatedWhere = this.transformWhereClause(where,nextProps.otherValues);
        if(JSON.stringify(oldFormatedWhere)!==JSON.stringify(newFormatedWhere)){
            this.setState({key:this.state.key+1});
        }
    }

    loadOptions(inputValue,callback){
        const {link,limit,where,otherValues} = this.props;
        agent.Crud.dropdown(link,inputValue,this.transformWhereClause(where,otherValues),limit).then(data=>{
            callback(data);
        })
    }

    transformWhereClause(where,otherValues){
        if(!where) return undefined;

        let modedWhere = {...where};

        Object.keys(where).forEach(column=>{
            let value = where[column];

            if(typeof value!='undefined'&&value.toString().match(/\{([0-9a-zA-Z\_\-]+)\}/g)){
                let newValue = otherValues[value.substr(1).substr(0,value.length-2)];
                if(newValue) modedWhere[column] = newValue.value;
                else modedWhere[column] = undefined;
            }
        })

        return modedWhere;
    }

    render() {
        const selectStyles = {
            input: base => ({
                ...base,
                '& input': {
                    font: 'inherit',
                },
                
            }),
            indicatorsContainer:(base)=>({
                ...base,
                height:0,
                marginTop:16,
                position:"absolute",
                top:0,
                right:8
            })
        };

        const {classes,value,onChange,label,multiple,otherValues,name,helperText,error,required,className} = this.props;
        const {key} = this.state;

        return (
            <div className={classes.root}>
                <NoSsr>
                    <Async
                        isClearable={true}
                        classes={classes}
                        className={className}
                        styles={selectStyles}
                        components={components}
                        loadOptions={(this.loadOptions).bind(this)}
                        defaultOptions
                        value={value}
                        onChange={onChange}
                        placeholder={label}
                        noOptionsMessage={()=>"No "+label+" Found For Your Keyword."}
                        isMulti={multiple}
                        cacheOptions={JSON.stringify(otherValues)}
                        key={key}
                        helperText={helperText}
                        required={required}
                        error={error}
                    />
                </NoSsr>
            </div>
        );
    }
}

export default withStyles(styles)( AjaxSelect);