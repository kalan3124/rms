import React, { Suspense, lazy, Component } from 'react';
import PropTypes from 'prop-types';
import LoadingPage from '../App/LoadingPage';

class AsyncComponent extends Component{

    constructor(props){
        super(props);

        const {RenderModule} = this.props;

        this.state = {RenderModule}
    }

    render(){
        const {RenderModule} = this.state;

        const {page} = this.props;

        if(!RenderModule) return null;

        return (
            <Suspense fallback={page?<LoadingPage/>:<div>Loading...</div>}>
                <RenderModule {...this.props}/>
            </Suspense>
        )
    }
}

AsyncComponent.propTypes = {
    page:PropTypes.bool
}

export default AsyncComponent;