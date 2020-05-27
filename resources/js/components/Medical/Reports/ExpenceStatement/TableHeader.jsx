import React, {Component} from "react";
import PropTypes from "prop-types";
import TableHead from "@material-ui/core/TableHead";
import TableCell from "@material-ui/core/TableCell";
import TableRow from "@material-ui/core/TableRow";

class TableHeader extends Component{
    render(){

        const {types,style,bataCategories} = this.props;

        return (
            <TableHead>
                <TableRow>
                    <TableCell
                        align='center'
                        padding='dense'
                        colSpan={5}
                        style={style}
                    >
                    </TableCell>
                    <TableCell
                        align='center'
                        padding='dense'
                        colSpan={8+Object.keys(types).length+bataCategories.length}
                        style={style}
                    >
                        Type
                    </TableCell>
                    <TableCell
                        align='center'
                        padding='dense'
                        style={style}
                    >
                    </TableCell>
                </TableRow>
                <TableRow>
                    <TableCell
                        align='center'
                        padding='dense'
                        colSpan={5}
                        style={style}
                    >
                    </TableCell>
                    <TableCell
                        align='center'
                        padding='dense'
                        colSpan={bataCategories.length+1}
                        style={style}
                    >
                        Bata (Rs)
                    </TableCell>
                    <TableCell
                        align='center'
                        padding='dense'
                        colSpan={6}
                        style={style}
                    >
                        Mileage pay (Km)
                    </TableCell>
                    <TableCell
                        align='center'
                        padding='dense'
                        style={style}
                        colSpan={Object.keys(types).length+1}
                    >Other
                    </TableCell>
                    <TableCell
                        align='center'
                        padding='dense'
                        style={style}
                    >
                        Grand Total
                    </TableCell>
                </TableRow>
                <TableRow>
                    <TableCell
                        align='center'
                        padding='dense'
                        style={style}
                    >Date
                    </TableCell>
                    <TableCell
                        align='center'
                        padding='dense'
                        style={style}
                    >Town
                    </TableCell>
                    <TableCell
                        align='center'
                        padding='dense'
                        style={style}
                    >Station
                    </TableCell>
                    <TableCell
                        align='center'
                        padding='dense'
                        style={style}
                    >Km(s)
                    </TableCell>
                    <TableCell
                        align='center'
                        padding='dense'
                        style={style}
                    >Base Allowance
                    </TableCell>
                    {this.renderbataCategories()}
                    <TableCell
                        align='center'
                        padding='dense'
                        style={style}
                    >Total
                    </TableCell>
                    <TableCell
                        align='center'
                        padding='dense'
                        style={style}
                    >Mileage
                    </TableCell>
                    <TableCell
                        align='center'
                        padding='dense'
                        style={style}
                    >Mileage Pay
                    </TableCell>
                    <TableCell
                        align='center'
                        padding='dense'
                        style={style}
                    >Addtional Mileage
                    </TableCell>
                    <TableCell
                        align='center'
                        padding='dense'
                        style={style}
                    >Pvt Mileage
                    </TableCell>
                    <TableCell
                        align='center'
                        padding='dense'
                        style={style}
                    >GPS Mileage
                    </TableCell>
                    <TableCell
                        align='center'
                        padding='dense'
                        style={style}
                    >Total
                    </TableCell>
                    {this.renderTypeColumns()}
                    <TableCell
                        align='center'
                        padding='dense'
                        style={style}
                    >Total
                    </TableCell>
                    <TableCell
                        align='center'
                        padding='dense'
                        style={style}
                    >
                    </TableCell>
                </TableRow>
            </TableHead>
        );
    }

    renderbataCategories(){
        const {bataCategories,style} = this.props;

        return bataCategories.map(({value,label})=>(
            <TableCell
                    align='center'
                    padding='dense'
                    style={style}
                    key={value}
            >
                   {label}
            </TableCell>
        ))
    }

    renderTypeColumns(){
        const {types,style} = this.props;

        return Object.keys(types).map((typeId,key)=>{
            const {label} = types[typeId];

            return (
                <TableCell
                    align='center'
                    padding='dense'
                    style={style}
                    key={key}
                >
                   {label}
                </TableCell>
            )
        })
    }
}

TableHeader.propTypes = {
    types:PropTypes.object,
    className: PropTypes.string,
    bataCategories:PropTypes.array
}

export default TableHeader;