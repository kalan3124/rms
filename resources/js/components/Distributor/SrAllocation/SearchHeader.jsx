import React, { Component } from "react";
import PropTypes from "prop-types";
import { fade } from '@material-ui/core/styles/colorManipulator';
import withStyles from "@material-ui/core/styles/withStyles";
import AppBar from "@material-ui/core/AppBar";
import Toolbar from "@material-ui/core/Toolbar";
import IconButton from "@material-ui/core/IconButton";
import InputBase from "@material-ui/core/InputBase";

import SearchIcon from "@material-ui/icons/Search";

const styles = theme => ({
    iconButton: {
        width: theme.spacing.unit * 4,
        height: theme.spacing.unit * 4,
        padding: 0,
        marginTop: -12
    },
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
    },
    grow:{
        flexGrow:1
    }
})

class SearchHeader extends Component {

    constructor(props){
        super(props);

        this.handleChange = this.handleChange.bind(this)
    }

    handleChange({currentTarget}){
        const {onChange} = this.props;

        onChange(currentTarget.value);
    }

    render() {

        const { classes, icon,label,value } = this.props;

        return (
            <AppBar color="primary" position="relative">
                <Toolbar>
                    <div className={classes.search}>
                        <div className={classes.searchIcon}>
                            <SearchIcon />
                        </div>
                        <InputBase
                            placeholder={"Search for "+label}
                            classes={{
                                root: classes.inputRoot,
                                input: classes.inputInput,
                            }}
                            value={value}
                            onChange={this.handleChange}
                        />
                    </div>
                    <div className={classes.grow}/>
                    <IconButton className={classes.iconButton} color="inherit">
                        {icon}
                    </IconButton>
                </Toolbar>
            </AppBar>
        )
    }
}

SearchHeader.propTypes = {
    icon: PropTypes.node,
    classes: PropTypes.shape({
        iconButton: PropTypes.string,
        search: PropTypes.string,
        searchIcon: PropTypes.string,
        inputRoot: PropTypes.string,
        inputInput: PropTypes.string,
        grow: PropTypes.string
    }),
    label: PropTypes.string,

    value: PropTypes.string,
    onChange: PropTypes.func
}

export default withStyles(styles)(SearchHeader);