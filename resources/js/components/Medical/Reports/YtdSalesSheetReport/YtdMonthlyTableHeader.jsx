import React, {Component} from "react";
import PropTypes from "prop-types";
import TableHead from "@material-ui/core/TableHead";
import TableCell from "@material-ui/core/TableCell";
import TableRow from "@material-ui/core/TableRow";

class YtdMonthlyTableHeader extends Component{
     render(){

          const {className} = this.props;
  
          return (
              <TableHead>
                  <TableRow>
                      <TableCell
                      align='center'
                      padding='dense'
                      className={className}
                      colSpan={10}
                      >Monthly Sales sheet
                      </TableCell>
                  </TableRow>
                  <TableRow>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                      >Product
                      </TableCell>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                      >Target Qty
                      </TableCell>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                      >Achiev. Qty
                      </TableCell>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                      >Target
                      Value Rs.
                      </TableCell>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                      >Achiev.
                      Value Rs.
                      </TableCell>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                      >Achi. %
                      </TableCell>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                      >Defict QTY
                      </TableCell>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                      >Value
                      </TableCell>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                      >Last Year Same Month
                      </TableCell>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                      >Growth %
                      </TableCell>
                  </TableRow>
              </TableHead>
          );
      }
}

YtdMonthlyTableHeader.propTypes = {
     className: PropTypes.string,
}

export default YtdMonthlyTableHeader;