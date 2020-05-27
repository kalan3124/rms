import React, { Component } from 'react';
import DayCalendar from 'rc-calendar';
import PropTypes from "prop-types";
import moment from 'moment';

import Card from '@material-ui/core/Card';
import withStyles from '@material-ui/core/styles/withStyles';
import Typography from '@material-ui/core/Typography';
import Chip from '@material-ui/core/Chip';
import PlusIcon from "@material-ui/icons/Add";
import PersonIcon from "@material-ui/icons/PersonAdd";
import ListIcon from "@material-ui/icons/PlaylistAddCheck";
import CloseIcon from "@material-ui/icons/Close";
import MoneyIcon from "@material-ui/icons/Money";
import blue from "@material-ui/core/colors/blue";
import red from "@material-ui/core/colors/red";
import Tooltip from '@material-ui/core/Tooltip';

const now = moment();

const styles = theme => ({
  date: {
    width: theme.spacing.unit * 16,
    height: theme.spacing.unit * 12,
    padding: theme.spacing.unit * 3,
    margin: 'auto',
    position: 'relative',
    marginTop: theme.spacing.unit / 2
  },
  calendar: {
    width: Math.round(theme.spacing.unit * 12 * 7 * 1.5),
    height: 'auto',
    [theme.breakpoints.down('md')]:{
      width:"auto"
    }
  },
  pastMonthDate: {
    color: theme.palette.grey[500]
  },
  chip: {
    padding: 0,
    height: theme.spacing.unit * 2,
    fontSize: '0.7em',
    color: theme.palette.common.white,
    background: theme.palette.grey[500],
    marginLeft: theme.spacing.unit / 4,
    cursor: 'pointer'
  },
  chipContainer: {
    position: 'absolute',
    top: 0,
    left: 0
  },
  chipLabel: {
    paddingLeft: theme.spacing.unit / 2,
    paddingRight: theme.spacing.unit / 2,
  },
  description: {
    background: theme.palette.grey[300],
    position: 'absolute',
    bottom: 0,
    width: '100%',
    left: 0,
    height: theme.spacing.unit * 4,
    padding: theme.spacing.unit / 2,
    fontSize: '.8em',
    textAlign: "center"
  },
  grow: {
    flexGrow: 1
  },
  plusIcon: {
    position: "absolute",
    cursor: "pointer",
    top: theme.spacing.unit / 2,
    right: 2,
    color: blue[500],
    background: theme.palette.common.white,
    borderRadius: '100%',
    "&:hover": {
      background: blue[500],
      color: theme.palette.common.white
    }
  },
  personIcon: {
    position: "absolute",
    color: blue[500],
    top: theme.spacing.unit / 2,
    left: 2,
    cursor: "pointer",
    background: theme.palette.common.white,
    borderRadius: '100%',
    "&:hover": {
      background: blue[500],
      color: theme.palette.common.white
    }
  },
  selectedPlusIcon: {
    position: "absolute",
    cursor: "pointer",
    top: theme.spacing.unit / 2,
    right: 2,
    borderRadius: '100%',
    background: blue[500],
    color: theme.palette.common.white
  },
  selectedPersonIcon: {
    cursor: "pointer",
    borderRadius: '100%',
    background: blue[500],
    color: theme.palette.common.white
  },
  listIcon: {
    position: "absolute",
    cursor: "pointer",
    top: theme.spacing.unit / 2,
    right: 50,
    color: blue[500],
    background: theme.palette.common.white,
    borderRadius: '100%',
    "&:hover": {
      background: blue[500],
      color: theme.palette.common.white
    }
  },
  selectedListIcon: {
    position: "absolute",
    cursor: "pointer",
    top: theme.spacing.unit / 2,
    right: 50,
    color: theme.palette.common.white,
    background: blue[500],
    borderRadius: '100%',
    "&:hover": {
      background: blue[500],
      color: theme.palette.common.white
    }
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
  }
})



class Calendar extends Component {

  constructor(props) {
    super(props);

    this.renderDate = this.renderDate.bind(this)
  }

  render() {
    const { classes, onDateSelect } = this.props;

    return (
      <div>
        <DayCalendar
          fullscreen
          defaultValue={now}
          type='month'
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

  handleDayTypeChange(dateDetails, typeId) {
    const { onDayTypeChange,yearMonth } = this.props;

    if(dateDetails&&dateDetails.forbidden) return null;
    

    // const b = moment();

    // if(yearMonth.trim()+'-'+dateDetails.date.toString().padStart(2,"0").trim()<b.format('YYYY-MM-DD').trim()){
    //   return null;
    // }

    onDayTypeChange(dateDetails, typeId)
  }

  handleListIconClick(details) {
    const { onDescriptionFocus } = this.props;
    return e => {
      onDescriptionFocus(details);
    }
  }

  handleAddButtonClick(details) {
    const { onCreateRoutePlan } = this.props;

    return e => {
      onCreateRoutePlan(details);
    }
  }

  handlePersonAddButtonClick(details) {
    const { onJointFieldWorker } = this.props;

    return e => onJointFieldWorker(details);
  }

  handleClearDate(details) {
    const { onClearDate } = this.props;

    return e => onClearDate(details)
  }

  handleMileageClick(details) {
    const { onAddMileage } = this.props;

    return e => onAddMileage(details)
  }

  renderDate(current, value) {

    const { classes, yearMonth, dates } = this.props;

    let dateDetails = dates[parseInt(current.format('DD'))];

    let className;

    if (current.format('YYYY-MM') != yearMonth) {
      className = classes.pastMonthDate;
    } else if (dateDetails && dateDetails.special) {
      className = classes.specialDay;
    } else if (current.isoWeekday() == 6) {
      className = classes.saturday;
    } else if (current.isoWeekday() == 7) {
      className = classes.sunday;
    }
  

    return (
      <Card className={classes.date}>
        <Tooltip title={dateDetails && dateDetails.special ? dateDetails.special : ( dateDetails? "Mileage:- "+dateDetails.mileage+" | Bata Type:-"+dateDetails.bataTypeName:current.format('dddd'))}>
          <Typography className={className} align="center" variant="h4">
            {current.format('DD')}
          </Typography>
        </Tooltip>
        <div className={classes.chipContainer} >
          {this.renderChips(dateDetails, current)}
        </div>
        {this.renderClearButton(dateDetails, current)}
        {this.renderDescription(dateDetails, current)}
      </Card>
    )
  }

  renderClearButton(dateDetails, date) {
    const { classes, yearMonth } = this.props;

    if (date.format('YYYY-MM') != yearMonth) return null;

    if (!dateDetails || !dateDetails.types || !dateDetails.types.length) return null;

    if(dateDetails&&dateDetails.forbidden) return null;

    return (
      <div onClick={this.handleClearDate(dateDetails)} className={classes.clearButton}>
        <CloseIcon color="inherit" />
      </div>
    )
  }

  renderDescription(dateDetails, date) {
    const { yearMonth, classes, dayTypes } = this.props;

    if (date.format('YYYY-MM') != yearMonth) return null;

    if (!dateDetails) return null;

    let fieldWorkingDate = false;

    let working = false;

    Object.keys(dayTypes).forEach(typeId => {
      if (dateDetails.types.includes(typeId)) {
        let type = dayTypes[typeId];
        if (type.fieldWorking) fieldWorkingDate = true;
        if (type.working) working = true;
      }
    });

    if (!fieldWorkingDate && !working) return null;

    let children = dateDetails.description ? dateDetails.description.label : null;

    let modedDateDetails = this.getFormatedDateDetails(dateDetails, date);

    if(dateDetails.changedRoute){
      return (
        <div className={classes.description}>
          <Tooltip title={dateDetails.changedRoute.areas.map(area=>area.label).join(', ')}>
            <div>Changed Plan</div>
          </Tooltip>
        </div>
      )
    }else if (working && !fieldWorkingDate) {
      return (
        <div className={classes.description}>
          <Tooltip title="Add Mileage and bata type">
            <MoneyIcon onClick={this.handleMileageClick(modedDateDetails)} className={!modedDateDetails.otherDay ? classes.listIcon : classes.selectedListIcon} />
          </Tooltip>
        </div>
      );
    } else {
      return (
        <div className={classes.description}>
          {this.renderPersonIcon(children, modedDateDetails)}
          {this.renderListIcon(children, modedDateDetails)}
          {this.renderPlusButton(children, modedDateDetails)}
        </div>
      );
    }
  }

  renderListIcon(children, details) {
    const { classes,yearMonth } = this.props;

    // const a = moment(yearMonth+'-'+details.date.toString().padStart(2,"0"));
    // const b = moment();
    if (children || details.additionalValues || details.joinFieldWorker || details.changedRoute) return children;

    // if(a.format('YYYY-MM-DD')<b.format('YYYY-MM-DD')){
    //   return null;
    // }
    if(details&&details.forbidden) return null;

    
    return (
      <Tooltip title="Select a standard itinerary">
        <ListIcon onClick={this.handleListIconClick(details)} className={classes.listIcon} />
      </Tooltip>
    );
  }

  renderPersonIcon(children, details) {
    const { classes, type ,yearMonth} = this.props;

    // const a = moment(yearMonth+'-'+details.date.toString().padStart(2,"0"));
    // const b = moment();

    if (type != "fm" || details.additionalValues || children || details.changedRoute) return null;

    if (details.joinFieldWorker && details.joinFieldWorker.jointFieldWorker) {
      return (
        <Tooltip title={"JFW - " + details.joinFieldWorker.jointFieldWorker.label}>
          <div>{"JFW - " + details.joinFieldWorker.jointFieldWorker.label}</div>
        </Tooltip>
      );
    }

    if(details&&details.forbidden) return null;

    // if(a.format('YYYY-MM-DD')<b.format('YYYY-MM-DD')){
    //   return null;
    // }
    
    return (
      <Tooltip title="Add Join Field Worker">
        <PersonIcon onClick={this.handlePersonAddButtonClick(details)} className={!details.joinFieldWorker ? classes.personIcon : classes.selectedPersonIcon} />
      </Tooltip>
    )
  }

  renderPlusButton(children, details) {

    const { type, classes,yearMonth } = this.props;

    // const a = moment(yearMonth+'-'+details.date.toString().padStart(2,"0"));
    // const b = moment();
    
    if (children || details.joinFieldWorker || details.changedRoute) return null;

    if (details.additionalValues) {
      const areas = details.additionalValues.areas;
      
      if(typeof(areas) == "undefined") return "ARP - Not Selected";

      if (typeof(areas) != "undefined"){  
        return (
          <Tooltip title={"ARP - " + areas.map(area => area.label).join(", ")}>
            <div>{"ARP - " + details.additionalValues.description }</div>
          </Tooltip>
        );
      } 
    } 

    if(details&&details.forbidden) return null;

    // if(a.format('YYYY-MM-DD')<b.format('YYYY-MM-DD')){
    //   return null;
    // }

    return (
      <Tooltip title="Create new route plan">
        <PlusIcon onClick={this.handleAddButtonClick(details)} className={details.additionalValues ? classes.selectedPlusIcon : classes.plusIcon} />
      </Tooltip>
    );
  }

  renderChips(dateDetails, date) {
    const { classes, dayTypes, yearMonth } = this.props;

    if (date.format('YYYY-MM') != yearMonth) return null;

    if (!dayTypes) return null;

    let modedDateDetails = this.getFormatedDateDetails(dateDetails, date);

    return Object.keys(dayTypes).map((typeId, i) => {
      const type = dayTypes[typeId];

      let background;

      background = modedDateDetails.types.includes(typeId) ? type.color.toLowerCase() : undefined;

      return (
        <Chip onClick={e => this.handleDayTypeChange(modedDateDetails, typeId)} classes={{ label: classes.chipLabel }} style={{ background }} className={classes.chip} key={i} label={type.label} />
      )
    })

  }

  getFormatedDateDetails(dateDetails, date) {

    let modedDateDetails = { ...dateDetails };

    if (!dateDetails) modedDateDetails = {
      date: parseInt(date.format('DD')),
      types: []
    }

    return modedDateDetails;
  }
}

Calendar.propTypes = {
  type: PropTypes.oneOf(["mr", "fm"]),
  onCreateRoutePlan: PropTypes.func,
  onJointFieldWorker: PropTypes.func,
  onClearDate: PropTypes.func,
  onAddMileage: PropTypes.func
}


export default withStyles(styles)(Calendar);