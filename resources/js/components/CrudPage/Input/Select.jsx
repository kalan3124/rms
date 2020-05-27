import React, { Component } from 'react';

import FormControl from '@material-ui/core/FormControl';
import InputLabel from '@material-ui/core/InputLabel';
import MenuItem from '@material-ui/core/MenuItem';
import OutlinedInput from '@material-ui/core/OutlinedInput';
import { default as MSelect } from '@material-ui/core/Select';
import FormHelperText from '@material-ui/core/FormHelperText';

class Select extends Component {
    render() {

        const { label, name, value, fullWidth, margin, options,error,helperText,required, className } = this.props;

        let modedoptions = (typeof options == 'undefined') ? {} : { ...options };

        return (
            <FormControl
                fullWidth={fullWidth}
                margin={margin}
                variant="outlined"
                error={error}
                required={required}
                className={className}
            >
                <InputLabel required={required} error={error} ref={ref => {this.InputLabelRef = ref;}} htmlFor={name + '-select'}>{label}</InputLabel>
                <MSelect
                    required={required}
                    value={typeof value == 'undefined'||typeof value.value =='undefined' ? '' : value.value}
                    onChange={(this.handleChange).bind(this)}
                    input={
                        <OutlinedInput labelWidth={name.length*8} name={name} id={name + '-select'} />
                    }
                >
                    <MenuItem key={0} value="">Select A {label}</MenuItem>
                    {Object.keys(modedoptions).map(key => (
                        <MenuItem key={key} value={key}>{modedoptions[key]}</MenuItem>
                    ))}
                </MSelect>
                <FormHelperText required={required} error={error} >{helperText}</FormHelperText>
            </FormControl>
        )
    }

    handleChange({target}){
        const {options,onChange} = this.props;

        if(typeof onChange=='undefined') return;

        onChange({
            value:target.value,
            label:options[target.value]
        });
    }
}

export default Select;