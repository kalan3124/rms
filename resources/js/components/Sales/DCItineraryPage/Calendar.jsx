import React, { Component } from "react";
import DayCalendar from "rc-calendar";
import PropTypes from "prop-types";
import moment from "moment";

import Card from "@material-ui/core/Card";
import withStyles from "@material-ui/core/styles/withStyles";
import Typography from "@material-ui/core/Typography";
import Chip from "@material-ui/core/Chip";
import CloseIcon from "@material-ui/icons/Close";
import red from "@material-ui/core/colors/red";
import Tooltip from "@material-ui/core/Tooltip";
import MoneyIcon from "@material-ui/icons/Money";
import PersonIcon from "@material-ui/icons/Person";
import DirectionsIcon from "@material-ui/icons/Directions";
import blue from "@material-ui/core/colors/blue";

const now = moment();

const styles = theme => ({
    date: {
        width: theme.spacing.unit * 16,
        height: theme.spacing.unit * 12,
        padding: theme.spacing.unit * 3,
        margin: "auto",
        position: "relative",
        marginTop: theme.spacing.unit / 2
    },
    calendar: {
        width: Math.round(theme.spacing.unit * 12 * 7 * 1.5),
        height: "auto",
        margin: "auto",
        marginTop: 8
    },
    pastMonthDate: {
        color: theme.palette.grey[500]
    },
    chip: {
        padding: 0,
        height: theme.spacing.unit * 2,
        fontSize: "0.7em",
        color: theme.palette.common.white,
        background: theme.palette.grey[500],
        marginLeft: theme.spacing.unit / 4,
        cursor: "pointer"
    },
    chipContainer: {
        position: "absolute",
        top: 0,
        left: 0
    },
    chipLabel: {
        paddingLeft: theme.spacing.unit / 2,
        paddingRight: theme.spacing.unit / 2
    },
    clearButton: {
        position: "absolute",
        height: theme.spacing.unit * 4,
        top: theme.spacing.unit * 4,
        right: 0,
        width: theme.spacing.unit * 4,
        padding: theme.spacing.unit / 2,
        background: theme.palette.grey[200],
        borderTopLeftRadius: theme.spacing.unit,
        borderBottomLeftRadius: theme.spacing.unit,
        cursor: "pointer",
        color: red[500],
        "&:hover": {
            color: theme.palette.common.white,
            background: red[500]
        }
    },
    specialDay: {
        color: red[700]
    },
    saturday: {
        color: theme.palette.grey[700]
    },
    sunday: {
        color: red[200]
    },
    description: {
        background: theme.palette.grey[300],
        position: "absolute",
        bottom: 0,
        width: "100%",
        left: 0,
        height: theme.spacing.unit * 4,
        padding: theme.spacing.unit / 2,
        fontSize: ".8em",
        textAlign: "center"
    },

    moneyIcon: {
        position: "absolute",
        cursor: "pointer",
        top: theme.spacing.unit / 2,
        right: 50,
        color: blue[500],
        background: theme.palette.common.white,
        borderRadius: "100%",
        "&:hover": {
            background: blue[500],
            color: theme.palette.common.white
        }
    },
    selectedMoneyIcon: {
        position: "absolute",
        cursor: "pointer",
        top: theme.spacing.unit / 2,
        right: 50,
        color: theme.palette.common.white,
        background: blue[500],
        borderRadius: "100%",
        "&:hover": {
            background: blue[500],
            color: theme.palette.common.white
        }
    },
    personIcon: {
        position: "absolute",
        cursor: "pointer",
        top: theme.spacing.unit / 2,
        left: 2,
        color: blue[500],
        background: theme.palette.common.white,
        borderRadius: "100%",
        "&:hover": {
            background: blue[500],
            color: theme.palette.common.white
        }
    },
    selectedPersonIcon: {
        position: "absolute",
        cursor: "pointer",
        top: theme.spacing.unit / 2,
        left: 2,
        color: theme.palette.common.white,
        background: blue[500],
        borderRadius: "100%",
        "&:hover": {
            background: blue[500],
            color: theme.palette.common.white
        }
    },
    directionsIcon: {
        position: "absolute",
        cursor: "pointer",
        top: theme.spacing.unit / 2,
        right: 2,
        color: blue[500],
        background: theme.palette.common.white,
        borderRadius: "100%",
        "&:hover": {
            background: blue[500],
            color: theme.palette.common.white
        }
    },
    selectedDirectionsIcon: {
        position: "absolute",
        cursor: "pointer",
        top: theme.spacing.unit / 2,
        right: 2,
        borderRadius: "100%",
        background: blue[500],
        color: theme.palette.common.white
    }
});

class Calendar extends Component {
    constructor(props) {
        super(props);

        this.renderDate = this.renderDate.bind(this);
    }

    handleClickChip(date, dayType, select) {
        const { onSelectDayType, onUnselectDayType } = this.props;

        return e => {
            if (select) {
                onUnselectDayType(date.format("D"), dayType);
            } else {
                onSelectDayType(date.format("D"), dayType);
            }
        };
    }

    render() {
        const {
            classes,
            onDateSelect
        } = this.props;

        return (
            <div>
                <DayCalendar
                    fullscreen
                    defaultValue={now}
                    type="month"
                    dateRender={this.renderDate}
                    showToday={false}
                    className={classes.calendar}
                    mode="date"
                    showDateInput={false}
                    onChange={onDateSelect}
                />
            </div>
        );
    }

    renderDate(current, value) {
        const { classes, yearMonth, dates, dayTypes } = this.props;

        let dateDetails = dates[parseInt(current.format("DD"))];
        // console.log(dateDetails);

        let className;

        if (current.format("YYYY-MM") != yearMonth) {
            className = classes.pastMonthDate;
        } else if (dateDetails && dateDetails.special) {
            className = classes.specialDay;
        } else if (current.isoWeekday() == 6) {
            className = classes.saturday;
        } else if (current.isoWeekday() == 7) {
            className = classes.sunday;
        }

        let isWorkingDay = false;
        let isFieldWorkingDay = false;

        for (const dayType of dayTypes) {
            if (dateDetails && dateDetails.dayTypes.includes(dayType.value)) {
                if (dayType.isWorking) {
                    isWorkingDay = true;
                }

                if (dayType.isFieldWorking) {
                    isFieldWorkingDay = true;
                }
            }
        }

        if (dateDetails) {
            dateDetails.isWorking = isWorkingDay;
            dateDetails.isFieldWorking = isFieldWorkingDay;
        }

        return (
            <Card className={classes.date}>
                <Tooltip
                    title={
                        dateDetails && dateDetails.special
                            ? dateDetails.special
                            : dateDetails
                            ? "Mileage:- " +
                              dateDetails.mileage + " | Day Target:-" + dateDetails.day_tar +
                              " | Bata Type:-" +
                              dateDetails.bataTypeName
                            : current.format("dddd")
                    }
                >
                    <Typography
                        className={className}
                        align="center"
                        variant="h4"
                    >
                        {current.format("DD")}
                    </Typography>
                </Tooltip>
                <div className={classes.chipContainer}>
                    {this.renderChips(dateDetails, current)}
                </div>
                {this.renderClearButton(dateDetails, current)}
                {this.renderDescription(dateDetails, current)}
            </Card>
        );
    }

    renderDescription(dateDetails, current) {
        const { classes, yearMonth } = this.props;

        if (current.format("YYYY-MM") != yearMonth) {
            return null;
        }

        if (dateDetails && !dateDetails.isWorking) {
            return null;
        }

        if (!dateDetails || !dateDetails.dayTypes.length) {
            return null;
        }

        return (
            <div className={classes.description}>
                {this.renderMoneyIcon(dateDetails, current)}
                {this.renderRoutesIcon(dateDetails, current)}
                {this.renderPersonIcon(dateDetails, current)}
                {this.renderRoute(dateDetails, current)}
            </div>
        );
    }

    renderRoute(dateDetails, date){
        let desc = "";
        let new_desc = "";
        if(dateDetails.route != null){
            desc = "ARP - "+dateDetails.route.label;

            if(dateDetails.route.label.length >= 4){
                const res = dateDetails.route.label.slice(0, 5);
                new_desc = "ARP - "+res+"...";
            } else {
                new_desc = desc;
            }

        } else {
            desc = "ARP";
        }

        return (
            <Tooltip title={desc}>
              <div>{new_desc}</div>
            </Tooltip>
        );
    }

    renderClearButton(dateDetails, date) {
        const { classes, yearMonth } = this.props;

        if (date.format("YYYY-MM") != yearMonth) return null;

        if (
            !dateDetails ||
            !dateDetails.dayTypes ||
            !dateDetails.dayTypes.length
        )
            return null;

        return (
            <div className={classes.clearButton}>
                <CloseIcon
                    onClick={this.handleClearDate(date)}
                    color="inherit"
                />
            </div>
        );
    }

    renderChips(dateDetails, date) {
        const { classes, dayTypes, yearMonth } = this.props;

        if (date.format("YYYY-MM") != yearMonth) return null;

        if (!dayTypes) return null;

        return dayTypes.map((dayType, i) => {
            let background;

            let selected =
                dateDetails && dateDetails.dayTypes.includes(dayType.value);

            background = selected ? dayType.color.toLowerCase() : undefined;

            return (
                <Chip
                    onClick={this.handleClickChip(date, dayType, selected)}
                    classes={{ label: classes.chipLabel }}
                    style={{ background }}
                    className={classes.chip}
                    key={i}
                    label={dayType.label}
                />
            );
        });
    }

    handleClearDate(date) {
        const { onClear } = this.props;

        return e => {
            if (onClear) {
                onClear(date.format("D"));
            }
        };
    }

    handleModeChange(date, mode) {
        const { onChangeMode } = this.props;

        return e => {
            if (onChangeMode) {
                onChangeMode(date.format("D"), mode);
            }
        };
    }

    renderRoutesIcon(dateDetails, date) {
        const { classes } = this.props;

        if (dateDetails && !dateDetails.isFieldWorking) {
            return null;
        }

        if (!dateDetails || !dateDetails.route) {
            return (
                <Tooltip title="Add Route">
                    <DirectionsIcon
                        onClick={this.handleModeChange(date, 2)}
                        className={classes.directionsIcon}
                    />
                </Tooltip>
            );
        } else {
            return (
                <Tooltip title="Change Route">
                    <DirectionsIcon
                        onClick={this.handleModeChange(date, 2)}
                        className={classes.selectedDirectionsIcon}
                    />
                </Tooltip>
            );
        }
    }

    renderMoneyIcon(dateDetails, date) {
        const { classes } = this.props;
        if (dateDetails && (!dateDetails.isWorking || dateDetails.route)) {
            return null;
        }

        if (
            !dateDetails ||
            (dateDetails.mileage !== "0.00" && !dateDetails.bataType)
        ) {
            return (
                <Tooltip title="Add Mileage and bata type">
                    <MoneyIcon
                        onClick={this.handleModeChange(date, 1)}
                        className={classes.moneyIcon}
                    />
                </Tooltip>
            );
        } else {
            return (
                <Tooltip title="Change Mileage and bata type">
                    <MoneyIcon
                        onClick={this.handleModeChange(date, 1)}
                        className={classes.selectedMoneyIcon}
                    />
                </Tooltip>
            );
        }
    }

    renderPersonIcon(dateDetails, date) {
        const { classes } = this.props;

        if (dateDetails && !dateDetails.isFieldWorking) {
            return null;
        }

        if (!dateDetails || !dateDetails.jointFieldWorkers) {
            return (
                <Tooltip title="Add joint field worker">
                    <PersonIcon
                        onClick={this.handleModeChange(date, 3)}
                        className={classes.personIcon}
                    />
                </Tooltip>
            );
        } else {
            return (
                <Tooltip title="Change joint field worker">
                    <PersonIcon
                        onClick={this.handleModeChange(date, 3)}
                        className={classes.selectedPersonIcon}
                    />
                </Tooltip>
            );
        }
    }
}

Calendar.propTypes = {};

export default withStyles(styles)(Calendar);
