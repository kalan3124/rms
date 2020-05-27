import React , {Component} from 'react';
import { connect } from 'react-redux';
import PropTypes from "prop-types";
import Typography from '@material-ui/core/Typography';
import Layout from '../../App/Layout';
import Divider from "@material-ui/core/Divider";
import withStyles from "@material-ui/core/styles/withStyles"
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import Toolbar from "@material-ui/core/Toolbar"
import IconButton from "@material-ui/core/IconButton";
import SearchIcon from "@material-ui/icons/Search";
import FormControlLabel from "@material-ui/core/FormControlLabel"
import Switch from "@material-ui/core/Switch"
import Button from "@material-ui/core/Button"
import Modal from "@material-ui/core/Modal";
import List from "@material-ui/core/List"
import ListItem from "@material-ui/core/ListItem"
import ListItemText from "@material-ui/core/ListItemText"
import ListItemSecondaryAction from "@material-ui/core/ListItemSecondaryAction";
import { DISTRIBUTOR_SALES_REP_TYPE} from '../../../constants/config';
import { changeArea,changeType, fetchResults, approve, changeUser, closeItinerary, fetchItinerary, changeMode } from "../../../actions/Sales/SalesItineraryApproval";
import withRouter from 'react-router/withRouter';

const styles = theme => ({
     dropdownWrapper: {
         width: "50%",
         padding: theme.spacing.unit
     },
     grow: {
         flexGrow: 1
     },
     searchButton: {
         marginLeft: theme.spacing.unit
     },
     modal:{
         top:theme.spacing.unit*4,
         paddingLeft: theme.spacing.unit*20,
         width:1010
     }
});

const mapStateToProps = state => ({
     ...state.SalesItineraryApproval
});

const mapDispatchToProps = dispatch => ({
     onChangeUser: user => dispatch(changeUser(user)),
     onChangeArea: area => dispatch(changeArea(area)),
     onChangeType: type => dispatch(changeType(type)),
     onSearch: (user, type,area,mode) => dispatch(fetchResults(user,type,area,mode)),
     onApprove: id => dispatch(approve(id)),
     onOpenItinerary: id=>dispatch(fetchItinerary(id)),
     onCloseItinerary: ()=>dispatch(closeItinerary()),
     onChangeMode:(mode)=>dispatch(changeMode(mode))
});

class  SalesItineraryApproval extends Component{

     constructor(props) {
          super(props);

          this.handleCheck = this.handleCheck.bind(this);
          this.handleSearchButtonClick = this.handleSearchButtonClick.bind(this);
          this.props.onChangeMode(this.props.match.params.mode);
    }

    componentDidUpdate(prevProps){
        const {match} = this.props;

        if(match.params.mode != prevProps.match.params.mode){
            this.props.onChangeMode(match.params.mode);
        }
    }

    handleCheck(e, value) {
          const { onChangeType } = this.props;

          onChangeType(value ? 1 : 0);
     }

     handleSearchButtonClick() {
          const { user,area,type, page, perPage, onSearch,mode } = this.props;

          onSearch(user,type,area,mode);
     }

     handleApproveButtonClick(id) {
          const {user, page,area, perPage, type, onSearch, onApprove ,mode} = this.props;

          return e => {
              onApprove(id);
              onSearch(user, type,area,mode);
          }
     }

     renderList() {
          const { searched, results } = this.props;

          if (!searched) return (
              <Typography variant="caption" align="center" >Select User and click search..</Typography>
          )

          if (!results.length) return (
              <Typography variant="caption" align="center" >Sorry no results found.. :-(</Typography>
          );

          return (
              <List dense>
                  {this.renderListItems()}
              </List>
          )
     }

     emptyList(){
          return (
              <ListItem dense>
              </ListItem>
          )
     }

     renderListItems() {
          const { results } = this.props;
          return results.map(({ id, approvedBy, approvedTime, yearMonth, type, createdTime, user }) => (
              <ListItem divider key={id} dense>
                  <ListItemText primary={user + ' [' + yearMonth + ']'} secondary={"Created at " + createdTime + (type ? " | Approved By " + approvedBy + " at " + approvedTime : "")} />
                  <ListItemSecondaryAction>
                      {this.renderApproveButton(id, type)}
                  </ListItemSecondaryAction>
              </ListItem>
          ))
     }

     renderApproveButton(id, type) {
          if (type) return null;
  
          return (
              <Button onClick={this.handleApproveButtonClick(id)} variant="contained" color="secondary">Approve</Button>
          )
     }

     render(){
          const {classes,user, type, onChangeUser,onChangeArea,area,results,openedItinerary,onCloseItinerary,dates, mode} = this.props;
          return(
               <Layout sidebar>
                    <Typography align="center" variant="h5">Itinerary Approval</Typography>
                    <Toolbar>
                    {mode=="sales"?
                        <div className={classes.dropdownWrapper} >
                            <AjaxDropdown onChange={onChangeArea} value={area} link="area" label="Area" />
                        </div>
                    :
                        <div className={classes.dropdownWrapper}>
                            <AjaxDropdown  where={{u_tp_id:DISTRIBUTOR_SALES_REP_TYPE}} onChange={onChangeUser} link="user" value={user} label="User" />
                        </div>
                    }
                    <Divider/>
                    <div className={classes.grow} />
                    <FormControlLabel
                        control={
                            <Switch
                                checked={type == 1}
                                onChange={this.handleCheck}
                            />
                        }
                        labelPlacement="start"
                        label="Approved"
                    />
                    <Button className={classes.searchButton} onClick={this.handleSearchButtonClick} variant="contained" color="primary">
                        <SearchIcon />
                        Search
                    </Button>
                    </Toolbar>
                    <Divider/>
                    {this.renderList()}
               </Layout>
          );
     }
}

SalesItineraryApproval.propTypes = {
     classes: PropTypes.object,
     type: PropTypes.number,
     user: PropTypes.shape({
          value: PropTypes.number,
          label: PropTypes.string
     }),
    area: PropTypes.shape({
        value: PropTypes.number,
        label: PropTypes.string
    }),
     onChangeType: PropTypes.func,
     onChangeUser: PropTypes.func,
     onChangeArea: PropTypes.func,
     searched: PropTypes.bool
}
export default connect(mapStateToProps,mapDispatchToProps)(withStyles(styles)( withRouter (SalesItineraryApproval)));