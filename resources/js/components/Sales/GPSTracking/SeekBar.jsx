import React, { Component } from "react";
import PropTypes from "prop-types";
import withStyles from "@material-ui/core/styles/withStyles";
import PlayIcon from "@material-ui/icons/PlayArrow";
import PauseIcon from "@material-ui/icons/Pause";
import FocusIcon from "@material-ui/icons/CenterFocusStrong";
import IconButton from "@material-ui/core/IconButton";
import Toolbar from "@material-ui/core/Toolbar";
import Tooltip from "@material-ui/core/Tooltip";
import Button from "@material-ui/core/Button";
import Menu from "@material-ui/core/Menu";
import MenuItem from "@material-ui/core/MenuItem";
import moment from 'moment';
import DateFnsUtils from '@date-io/moment';
import { MuiPickersUtilsProvider, TimePicker as MuiTimePicker } from 'material-ui-pickers';


const styles = theme => ({
    white: {
        color: theme.palette.common.white
    },
    grey: {
        color: theme.palette.grey[400]
    },
    padding: {
        padding: theme.spacing.unit
    },
    grow: {
        background: theme.palette.grey[400],
        height: 20,
        borderRadius: 5,
        flexGrow: 1,
        position: 'relative',
        overflow: 'hidden',
        cursor: 'pointer'
    },
    completed: {
        background: theme.palette.common.white,
        position: 'absolute',
        height: 20
    },
    menu: {
        zIndex: 2500
    },
    picker: {
        color: theme.palette.common.white,
        border: 'none',
        width: 100,
        marginRight: 4,
        marginLeft:4
    }
});

class SeekBar extends Component {

    constructor(props) {
        super(props);

        this.state = {
            mouseTime: this.calculateTime(0),
            fastMenuRef: undefined,
            fastMenuOpen: false
        };

        this.handleMouseMove = this.handleMouseMove.bind(this);
        this.handleChangeTime = this.handleChangeTime.bind(this);
        this.handleClickPlayButton = this.handleClickPlayButton.bind(this);
        this.handleClickFastButton = this.handleClickFastButton.bind(this);
        this.handleCloseFastMenu = this.handleCloseFastMenu.bind(this);

    }

    calculatePercent(startTime, currentTime, endTime) {

        const total = endTime - startTime;

        const current = currentTime - startTime;

        return parseInt((current / total) * 100);
    }

    calculateTime(percent) {
        const { startTime, endTime } = this.props;

        const total = endTime - startTime;

        const current = (total * (percent / 100)) + startTime;
        return current;
    }

    handleChangeTime(e) {
        const { onChangeTime } = this.props;
        const rect = e.target.getBoundingClientRect(),
            x = e.clientX - rect.left, //x position within the element.
            width = e.currentTarget.offsetWidth;

        const percent = parseInt((x > width ? width : (x < 0 ? 0 : x) / width) * 100);

        if (typeof onChangeTime != 'undefined') {
            onChangeTime(this.calculateTime(percent));
        }
    }

    handleMouseMove(e) {
        const rect = e.target.getBoundingClientRect(),
            x = e.clientX - rect.left, //x position within the element.
            width = e.currentTarget.offsetWidth;

        const percent = parseInt((x > width ? width : (x < 0 ? 0 : x) / width) * 100);

        this.setState({
            mouseTime: this.calculateTime(percent)
        });
    }

    handleClickPlayButton() {
        const { onPlay, onPause,playing } = this.props;

        if (playing && typeof onPause != 'undefined') {
            onPause();
        } else if (!playing && typeof onPlay != 'undefined') {
            onPlay();
        }
    }

    handleClickFastButton(e) {
        this.setState({ fastMenuRef: e.target, fastMenuOpen: true });
    }

    handleCloseFastMenu() {
        this.setState({ fastMenuOpen: false })
    }

    handleChangeSpeed(speed) {

        const { onChangeSpeed } = this.props;

        return e => {
            if (typeof onChangeSpeed != 'undefined') {
                onChangeSpeed(speed);
            }

            this.setState({ fastMenuOpen: false })
        }
    }

    render() {
        const { className,playing, classes, startTime, endTime, currentTime, onFollow, following, speed, speeds, onChangeStartTime, onChangeEndTime,fresh } = this.props;
        const { mouseTime, fastMenuRef, fastMenuOpen } = this.state;

        const percent = this.calculatePercent(startTime, currentTime, endTime);

        const Icon = playing ? PauseIcon : PlayIcon;

        let speedLabel = undefined;
        speeds.forEach(({ label, value }) => {
            if (value == speed)
                speedLabel = label;
        });

        if (!speedLabel)
            speedLabel = speeds[0].label;

        return (
            <div className={className}>
                <Toolbar id="test" variant="dense" >
                    <IconButton onClick={this.handleClickPlayButton} >
                        <Icon className={classes.white} />
                    </IconButton>
                    <Tooltip title={moment.unix(startTime).format('YYYY-MM-DD')}>
                        <MuiPickersUtilsProvider utils={DateFnsUtils}>
                            <div>
                                <MuiTimePicker
                                    margin="dense"
                                    value={moment.unix(fresh?startTime:currentTime)}
                                    onChange={value => onChangeStartTime(value.unix())}
                                    variant="outlined"
                                    InputProps={{ className: classes.picker }}
                                />
                            </div>
                        </MuiPickersUtilsProvider>
                    </Tooltip>
                    <Tooltip title={moment.unix(mouseTime).format('YYYY-MM-DD HH:mm:ss')}>
                        <div onMouseMove={this.handleMouseMove} onClick={this.handleChangeTime} className={classes.grow}>
                            <div className={classes.completed} style={{ width: percent + "%" }} />
                        </div>
                    </Tooltip>
                    <Tooltip title={moment.unix(endTime).format('YYYY-MM-DD')}>
                        <MuiPickersUtilsProvider utils={DateFnsUtils}>
                            <div>
                                <MuiTimePicker
                                    margin="dense"
                                    value={moment.unix(endTime)}
                                    onChange={value => onChangeEndTime(value.unix())}
                                    variant="outlined"
                                    InputProps={{ className: classes.picker }}
                                />
                            </div>
                        </MuiPickersUtilsProvider>
                    </Tooltip>
                    <Button onClick={this.handleClickFastButton} className={classes.white}>
                        {speedLabel}
                    </Button>
                    <Tooltip title="Follow current location">
                        <IconButton onClick={onFollow}>
                            <FocusIcon className={following ? classes.white : classes.grey} />
                        </IconButton>
                    </Tooltip>
                </Toolbar>
                <Menu className={classes.menu} anchorEl={fastMenuRef} open={fastMenuOpen} onClose={this.handleCloseFastMenu} >
                    {this.renderFastMenuItems()}
                </Menu>
            </div>
        );
    }

    renderFastMenuItems() {
        const { speeds } = this.props;

        return speeds.map(({ label, value }, key) => (
            <MenuItem key={key} onClick={this.handleChangeSpeed(value)} >
                {label}
            </MenuItem>
        ))
    }
}

SeekBar.propTypes = {
    startTime: PropTypes.number,
    endTime: PropTypes.number,
    currentTime: PropTypes.number,
    className: PropTypes.string,
    onChangeTime: PropTypes.func,
    onPlay: PropTypes.func,
    onPause: PropTypes.func,
    onFollow: PropTypes.func,
    following: PropTypes.bool,
    speeds: PropTypes.arrayOf(PropTypes.shape({
        label: PropTypes.string,
        value: PropTypes.value
    })).isRequired,
    speed: PropTypes.number,
    onChangeStartTime:PropTypes.func,
    onChangeEndTime: PropTypes.func,
    fresh:PropTypes.bool,
    onChangeSpeed: PropTypes.func,
    playing:PropTypes.bool
}

export default withStyles(styles)(SeekBar);