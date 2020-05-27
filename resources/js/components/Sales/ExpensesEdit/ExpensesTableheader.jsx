import React, { Component } from "react";
import withStyles from "@material-ui/core/styles/withStyles";

import TableHead from "@material-ui/core/TableHead";
import TableCell from "@material-ui/core/TableCell";
import TableRow from "@material-ui/core/TableRow";

const styles = theme => ({
     darkGrey: {
          background: theme.palette.grey[600],
          color: theme.palette.common.white,
          border: '1px solid #fff'
     }
});

class ExpensesTableheader extends Component {
     render() {
          const { classes } = this.props;
          return [
               <TableHead key={'newHeader'}>
                    <TableRow key={'newRow'}>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.darkGrey}
                         >
                              Expenses Date
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.darkGrey}
                         >
                              Day Type
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.darkGrey}
                         >
                              Route
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.darkGrey}
                         >
                              Bata Type
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.darkGrey}
                         >
                              Stationery
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.darkGrey}
                         >
                              Default Actual Mileage
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.darkGrey}
                         >
                              Actual Mileage
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.darkGrey}
                         >
                              Mileage Amount
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.darkGrey}
                         >
                              Parking
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.darkGrey}
                         >
                              Mileage
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.darkGrey}
                         >
                              Remark
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.darkGrey}
                         >
                              App Version
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={classes.darkGrey}
                         >
                              Approval
                         </TableCell>
                    </TableRow>
               </TableHead>
          ];
     }
}

export default withStyles(styles)(ExpensesTableheader);