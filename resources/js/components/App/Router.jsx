import React, { Component, lazy } from 'react';
import { connect } from 'react-redux';
import  BrowserRouter  from "react-router-dom/BrowserRouter";
import { APP_DIRECTORY } from '../../constants/config';
import {
    guestAccess,
    loadUser
} from '../../actions/App';

import  Route  from 'react-router-dom/Route';
import  Switch  from 'react-router-dom/Switch';

// Pre loading components
import LoadingPage from './LoadingPage';
import LoginForm from '../LoginForm/LoginForm';
import DashBoard from '../DashBoard/DashBoard';
import CrudPage from '../CrudPage/CrudPage';
import Empty from './Empty';
import AsyncComponent from './AsyncComponent';

const Permission = ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "permission" */'../Permission/Permission'))}/>);
const IssueTracker = ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "issue-tracker" */"../IssueTracker/IssueTracker"))}/>);
const UserChangePassword = ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "change-password" */"../Password/UserChangePassword"))}/>);

//Lazy Loading components
// const medical ={
//     ItineraryPage: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-itinerary" */'../Medical/ItineraryPage/ItineraryPage'))} />),
//     StandardItineraryPage: ()=>(<AsyncComponent page RenderModule={lazy(()=>import(/* webpackChunkName: "medical-standard-itinerary" */'../Medical/StandardItinerary/StandardItinerary'))} />),
// }

const mapStateToProps = state => ({
    ...state,
    ...state.App
})

class Router extends Component {

    componentDidMount() {
        const { dispatch } = this.props;
        // Loading user on Router loaded
        if (localStorage.getItem('userToken')) {
            dispatch(loadUser(localStorage.getItem('userToken')));
        } else {
            dispatch(guestAccess());
        }
    }

    render() {
        const { user, userLoading } = this.props;

        return (
            <BrowserRouter basename={APP_DIRECTORY}>
                {userLoading ?
                    <LoadingPage/>
                    :
                    (user ?
                        <div>
                            <Switch>
                                <Route exact={true} path="/" component={DashBoard} />
                                <Route exact path="/:type/:menu/permission" component={Permission} />
                                <Route path="/:type/:menu/panel/:form/:mode?" component={CrudPage} />
                                <Route exact path="/:type/:menu/issues" component={IssueTracker}/>
                            </Switch>
                        </div>
                        :
                        <div>
                            <Route path='/' component={LoginForm} />
                        </div>
                    )
                }
            </BrowserRouter>
        )
    }
}

export default connect(mapStateToProps)(Router);
