import React, { Component } from "react";
import LocationPicker from 'react-location-picker';

import withStyles from "@material-ui/core/styles/withStyles";


const defaultPosition = {lat: 6.9270786, lng: 79.86124300000006};
  

class Location extends Component {

    constructor(props){
        super(props);

        this.handleChange = this.handleChange.bind(this);
    }

    handleChange(place){
        this.props.onChange(place.position);
    }

    render(){

        const {value} = this.props;
        console.log(value);
        return (
            <LocationPicker
                containerElement={ <div style={ {height: '100%'} } /> }
                mapElement={ <div style={ {height: '300px'} } /> }
                defaultPosition={value?value:defaultPosition}
                onChange={this.handleChange}
            />
        )
    }
}

export default Location;
