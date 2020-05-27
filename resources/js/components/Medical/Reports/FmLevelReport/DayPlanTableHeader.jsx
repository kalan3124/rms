import React, {Component} from "react";
import PropTypes from "prop-types";
import TableHead from "@material-ui/core/TableHead";
import TableCell from "@material-ui/core/TableCell";
import TableRow from "@material-ui/core/TableRow";

class DayPlanTableHeader extends Component{

     render(){

          const {className} = this.props;
  
          return (
              <TableHead>
                  <TableRow>
                    <TableCell
                        align='center'
                        padding='dense'
                        colSpan={2}
                        className={className}
                    >
                    Day Plan Summary
                    </TableCell>
                </TableRow>
              </TableHead>
          );
      }
}

DayPlanTableHeader.propTypes = {
     className: PropTypes.string,
 }
 
 export default DayPlanTableHeader;