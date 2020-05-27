import React , {Component} from 'react';
import {connect} from 'react-redux';

import Layout from '../../App/Layout';
import DatePicker from '../../CrudPage/Input/DatePicker';
import {changeMonth, submit} from '../../../actions/Distributor/SalesProcess';

import Typography from '@material-ui/core/Typography';
import Divider from '@material-ui/core/Divider';
import Grid from '@material-ui/core/Grid';
import Paper from '@material-ui/core/Paper';
import Button from '@material-ui/core/Button';
import withStyles from '@material-ui/core/styles/withStyles';
import Toolbar from '@material-ui/core/Toolbar';
import LinearProgress from '@material-ui/core/LinearProgress';

const mapStateToProps = state =>({
    ...state.SalesProcess
});

const mapDispatchToProps = dispatch =>({
    onChangeMonth: month=>dispatch(changeMonth(month)),
    onSubmit:(month)=>dispatch(submit(month))
});

const styler = withStyles(theme=>({
    padding: {
        padding: theme.spacing.unit,
    },
    grow:{
        flexGrow:1
    },
    margin: {
        margin: theme.spacing.unit
    }
}))

class SalesProcess extends Component {

    constructor(props){
        super(props);

        this.handleSubmit = this.handleSubmit.bind(this);
    }


    render(){

        const {percentage,month,message,classes,status,onChangeMonth} = this.props;

        return (
            <Layout sidebar={true} >
                <Grid justify="center"  container>
                    <Grid item md={6}>
                        <Paper className={classes.padding} >
                            <Typography align="center" variant="h5" >Sales Data Process</Typography>
                            <Divider />
                            <DatePicker label="Month" value={month} onChange={onChangeMonth} />
                            {typeof percentage==='undefined'?null:
                                <div className={classes.margin} >
                                    <Typography>{message} ({percentage}%)</Typography>
                                    <LinearProgress color={status=='running'?'primary':'secondary'} variant="determinate"  value={percentage} />
                                </div>
                            }
                            <Toolbar variant="dense">
                                <div className={classes.grow} />
                                <Button onClick={this.handleSubmit} variant="contained" color="primary" >Process</Button>
                            </Toolbar>
                        </Paper>
                    </Grid>
                </Grid>
            </Layout>
        )
    }

    handleSubmit(){
        const {month,onSubmit} = this.props;

        onSubmit(month);
    }

}

export default connect(mapStateToProps,mapDispatchToProps) ( styler (SalesProcess));