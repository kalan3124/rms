import React , {Component} from 'react';
import Toolbar from "@material-ui/core/Toolbar";
import TextField from "@material-ui/core/TextField";
import Button from "@material-ui/core/Button";
import withStyles from "@material-ui/core/styles/withStyles";
import CloseIcon from "@material-ui/icons/Close";
import AddIcon from "@material-ui/icons/Add";
import { Typography } from '@material-ui/core';

const styler = withStyles(theme=>({
    margin: {
        margin: theme.spacing.unit
    }
}))

const emptyLine = {
    min:1,
    max:1,
    purchase:1,
    free:1,
    id:0
};

class BonusLine extends Component {

    constructor(props){
        super(props);

        this.handleAddBonusLine = this.handleAddBonusLine.bind(this);

    }

    value(){
        const {value} = this.props;

        return value?value:{lines:{0:{...emptyLine}},lastId:0};
    }

    render(){

        const {classes} = this.props;

        const {lines} = this.value();

        return (
            <div>
                <Typography variant="caption">Ratio</Typography>
                {Object.values(lines).map((line,key)=>(
                    <Toolbar variant="dense" key={key}>
                        <TextField
                            label="Min"
                            type="number"
                            variant="outlined"
                            margin="dense"
                            className={classes.margin}
                            onChange={this.handleChangeQty('min',line.id,key)}
                            value={line.min}
                        />
                        <TextField
                            label="Max"
                            type="number"
                            variant="outlined"
                            margin="dense"
                            className={classes.margin}
                            onChange={this.handleChangeQty('max',line.id,key)}
                            value={line.max}
                        />
                        <TextField
                            label="Purchased"
                            type="number"
                            variant="outlined"
                            margin="dense"
                            className={classes.margin}
                            onChange={this.handleChangeQty('purchase',line.id,key)}
                            value={line.purchase}
                        />
                        <TextField
                            label="Free"
                            type="number"
                            variant="outlined"
                            margin="dense"
                            className={classes.margin}
                            onChange={this.handleChangeQty('free',line.id,key)}
                            value={line.free}
                        />
                        {
                            key+1==Object.keys(lines).length ?
                            <Button
                                color="primary"
                                variant="contained"
                                className={classes.margin}
                                onClick={this.handleAddBonusLine}
                            >
                                <AddIcon/>
                            </Button>:
                            <Button
                                color="secondary"
                                variant="contained"
                                className={classes.margin}
                                onClick={this.handleRemoveLine(line.id)}
                            >
                                <CloseIcon/>
                            </Button>
                        }
                    </Toolbar>
                ))}
            </div>
        )
    }

    handleAddBonusLine(){
        const {lines,lastId} = this.value();

        this.props.onChange({
            lines : {
                ...lines,
                [lastId+1]:{...emptyLine,id:lastId+1}
            },
            lastId: lastId+1
        })
    }

    handleRemoveLine(id){
        return e=>{
            const {lines,lastId} = this.value();

            const modedLines = {...lines};

            delete modedLines[id];


            this.props.onChange({lines:{...modedLines},lastId});

        }
    }


    handleChangeQty(field,id,key){
        return e => {
            let qty = parseInt( e.target.value);
            const {lines,lastId} = this.value();

            if( isNaN( qty) || qty<1)
                return;

            const lastMax = key==0?0:Object.values(lines)[key-1].max;
            const currentMin = Object.values(lines)[key].min;

            if(qty<lastMax+1&&field=='min')
                qty = lastMax+1;

            if(field=='max'&& qty<currentMin+1)
                qty = currentMin +1;

            const modedLines = {
                    ...lines,
                    [id]:{
                        ...lines[id],
                        [field]:qty
                    }
            };

            this.props.onChange({lines:modedLines,lastId:lastId});
        }
    }
}

export default styler (BonusLine);