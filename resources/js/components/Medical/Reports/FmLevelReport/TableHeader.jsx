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
                          style={{width:100}}
                      >Date
                      </TableCell>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                          style={{width:100}}
                      >Plan Type
                      </TableCell>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                          style={{width:200}}
                      >Mr
                      </TableCell>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                          style={{width:400}}
                      >TownWork
                      </TableCell>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                          style={{width:100}}
                      >Km(s)
                      </TableCell>
                      <TableCell
                          align='center'
                          padding='dense'
                          className={className}
                          style={{width:100}}
                      >Station
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