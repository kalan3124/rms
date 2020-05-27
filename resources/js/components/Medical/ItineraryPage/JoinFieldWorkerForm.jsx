import React, {Component} from "react";
import PropTypes from "prop-types";
import withStyles from "@material-ui/core/styles/withStyles";
import CrudForm from "../../CrudPage/CrudForm";

const styles = theme=>({
    list:{
        maxHeight:"60vh",
        overflowY:"auto"
    }
});

const initialState = {
    jointFieldWorker:undefined,
    mileage:0.00,
    bataType:undefined
};

class JoinFieldWorkerForm extends Component{

    constructor(props){
        super(props);

        this.state={...initialState};

        this.handleChange = this.handleChange.bind(this);
        this.handleClearForm = this.handleClearForm.bind(this);
        this.handleSubmitForm = this.handleSubmitForm.bind(this);
    }

    handleChange({jointFieldWorker,mileage,bataType}){
        this.setState({
            jointFieldWorker,
            mileage,
            bataType
        })
    }

    handleClearForm(){
        this.setState(initialState)
    }

    handleSubmitForm(){
        const {onChange} = this.props;
        const {jointFieldWorker,mileage,bataType} = this.state;

        onChange({jointFieldWorker,mileage,bataType});

        this.handleClearForm()
    }
    
    render(){
        const {fm,value,date,userType,user} = this.props;
        const {jointFieldWorker,mileage,bataType} = this.state;

        let modedValue = typeof value=='undefined'?
            {...initialState,jointFieldWorker,mileage,bataType}:
            {
                jointFieldWorker:jointFieldWorker?jointFieldWorker:value.jointFieldWorker,  
                mileage:mileage?mileage:value.mileage,  
                bataType:bataType?bataType:value.bataType,  
            };

        return (
            <div>
                <CrudForm
                    title="Joint Field Worker"
                    inputs={{
                        jointFieldWorker:{
                            label:"Joint Field Worker",
                            type:"ajax_dropdown",
                            link:"team_member_with_itinerary",
                            where:{
                                fm:'{fm}',
                                date
                            },
                            otherValues:{
                                fm
                            }
                        },
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
                    structure={["jointFieldWorker",["mileage","bataType"]]}
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

JoinFieldWorkerForm.propTypes = {
    value:PropTypes.shape({
        value:PropTypes.number,
        label:PropTypes.string
    }),
    onChange:PropTypes.func,

    fm: PropTypes.shape({
        value:PropTypes.number,
        label:PropTypes.string
    })
}

export default  withStyles(styles) (JoinFieldWorkerForm);