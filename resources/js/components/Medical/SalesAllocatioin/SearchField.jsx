import React, { Component } from "react";
import withStyles from "@material-ui/core/styles/withStyles";
import { fade } from "@material-ui/core/styles/colorManipulator";

import SearchIcon from "@material-ui/icons/Search";
import InputBase from "@material-ui/core/InputBase";

export const styles = theme=>({

    search: {
        position: 'relative',
        borderRadius: theme.shape.borderRadius,
        backgroundColor: fade(theme.palette.common.white, 0.15),
        '&:hover': {
            backgroundColor: fade(theme.palette.common.white, 0.25),
        },
        marginRight: theme.spacing.unit * 2,
        marginLeft: 0-theme.spacing.unit*2,
        marginTop: -12,
        width: '100%',
        [theme.breakpoints.up('sm')]: {
            marginLeft: 0-theme.spacing.unit*2,
        },
    },
    searchIcon: {
        width: theme.spacing.unit * 9,
        height: '100%',
        position: 'absolute',
        pointerEvents: 'none',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
    },
    inputRoot: {
        color: 'inherit',
        width: '100%',
    },
    inputInput: {
        paddingTop: theme.spacing.unit,
        paddingRight: theme.spacing.unit,
        paddingBottom: theme.spacing.unit,
        paddingLeft: theme.spacing.unit * 10,
        transition: theme.transitions.create('width'),
        width: '100%',
        [theme.breakpoints.up('md')]: {
            width: 200,
        },
    }
})

class SearchField extends Component {
    constructor(props){
        super(props);

        this.handleChange  = this.handleChange.bind(this);
    }

    handleChange(e){
        const {onChange} = this.props;

        if(onChange){
            onChange(e.target.value);
        }
    }

    render() {
        const {classes,value,label} =this.props;

        return (
            <div className={classes.search}>
                <div className={classes.searchIcon}>
                    <SearchIcon />
                </div>
                <InputBase
                    placeholder={"Search for " + label}
                    classes={{
                        root: classes.inputRoot,
                        input: classes.inputInput
                    }}
                    value={value}
                    onChange={this.handleChange}
                />
            </div>
        );
    }
}

export default withStyles(styles) (SearchField);
