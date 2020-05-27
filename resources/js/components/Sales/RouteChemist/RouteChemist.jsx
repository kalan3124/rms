import React , {Component} from 'react';
import { connect } from 'react-redux';
import { Link } from "react-router-dom";
import Typography from '@material-ui/core/Typography';

import Layout from '../../App/Layout';
import Divider from "@material-ui/core/Divider";
import Grid from "@material-ui/core/Grid";
import withStyles from "@material-ui/core/styles/withStyles";
import Toolbar from "@material-ui/core/Toolbar";
import Button from "@material-ui/core/Button"; 
import SearchAndCheckPanel from './SearchAndCheckPanel';
import DirectionsIcon from '@material-ui/icons/Directions';
import AccountBalanceIcon from '@material-ui/icons/AccountBalance';
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import CloudUploadIcon from "@material-ui/icons/CloudUpload";
import { fetchAreas,addChemist, addRoute, removeChemist, removeRoute, fetchRoutes, fetchChemist, submit, fetchChemistsByRoute, changeType } from '../../../actions/Sales/RouteChemist';
import {withRouter} from 'react-router-dom';

const mapStateToProps = state =>({
    ...state.RouteChemist
});

const mapDispatchToProps = dispatch=>({
    onAddChemist:(chemist)=>dispatch(addChemist(chemist)),
    onAddRoute:route=>dispatch(addRoute(route)),
    onRemoveChemist:(chemist)=>dispatch(removeChemist(chemist)),
    onRemoveRoute:(route)=>dispatch(removeRoute(route)),
    onSearchRoute:(type,area,keyword)=>dispatch(fetchRoutes(type,area,keyword)),
    onSearchChemist:(type,area,keyword)=>dispatch(fetchChemist(type,area,keyword)),
    onSubmit:(type,routes,chemists)=>dispatch(submit(type,routes,chemists)),
    onLoadChemistsByRoute:(type,routeId)=>dispatch(fetchChemistsByRoute(type,routeId)),
    onChangeArea: (type,area)=>dispatch(fetchAreas(type,area)),
    onChangeType: type=>dispatch(changeType(type))
});

const styler = withStyles(theme=>({
    padding: {
        padding: theme.spacing.unit*2
    },
    dense:{
        flexGrow:1
    },userField: {
        padding: theme.spacing.unit*2
    },zIndex: {
        zIndex: 1200
    }
}))

class RouteChemist extends Component {

    constructor(props){
        super(props);
        this.handleCheckChemist = this.handleCheckChemist.bind(this);
        this.handleCheckRoute = this.handleCheckRoute.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleChangeArea = this.handleChangeArea.bind(this);
        this.handleSearchChemist = this.handleSearchChemist.bind(this);
        this.handleSearchRoute = this.handleSearchRoute.bind(this);
        this.props.onChangeType(this.props.match.params.mode);
        this.state = {
            count: 0
        }

    }

    componentDidUpdate(prevProps){
        if(this.props.match.params.mode!=prevProps.match.params.mode){
            this.props.onChangeType(this.props.match.params.mode);
        }
    }

    handleChangeArea(area){
        const {onChangeArea,type} = this.props;
        
        onChangeArea(type,area);
    }

    handleCheckChemist(chemist,checked){
        const {
            onAddChemist,
            onRemoveChemist
        } = this.props;

        if(!checked){
            onRemoveChemist(chemist);
        } else {
            onAddChemist(chemist);
        }
    }

    handleCheckRoute(route,checked){
        
        const {
            onAddRoute,
            onRemoveRoute,
            onLoadChemistsByRoute,
            type
        } = this.props;

        if(!checked){
            onRemoveRoute(route);
            this.setState({count: this.state.count - 1})
        } else {
            this.setState({count: this.state.count + 1})
            onAddRoute(route);
            onLoadChemistsByRoute(type,route.value);
        }
        
       
    }

    handleSubmit(){
        const {selectedChemists,selectedRoutes,onSubmit,type} = this.props;
        
            onSubmit(type,selectedRoutes,selectedChemists); 
    }

    handleSearchChemist(keyword){
        const {onSearchChemist, area,type} = this.props;

        onSearchChemist(type,area,keyword);
    }

    handleSearchRoute(keyword){
        const {onSearchRoute, area,type} = this.props;

        onSearchRoute(type,area,keyword);
    }

    render(){
        const {
            chemistKeyword,
            routeKeyword,
            chemists,
            routes,
            area,
            selectedChemists,
            selectedRoutes,
            classes
        } = this.props;

        return (
            <Layout sidebar >
                <Toolbar variant="dense" className={classes.zIndex}>
                    <Typography variant="h5" >Route Customer Allocation</Typography>
                    <div className={classes.dense} />
                    <div className={classes.userField}>
                    <AjaxDropdown
                        onChange={this.handleChangeArea}
                        label="Area"
                        link="area"
                        value={area}
                    />
                    </div>
                    <Button variant="contained" onClick={this.handleSubmit} color="secondary">Submit</Button>
                    <Button
                        variant="contained"
                        color="secondary"
                        className={classes.button}
                        component={Link}
                        to="/sales/other/upload_csv/route_customer"
                    >
                        <CloudUploadIcon />
                        Upload
                    </Button>
                </Toolbar>
                <Divider/>
                <Grid container>
                    <Grid className={classes.padding} item md={6}>
                        <SearchAndCheckPanel 
                            label="Route"
                            icon={<DirectionsIcon />}
                            keyword={routeKeyword}
                            results={routes}
                            checked={selectedRoutes}
                            onSearch={this.handleSearchRoute}
                            onCheck={this.handleCheckRoute}
                        />
                    </Grid>
                    <Grid className={classes.padding} item md={6}>
                        <SearchAndCheckPanel 
                            label="Chemist"
                            icon={<AccountBalanceIcon />}
                            keyword={chemistKeyword}
                            results={chemists}
                            checked={selectedChemists}
                            onSearch={this.handleSearchChemist}
                            onCheck={this.handleCheckChemist}
                        />
                    </Grid>
                </Grid>
            </Layout>
        )
    }
}

export default connect(mapStateToProps,mapDispatchToProps) ( styler ( withRouter (RouteChemist)));