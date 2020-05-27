import React, { Component } from 'react';
import PropTypes from "prop-types";
import moment from 'moment';

import withStyles from '@material-ui/core/styles/withStyles';
import Table from "@material-ui/core/Table";
import TableHead from "@material-ui/core/TableHead";
import TableRow from "@material-ui/core/TableRow";
import TableBody from "@material-ui/core/TableBody";
import Paper from "@material-ui/core/Paper";
import TableCell from "@material-ui/core/TableCell";
import Chip from "@material-ui/core/Chip";

const now = moment();

const styles = theme => ({
  paper: {
    maxHeight: '90vh',
    overflowY: 'auto'
  },
  darkHeader:{
    background:'#404040',
    color:theme.palette.common.white
  },
  chip:{
    padding:0,
    color:theme.palette.common.white,
    fontSize:'.8em',
    margin:theme.spacing.unit/4,
    height:theme.spacing.unit*2
  }
})

class ItineraryCalendar extends Component {
  render() {
    const { classes } = this.props;

    return (
      <Paper className={classes.paper} >
        <Table>
          <TableHead >
            <TableRow>
              <TableCell className={classes.darkHeader} >
                Date
              </TableCell>
              <TableCell className={classes.darkHeader}>
                Description
              </TableCell>
              <TableCell className={classes.darkHeader}>
                Station
              </TableCell>
              <TableCell className={classes.darkHeader}>
                Day Types
              </TableCell>
              <TableCell className={classes.darkHeader}>
                Mileage
              </TableCell>
              <TableCell className={classes.darkHeader}>
                Towns
              </TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {this.renderResults()}
          </TableBody>
        </Table>
      </Paper>
    )
  }

  renderResults() {
    const { dates,classes } = this.props;

    return Object.values(dates).map((row, key) => (
      <TableRow key={key} >
        <TableCell>
          {row.date}
        </TableCell>
        <TableCell>
          {row.description}
        </TableCell>
        <TableCell>
          {row.bataType ? row.bataType.label : null}
        </TableCell>
        <TableCell>
          {row.dayTypes?row.dayTypes.map(({ label,color },key) => (
            <Chip className={classes.chip} key={key} label={label} style={{background:color}} />
          )):null}
        </TableCell>
        <TableCell>
          {row.mileage}
        </TableCell>
        <TableCell>
          {row.areas?row.areas.map(({ label }) => label).join(', '):null}
        </TableCell>
      </TableRow>
    ))
  }
}

ItineraryCalendar.propTypes = {
  dates: PropTypes.object,
  yearMonth: PropTypes.string
}


export default withStyles(styles)(ItineraryCalendar);