import React,{Component} from 'react';
import DateFnsUtils from '@date-io/moment';
import moment from 'moment';
import { MuiPickersUtilsProvider, InlineDatePicker as MuiDatePicker } from 'material-ui-pickers';
import withStyles from '@material-ui/core/styles/withStyles';

const styles = theme=>({
    white:{
        background:theme.palette.common.white
    }
});

class DatePicker extends Component{

    constructor(props){
        super(props);

        if(!props.value){
            props.onChange(moment().format('YYYY-MM-DD'));
        }
    }
 
    render(){
        const {onChange,label,value,classes,name,helperText,error,required} = this.props;

        let format = undefined;
        const regex = /month/gi;

        if((name&&name.match(regex))||(label&&label.match(regex))){
            format = "YYYY MMMM";
        }
        
        return(
            <MuiPickersUtilsProvider utils={DateFnsUtils}>
                <div>
                    <MuiDatePicker
                        margin="dense"
                        label={label}
                        value={value==''?undefined:moment(value)}
                        onChange={value=>onChange(value.format("YYYY-MM-DD"))}
                        fullWidth
                        variant="outlined"
                        helperText={helperText}
                        error={error}
                        InputProps={{
                            className:classes.white,
                        }}
                        format={format}
                        required={required}
                    />
                </div>
            </MuiPickersUtilsProvider>
        )
    }
}

export default withStyles(styles) (DatePicker);