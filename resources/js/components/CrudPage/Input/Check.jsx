import React from 'react';

import Checkbox from '@material-ui/core/Checkbox';
import FormGroup from '@material-ui/core/FormGroup';
import FormControlLabel from '@material-ui/core/FormControlLabel';

const Check = ({ value, onChange,label }) => (
    <FormGroup row>
        <FormControlLabel
            control={
                <Checkbox
                    checked={Boolean(value)}
                    onChange={e=>onChange(e.target.checked?1:0) }
                />
            }
            label={label}
        />
    </FormGroup>
)

export default Check;