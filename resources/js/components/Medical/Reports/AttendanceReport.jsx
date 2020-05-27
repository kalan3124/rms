import React , {Component} from 'react';

import Report from '../Report/Report';

class AttendanceReport extends Component{
    render(){
        <Report
            sortBy={''}
            sortMode={'desc'}
            results={[]}
            resultCount={0}
            columns={{
                'name':{
                    label:'juunjknkj',
                    type:'text'
                }
            }}
            title="Attendance Report"
            inputs={{}}
            page={1}
            inputsStructure={[]}
        />
    }
}

export default AttendanceReport;