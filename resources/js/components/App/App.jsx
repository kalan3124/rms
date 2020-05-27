import React,{Component} from 'react';
import { Provider } from 'react-redux';
import theme from '../../theme';
import  MuiThemeProvider  from '@material-ui/core/styles/MuiThemeProvider';
import store from '../../store';
import ReactDOM from 'react-dom';
import Router from './Router';

export default class App extends Component{

    render(){
        return(
            <div>
                <Provider store={store}>
                    <MuiThemeProvider theme={theme}>
                        <Router/>
                    </MuiThemeProvider>
                </Provider>
            </div>
        )
    }
}

ReactDOM.render(
    <App />,
    document.getElementById('root')
);
