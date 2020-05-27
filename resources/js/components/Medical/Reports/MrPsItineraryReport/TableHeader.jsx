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
                          className={className}
                      >Date
                      </TableCell>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                      >Plan Type
                      </TableCell>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                      >TownWork
                      </TableCell>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                      >Km(s)
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