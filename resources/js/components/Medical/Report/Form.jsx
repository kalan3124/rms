import React, { Component } from 'react';

import Paper from '@material-ui/core/Paper';
import Typography from '@material-ui/core/Typography';
import Divider from '@material-ui/core/Divider';
import Grid from '@material-ui/core/Grid';
import withStyles from '@material-ui/core/styles/withStyles';
import Button from '@material-ui/core/Button';

import Input from '../../CrudPage/Input/Input';

const styles = theme => ({
    blackButton: {
        background: theme.palette.common.black,
        color: theme.palette.common.white
    },
    padding:{
        padding: theme.spacing.unit,
    },
    margin: {
        margin: theme.spacing.unit
    }
})

class Form extends Component {

    constructor(props){
        super(props);

        this.handleSubmit = this.handleSubmit.bind(this);

        this.state = {
            values:{}
        };
    }
    
    renderInput(name, props, size) {

        const { classes } = this.props;

        const {values} = this.state;

        return (
            <Grid className={classes.padding} key={name} item xs={12} md={size}>
                <Input otherValues={values} name={name} value={typeof values[name] == 'undefined' ? '' : values[name]} onChange={(value) => this.handleChangeInput(name, value)} {...props} />
            </Grid>
        )
    }

    render() {
        const { title, inputsStructure, inputs, classes , onDownloadXLSX, onDownloadCSV, onDownloadPDF } = this.props;
        const {values} = this.state;

        return (
            <form onSubmit={this.handleSubmit} >
                <Typography variant="h6" align="center">{title}</Typography>
                <Divider />
                {inputsStructure.map((group, i) => (
                    <Grid key={i} container>
                        {
                            (typeof group == 'string') ?
                                this.renderInput(group, inputs[group], 12)
                                :
                                group.map(name => (
                                    this.renderInput(name, inputs[name], 12 / group.length)
                                ))
                        }
                    </Grid>
                ))}
                <Grid
                    justify="flex-end"
                    container
                >
                    <Grid className={classes.margin} item>
                        <Button onClick={()=>onDownloadPDF(values)} variant="contained" color="primary" margin="dense" >
                            Export (PDF)
                        </Button>
                    </Grid>
                    <Grid className={classes.margin} item>
                        <Button onClick={()=>onDownloadCSV(values)} variant="contained" color="primary" margin="dense" >
                            Export (CSV)
                        </Button>
                    </Grid>
                    <Grid className={classes.margin} item>
                        <Button onClick={()=>onDownloadXLSX(values)} variant="contained" color="primary" margin="dense" >
                            Export (XLSX)
                        </Button>
                    </Grid>
                    <Grid className={classes.margin} item>
                        <Button type="submit" variant="contained" className={ classes.blackButton} margin="dense" >Search</Button>
                    </Grid>
                </Grid>
            </form>
        )
    }

    handleChangeInput(name,value){
        const {values} = this.state;

        let modValues = {...values};

        modValues[name] = value;

        this.setState({values:modValues});
    }

    handleSubmit(e){
        e.preventDefault();

        const {values} = this.state;

        this.props.onSubmit(values)
    }

}

export default withStyles(styles)(Form);