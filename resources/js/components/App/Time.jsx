import React, {Component} from "react";
import Moment from 'react-moment';


class Time extends Component{
    constructor(props){
        super(props);

        this.state= {
            time:new Date(Date.now()-this.props.timeDif)
        }

    }

    componentDidMount(){

        this.setTime()
    }

    componentWillUnmount(){
        const {timeout} = this.state;

        if(!timeout) return;

        window.clearTimeout(timeout);
    }

    setTime(){
        const {timeDif} = this.props;

        let timeout = window.setTimeout(()=>{
            this.setTime();
        },1000);

        this.setState({
            time:new Date(Date.now()-timeDif),
            timeout
        })
    }

    render(){
        const {time} = this.state;

        if(!time) return null;

        return (
            <Moment interval={1000} parse="YYYY-MM-DD HH:mm:ss"  format="HH:mm:ss">{time}</Moment>
        );
    }
}

export default Time;