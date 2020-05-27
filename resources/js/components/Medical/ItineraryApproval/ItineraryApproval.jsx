import React, { Component } from "react";
import { connect } from "react-redux";
import PropTypes from "prop-types";

import Typography from "@material-ui/core/Typography"
import Divider from "@material-ui/core/Divider"
import Toolbar from "@material-ui/core/Toolbar"
import withStyles from "@material-ui/core/styles/withStyles"
import Button from "@material-ui/core/Button"
import FormControlLabel from "@material-ui/core/FormControlLabel"
import Switch from "@material-ui/core/Switch"
import List from "@material-ui/core/List"
import ListItem from "@material-ui/core/ListItem"
import ListItemText from "@material-ui/core/ListItemText"
import EyeIcon from "@material-ui/icons/RemoveRedEye";
import Modal from "@material-ui/core/Modal";
import ListItemSecondaryAction from "@material-ui/core/ListItemSecondaryAction";
import IconButton from "@material-ui/core/IconButton";
import SearchIcon from "@material-ui/icons/Search";

import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import Layout from "../../App/Layout";
import ItineraryCalendar from "../ItineraryViewer/ItineraryCalendar";

import { changeDivision,changeType, fetchResults, approve, changeTeam, closeItinerary, fetchItinerary } from "../../../actions/Medical/ItineraryApproval";

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
    ...state.ItineraryApproval
});

const mapDispatchToProps = dispatch => ({
    onChangeTeam: team => dispatch(changeTeam(team)),
    onChangeDivision: division => dispatch(changeDivision(division)),
    onChangeType: type => dispatch(changeType(type)),
    onSearch: (division,team, type) => dispatch(fetchResults(division,team, type)),
    onApprove: id => dispatch(approve(id)),
    onOpenItinerary: id=>dispatch(fetchItinerary(id)),
    onCloseItinerary: ()=>dispatch(closeItinerary())
});

class ItineraryApproval extends Component {
    constructor(props) {
        super(props);

        this.handleCheck = this.handleCheck.bind(this);
        this.handleSearchButtonClick = this.handleSearchButtonClick.bind(this);
        this.handleOpenItinerary = this.handleOpenItinerary.bind(this);
    }

    handleCheck(e, value) {
        const { onChangeType } = this.props;
        
        onChangeType(value ? 1 : 0);
    }

    handleSearchButtonClick() {
        const { division,team, type, page, perPage, onSearch } = this.props;
        onSearch(division,team, type, page, perPage);
    }

    handleApproveButtonClick(id) {
        const { division,team, page, perPage, type, onSearch, onApprove } = this.props;
        
        return e => {
            onApprove(id);
            onSearch(division,team, type, page, perPage);
        }
    }

    handleOpenItinerary(id){
        const {onOpenItinerary} = this.props;
        return e=>{
            onOpenItinerary(id);
        }
    }

    renderList() {
        const { searched, results } = this.props;

        if (!searched) return (
            <Typography variant="caption" align="center" >Select MR and click search..</Typography>
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
                    <IconButton onClick={this.handleOpenItinerary(id)} >
                        <EyeIcon />
                    </IconButton>
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

    render() {

        const { classes, team,division, type, onChangeTeam,onChangeDivision,results,openedItinerary,onCloseItinerary,dates } = this.props;

        const filtered = results.filter(({id})=>id==openedItinerary);

        return (
            <Layout sidebar>
                <Typography align="center" variant="h5">Itinerary Approval</Typography>
                <Divider />
                <Toolbar >
                    <div className={classes.dropdownWrapper} >
                        <AjaxDropdown onChange={onChangeDivision} link="division" value={division} label="Division" />
                    </div>
                    <div className={classes.dropdownWrapper} >
                        <AjaxDropdown onChange={onChangeTeam} where={{ divi_id:'{divi_id}' }} otherValues={{ divi_id:division }} link="team" value={team} label="Team" />
                    </div>
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
                    <Button onClick={this.handleSearchButtonClick} className={classes.searchButton} variant="contained" color="primary">
                        <SearchIcon />
                        Search
                    </Button>
                </Toolbar>
                <Divider />
                <Modal className={classes.modal} onClose={onCloseItinerary} open={!!openedItinerary}>
                    <div>
                        <ItineraryCalendar yearMonth={(typeof filtered[0]=='undefined')?'2019-01':filtered[0].yearMonth.split(' ').join('')}  dates={dates.mapToObject('date')}/>
                    </div>
                </Modal>
                {this.renderList()}
            </Layout>
        );
    }
}

ItineraryApproval.propTypes = {
    classes: PropTypes.object,
    type: PropTypes.number,
    team: PropTypes.shape({
        value: PropTypes.number,
        label: PropTypes.string
    }),
    division: PropTypes.shape({
        value: PropTypes.number,
        label: PropTypes.string
    }),

    onChangeType: PropTypes.func,
    onChangeTeam: PropTypes.func,
    onChangeDivision: PropTypes.func,
    searched: PropTypes.bool
}

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(ItineraryApproval));