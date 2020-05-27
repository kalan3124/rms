import React, {Component} from "react";
import PropTypes from "prop-types";
import TableHead from "@material-ui/core/TableHead";
import TableCell from "@material-ui/core/TableCell";
import TableRow from "@material-ui/core/TableRow";

class TableHeader extends Component{
     render(){

          const {className} = this.props;
  
          return (
              <TableHead>
                    <TableRow>
                         <TableCell
                              align='center'
                              padding='dense'
                              colSpan={3}
                              className={className}
                         >
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              colSpan={1}
                              className={className}
                         >
                              NOD
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              colSpan={1}
                              className={className}
                         >
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              colSpan={1}
                              className={className}
                         >
                              NOD
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              colSpan={1}
                              className={className}
                         >
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              colSpan={2}
                              className={className}
                         >
                              Variance
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              colSpan={1}
                              // className={className}
                         >

                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              colSpan={2}
                              className={className}
                         >
                         Last Year Sales
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              colSpan={2}
                              className={className}
                         >

                         </TableCell>
                    </TableRow>
                    <TableRow>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >Team
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >Product Code
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >Product Name
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >December Net Qty
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >December Sales Value
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >January Net Qty
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >January Sales Value
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >Qty
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
                         >

                         </TableCell>

                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >January Net Qty
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >January Sales Value
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >Qty Growth
                         </TableCell>
                         <TableCell
                              align='center'
                              padding='dense'
                              className={className}
                         >Value Growth
                         </TableCell>
                    </TableRow>
              </TableHead>
          );
      }
}
TableHeader.propTypes = {
     className: PropTypes.string,
}
export default TableHeader;