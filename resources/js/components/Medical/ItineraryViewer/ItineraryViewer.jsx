import React, {Component} from "react";
import PropTypes from "prop-types";
import {connect} from "react-redux";
import moment from "moment";

import Typography from "@material-ui/core/Typography";
import Divider from "@material-ui/core/Divider";
import Toolbar from "@material-ui/core/Toolbar";
import withStyles from "@material-ui/core/styles/withStyles";
import Button from "@material-ui/core/Button";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import EyeIcon from "@material-ui/icons/RemoveRedEye";
import green from "@material-ui/core/colors/green";
import red from "@material-ui/core/colors/red";
import ListItemSecondaryAction from "@material-ui/core/ListItemSecondaryAction";
import IconButton from "@material-ui/core/IconButton";
import Modal from "@material-ui/core/Modal";

import { changeUser, changeMonth, fetchItineries, fetchItinerary, calendarClose } from "../../../actions/Medical/ItineraryViewer";
import { MEDICAL_REP_TYPE, MEDICAL_FIELD_MANAGER_TYPE,PRODUCT_SPECIALIST_TYPE } from "../../../constants/config";
import { PropNumOrString, PropDropdownOption } from "../../../constants/propTypes";
import Layout from "../../App/Layout";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import DatePicker from "../../CrudPage/Input/DatePicker";
import ItineraryCalendar from "./ItineraryCalendar";

const styles = theme=>({
    grow:{
        flexGrow:1
    },
    input:{
        maxWidth: '20vw',
        padding: theme.spacing.unit
    },
    green:{
        background:green[100]
    },
    red:{
        background:red[100]
    },
    modal:{
        top:'5vh',
        paddingLeft: theme.spacing.unit*20,
        width:1010
    }
});

const mapStateToProps = state=>({
    ...state.ItineraryViewer
});

const mapDispatchToProps = dispatch=>({
    onChangeUser:user=>dispatch(changeUser(user)),
    onChangeMonth:month=>dispatch(changeMonth(month)),
    onSearch:(user,month)=>dispatch(fetchItineries(user,month)),
    onLoadItinerary: id=>dispatch(fetchItinerary(id)),
    onCloseCalendar: ()=>dispatch(calendarClose())
});

class ItineraryViewer extends Component{

    constructor(props){
        super(props);

        this.handleSearch = this.handleSearch.bind(this);
    }

    handleSearch(){
        const {user,month,onSearch} = this.props;

        onSearch(user,month);
    }

    handleOpenItinerary(id){
        const {onLoadItinerary} = this.props;
        return e=>{
            onLoadItinerary(id)
        }
    }

    render(){
        const {user,onChangeUser,classes,month,onChangeMonth,dates,calendarOpen,onCloseCalendar} = this.props;

        let yearMonth = "";

        if(month){
            yearMonth = moment(month).format("YYYY-MM");
        } else {
            yearMonth = moment().format("YYYY-MM");
        }

        return (
            <Layout sidebar>
                <Typography variant="h5" align="center">Itinerary Viewer</Typography>
                <Divider/>
                <Toolbar>
                    <AjaxDropdown 
                        link="user" 
                        label="User" 
                        where={{u_tp_id:MEDICAL_REP_TYPE+"|"+MEDICAL_FIELD_MANAGER_TYPE+'|'+PRODUCT_SPECIALIST_TYPE}} 
                        value={user}
                        onChange={onChangeUser} 
                        classes={{root:classes.input}}
                    />
                    <DatePicker
                        label="Month"
                        onChange={onChangeMonth}
                        value={month}
                    />
                    <div className={classes.grow}/>
                    <Button onClick={this.handleSearch} variant="contained" color="primary" >
                        Search
                    </Button>
                </Toolbar>
                <Divider/>
                <List dense >
                    {this.renderList()}
                </List>
                <Modal className={classes.modal} onClose={onCloseCalendar} open={calendarOpen}>
                    <ItineraryCalendar yearMonth={yearMonth}  dates={dates}/>
                </Modal>
            </Layout>
        )
    }

    renderList(){
        const {itineraries,classes} = this.props;
        
        return itineraries.map(({id,year,month,user,approver,approvedTime,createdTime},key)=>{
            return(
                <ListItem 
                    key={key}
                    dense
                    divider
                    className={approver?classes.green:classes.red}
                >
                    <ListItemText 
                        primary={
                            <span>{user?user.label:"DELETED USER"}  [ {year}-{month.toString().padStart(2,"0")} ]</span>
                        }
                        secondary={
                            <span>Created Time:- {createdTime} {approver?"| Approved By:- "+approver.label+" |":""}{approver?"Approved Time:-"+approvedTime:""}</span>
                        }
                    />
                    <ListItemSecondaryAction>
                        <IconButton onClick={this.handleOpenItinerary(id)} >
                            <EyeIcon/>
                        </IconButton>
                    </ListItemSecondaryAction>
                </ListItem>
            )
        });
    }
}

ItineraryViewer.propTypes = {
    user:PropTypes.shape({
        value:PropTypes.number,
        label: PropTypes.string
    }),
    onChangeUser: PropTypes.func,

    month: PropTypes.oneOfType([
        PropTypes.string,
        PropTypes.object
    ]),
    onChangeMonth: PropTypes.func,

    onSearch: PropTypes.func,

    classes: PropTypes.shape({
        grow: PropTypes.string,
        input: PropTypes.string
    }),

    itineraries: PropTypes.arrayOf(PropTypes.shape({
        year: PropNumOrString,
        month: PropNumOrString,
        user:PropDropdownOption,
        approver: PropDropdownOption,
        approvedTime: PropTypes.string,
        createdTime: PropTypes.string
    })),

    onLoadItinerary: PropTypes.func,
    dates:PropTypes.object
}

export default connect(mapStateToProps,mapDispatchToProps)( withStyles(styles) (ItineraryViewer));