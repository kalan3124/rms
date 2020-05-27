import React, {Component} from "react";
import PropTypes from "prop-types";
import TableHead from "@material-ui/core/TableHead";
import TableCell from "@material-ui/core/TableCell";
import TableRow from "@material-ui/core/TableRow";

class JfwTableHeader extends Component{

     render(){

          const {className} = this.props;
  
          return (
              <TableHead>
                  <TableRow>
                    <TableCell
                        align='center'
                        padding='dense'
                        className={className}
                    >
                    JFW DISTRIBUTION WITH MR/TM'S
                    </TableCell>
                </TableRow>
              </TableHead>
          );
      }
}

JfwTableHeader.propTypes = {
     className: PropTypes.string,
 }
 
 export default JfwTableHeader;