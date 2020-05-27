import React,{Component} from 'react';
import DateFnsUtils from '@date-io/moment';
import moment from 'moment';
import { MuiPickersUtilsProvider, InlineDateTimePicker as MuiDateTimePicker } from 'material-ui-pickers';

class DateTimePicker extends Component{
 
    componentDidMount(){
        this.props.onChange(moment());
    }
    
    render(){
        const {onChange,label,value,helperText,error,required} = this.props;

        return(
            <MuiPickersUtilsProvider utils={DateFnsUtils}>
                <div>
                    <MuiDateTimePicker
                        margin="dense"
                        label={label}
                        value={value==''?undefined:moment(value)}
                        onChange={value=>onChange(value.format("YYYY-MM-DD HH:mm:ss"))}
                        fullWidth
                        helperText={helperText}
                        error={error}
                        variant="outlined"
                        required={required}
                    />
                </div>
            </MuiPickersUtilsProvider>
        )
    }
}

export default DateTimePicker;