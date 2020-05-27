import React, { Component } from "react";

class Location extends Component {

    render() {
        const { value } = this.props;

        if(!value)
            return null;

        return (
            <a href={`https://maps.google.com/?q=${value.lat},${value.lng}`} target="__blank">
                {value.lat},{value.lng}
            </a>
        )
    }
}

export default Location