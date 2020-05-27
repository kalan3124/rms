import React, {Component} from "react";
import CrudForm from "../../CrudPage/CrudForm";
import PropTypes from "prop-types";

const initialState = {
    mileage:0.00,
    bataType:undefined
};

class OtherDayForm extends Component{
    constructor(props){
        super(props);

        this.state={...initialState};

        this.handleChange = this.handleChange.bind(this);
        this.handleClearForm = this.handleClearForm.bind(this);
        this.handleSubmitForm = this.handleSubmitForm.bind(this);
    }

    handleChange({mileage,bataType}){
        this.setState({
            mileage,
            bataType
        })
    }

    handleClearForm(){
        this.setState(initialState)
    }

    handleSubmitForm(){
        const {onChange} = this.props;
        const {mileage,bataType} = this.state;

        onChange({mileage,bataType});

        this.handleClearForm()
    }
    
    render(){
        const {value,userType,user} = this.props;
        const {mileage,bataType} = this.state;

        let modedValue = typeof value=='undefined'?
            {...initialState,mileage,bataType}:
            {
                mileage:mileage?mileage:value.mileage,  
                bataType:bataType?bataType:value.bataType,  
            };

        return (
            <div>
                <CrudForm
                    title="Mileage and bata type"
                    inputs={{
                        mileage:{
                            label:"Mileage",
                            type:"number"
                        },
                        bataType:{
                            label:"Bata Type",
                            type:"ajax_dropdown",
                            link:"bata_type",
                            where:{
                                bt_type:userType,
                                user
                            }
                        }
                    }}
                    structure={[["mileage","bataType"]]}
                    onInputChange={this.handleChange}
                    values={modedValue}
                    onSubmit={this.handleSubmitForm}
                    onClear={this.handleClearForm}
                    mode="create"
                    disableSearch
                />
            </div>
        );
    }
}

OtherDayForm.propTypes = {
    value:PropTypes.shape({
        value:PropTypes.number,
        label:PropTypes.string
    }),
    onChange:PropTypes.func
}

export default OtherDayForm;