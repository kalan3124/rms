import React, {Component} from "react";
import PropTypes from "prop-types";
import TableHead from "@material-ui/core/TableHead";
import TableCell from "@material-ui/core/TableCell";
import TableRow from "@material-ui/core/TableRow";

class KiloTableHeader extends Component{
    render(){
        const {style} = this.props;

        return (
            <TableHead>
                <TableRow>
                    <TableCell
                        align='center'
                        padding='dense'
                        colSpan={2}
                        style={style}
                    >
                        Kilometers
                    </TableCell>
                </TableRow>
            </TableHead>
        );
    }

}

KiloTableHeader.propTypes = {
    types:PropTypes.object,
    className: PropTypes.string,
    bataCategories:PropTypes.array
}

export default KiloTableHeader;