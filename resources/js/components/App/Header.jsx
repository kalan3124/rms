import React, { Component } from "react";
import withStyles from "@material-ui/core/styles/withStyles";
import AppBar from "@material-ui/core/AppBar";
import Toolbar from "@material-ui/core/Toolbar";
import Typography from "@material-ui/core/Typography";
import Menu from "@material-ui/core/Menu";
import MenuItem from "@material-ui/core/MenuItem";
import CircularProgress from "@material-ui/core/CircularProgress";
import DashBoardIcon from "@material-ui/icons/Dashboard";
import IconButton from "@material-ui/core/IconButton";

import { APP_URL, APP_NAME } from "../../constants/config";
import { connect } from "react-redux";
import withRouter from "react-router-dom/withRouter";
import Link from "react-router-dom/Link";
import { toggleUserMenu } from "../../actions/Header";
import classNames from "classnames";
import Moment from "react-moment";
import "moment-timezone";
import Time from "./Time";

const styles = theme => ({
    root: {
        flexGrow: 1
    },
    whiteBackground: {
        background: "#fff",
        color: theme.palette.common.black
    },
    grow: {
        flexGrow: 1
    },
    menuLogoButton: {
        cursor: "pointer",
        backgroundSize: "100%"
    },
    headerHeight: {
        height: 56,
        [`${theme.breakpoints.up("xs")} and (orientation: landscape)`]: {
            height: 48
        },
        [theme.breakpoints.up("sm")]: {
            height: 50
        }
    },
    header: {
        color: theme.palette.common.white,
        zIndex: 1250,
        background: "#134f5c",
        marginLeft: 0,
        paddingLeft: 0
    },
    userMenu: {
        top: theme.spacing.unit * 4
    },
    toolBar: {
        marginLeft: 0,
        paddingLeft: 0,
        marginRight: 0,
        paddingRight: 0
    },
    leftToolbar: {
        minWidth: theme.spacing.unit * 17 - 48
    },
    profileName: {
        cursor: "pointer"
    },
    leftToolbarTexts: {
        padding: 0,
        lineHeight: "1.2",
        fontWeight: "600",
        textShadow: "unset",
        color: theme.palette.common.white
    },
    dashboardIcon: {
    }
});

const mapStateToProps = state => ({
    ...state,
    ...state.App,
    ...state.Header
});

class Header extends Component {
    constructor(props) {
        super(props);

        this.handleClickLogo = this.handleClickLogo.bind(this);
    }

    handleToggleUserMenu({ target }) {
        const { dispatch } = this.props;
        dispatch(toggleUserMenu(target));
    }

    handleLogoutClick() {
        localStorage.removeItem("userToken");
        window.location.href = APP_URL;
    }

    handleChangePassClick() {
        window.location.href = APP_URL + "change_password";
    }

    handleClickLogo() {
        const { onClickLogo } = this.props;

        if (onClickLogo) {
            onClickLogo();
        }
    }

    render() {
        const {
            classes,
            loading,
            user,
            userMenuExpanded,
            profileInfoAnchor,
            timeDif
        } = this.props;

        return (
            <div className={classes.root}>
                <AppBar className={classes.header} position="fixed">
                    <Toolbar className={classes.toolBar} variant="dense">
                        <Toolbar
                            className={classNames(
                                classes.whiteBackground,
                                classes.headerHeight,
                                classes.leftToolbar
                            )}
                            variant="dense"
                        >
                            <IconButton onClick={this.handleClickLogo}>
                                <DashBoardIcon
                                    color="primary"
                                    className={classes.dashboardIcon}
                                />
                            </IconButton>
                            {loading ? (
                                <CircularProgress />
                            ) : (
                                    <Link to="/" className={classes.logoContainer}>
                                        <img
                                            className={classes.headerHeight}
                                            src={APP_URL + "images/logo.jpg"}
                                        />
                                    </Link>
                                )}
                        </Toolbar>
                        <Typography
                            align="center"
                            variant="h6"
                            color="inherit"
                            className={classNames(classes.grow, classes.title)}
                        >
                            WELCOME TO {APP_NAME.toUpperCase()}
                        </Typography>
                        <Toolbar
                            className={classes.headerHeight}
                            variant="dense"
                        >
                            <div>
                                <Typography
                                    align="right"
                                    onClick={this.handleToggleUserMenu.bind(
                                        this
                                    )}
                                    className={classes.leftToolbarTexts}
                                    variant="subtitle2"
                                >
                                    {user.name}
                                </Typography>
                                <Typography
                                    align="right"
                                    onClick={this.handleToggleUserMenu.bind(
                                        this
                                    )}
                                    className={classes.leftToolbarTexts}
                                    variant="subtitle2"
                                >
                                    <Moment format="Do MMMM YYYY">
                                        {new Date(Date.now() - timeDif)}
                                    </Moment>
                                </Typography>
                                <Typography
                                    align="right"
                                    onClick={this.handleToggleUserMenu.bind(
                                        this
                                    )}
                                    className={classes.leftToolbarTexts}
                                    variant="subtitle2"
                                >
                                    <Time timeDif={timeDif} />
                                </Typography>
                            </div>
                            <Menu
                                id="simple-menu"
                                anchorEl={profileInfoAnchor}
                                open={Boolean(userMenuExpanded)}
                                onClose={this.handleToggleUserMenu.bind(this)}
                                className={classes.userMenu}
                            >
                                {/* <MenuItem onClick={this.handleClose}>
                                    Profile
                                </MenuItem> */}
                                {/* <MenuItem onClick={this.handleClose}>
                                    My account
                                </MenuItem> */}
                                <MenuItem onClick={this.handleChangePassClick}>
                                    My account
                                </MenuItem>
                                <MenuItem
                                    onClick={this.handleLogoutClick.bind(this)}
                                >
                                    Logout
                                </MenuItem>
                            </Menu>
                        </Toolbar>
                    </Toolbar>
                </AppBar>
            </div>
        );
    }

}

export default withStyles(styles)(connect(mapStateToProps)(withRouter(Header)));
