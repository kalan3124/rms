import React,{Component} from 'react';
import DateFnsUtils from '@date-io/moment';
import { MuiPickersUtilsProvider, TimePicker as MuiTimePicker } from 'material-ui-pickers';
import moment from "moment";

const today = moment().format("YYYY-MM-DD");

class TimePicker extends Component{
 
    componentDidMount(){
        this.props.onChange(moment());
    }
    
    render(){
        const {onChange,label,value} = this.props;

        return(
            <MuiPickersUtilsProvider utils={DateFnsUtils}>
                <div>
                    <MuiTimePicker
                        margin="dense"
                        label={label}
                        value={value==''?undefined:moment(today+" "+value)}
                        onChange={value=>onChange(value.format("HH:mm:ss"))}
                        fullWidth
                        variant="outlined"
                    />
                </div>
            </MuiPickersUtilsProvider>
        )
    }
}

export default TimePicker;