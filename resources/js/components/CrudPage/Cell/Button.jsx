import React, {Component} from "react";
import {connect} from 'react-redux';
import MuiButton from "@material-ui/core/Button";
import { PropNumOrString } from "../../../constants/propTypes";
import { alertDialog } from "../../../actions/Dialogs";
import PropTypes from "prop-types";
import Axios from "axios";
import { APP_URL } from "../../../constants/config";

const mapStateToProps = state=>({
    ...state.App,
})

const mapDispatchToProps = dispatch=>({
    onAlert:(message,type)=>dispatch(alertDialog(message,type))
});

class Button extends Component{

    constructor(props){
        super(props);

        this.handleClick = this.handleClick.bind(this);

        this.state = {
            fired:false
        };
    }

    handleClick(){
        const {value,onAlert,link} = this.props;

        Axios.post(APP_URL+'api/web/'+link, {value}).then(({data})=>{
            if(data.success){
                if(typeof data.link==='undefined'){
                    onAlert(data.message,'success');
                    this.setState({fired:true});
                } else {
                    window.open(data.link,'_blank');
                }
            } else {
                onAlert(data.message,'error');
            }
        }).catch(err=>{
            onAlert(err.response.data.message,'error');
        })
    }

    render(){
        const {value,label} = this.props;
        const {fired} = this.state;

        if(!value|fired)
            return null;

        return (
            <MuiButton onClick={this.handleClick} variant="contained" color="secondary">{label}</MuiButton>
        );
    }
}

Button.propTypes = {
    value: PropNumOrString,
    onAlert: PropTypes.func
}

export default connect(mapStateToProps,mapDispatchToProps)(Button);