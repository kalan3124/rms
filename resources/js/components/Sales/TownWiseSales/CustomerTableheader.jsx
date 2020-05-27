import React, { Component } from "react";
import PropTypes from "prop-types";
import TableHead from "@material-ui/core/TableHead";
import TableCell from "@material-ui/core/TableCell";
import TableRow from "@material-ui/core/TableRow";

class CustomerTableheader extends Component {

     render() {

          const { className } = this.props;

          return (
               <TableHead>
                    <TableRow>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >Customer
                      </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >Customer Name
                      </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >Target
                      </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >Achievemant
                      </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >%
                      </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >Balance
                      </TableCell>
                    </TableRow>
               </TableHead>
          );
     }

}

export default CustomerTableheader;