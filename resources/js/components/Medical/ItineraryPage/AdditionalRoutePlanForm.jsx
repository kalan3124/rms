import React, { Component } from "react";
import PropTypes from "prop-types";
import CrudForm from "../../CrudPage/CrudForm";

class AdditionalRoutePlanForm extends Component {
    render() {

        const { values, onChange, onSubmit, user, onClear,userType } = this.props;
        
        return (
            <CrudForm
                title="Additional Route Plan"
                inputs={{
                    mileage: {
                        label: "Mileage",
                        type: "text"
                    },
                    bata: {
                        label: "Bata Type",
                        type: "ajax_dropdown",
                        link: "bata_type",
                        where:{
                            bt_type:userType,
                            user
                        }
                    },
                    areas: {
                        label: "Sub Town",
                        type: 'multiple_ajax_dropdown',
                        link: "sub_town",
                        multiple: true,
                        where:{
                            'u_id':'{u_id}'
                        },
                        otherValues:{
                            'u_id':user
                        }
                    },
                    description: {
                        label: "Description",
                        type: "text"
                    }
                }}
                structure={["description", "areas", ["bata", "mileage"]]}
                onInputChange={onChange}
                values={values}
                onSubmit={onSubmit}
                onClear={onClear}
                mode="create"
                disableSearch
            />
        );
    }
}

AdditionalRoutePlanForm.propTypes = {
    onChange: PropTypes.func,
    values: PropTypes.shape({
        mileage: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
        bata: PropTypes.shape({
            value: PropTypes.number,
            label: PropTypes.string
        }),
        areas: PropTypes.arrayOf(PropTypes.shape({
            value: PropTypes.number,
            label: PropTypes.string
        })),
        description: PropTypes.string
    }),
    user: PropTypes.shape({
        label: PropTypes.string,
        value: PropTypes.number
    }),
    onSubmit: PropTypes.func,
    onClear: PropTypes.func
}

export default AdditionalRoutePlanForm;