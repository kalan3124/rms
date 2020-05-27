import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from "prop-types";
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
import SupervisorAccountIcon from "@material-ui/icons/SupervisorAccount";
import AjaxDropdown from "../../CrudPage/Input/AjaxDropdown";
import { withRouter } from 'react-router-dom';
import SaveIcon from "@material-ui/icons/Save";
import CloseIcon from "@material-ui/icons/Close";

import { changeDCUser, changeDCChemist , fetchUser, fetchChemist, changeCheckedUser, changeCheckedChemist, load ,save} from "../../../actions/Sales/DCCustomerAllocation";

const mapStateToProps = state => ({
     ...state.DCCustomerAllocation
});

const mapDispatchToProps = dispatch => ({
    onChangeDCUser: dcUser => dispatch(changeDCUser(dcUser)),
    onChangeDCChemist: dcChemist => dispatch(changeDCChemist(dcChemist)),
    onSearchUser: (keyword, delay) => dispatch(fetchUser(keyword, delay)),
    onSearchChemist: (keyword, delay) => dispatch(fetchChemist(keyword, delay)),
    onChangeCheckedUser: userChecked => dispatch(changeCheckedUser(userChecked)),
    onChangeCheckedChemist: chemistChecked => dispatch(changeCheckedChemist(chemistChecked)),
    onSave: (user, chemist) => dispatch(save(user, chemist)),
    onLoad: (user) => dispatch(load(user)),
    onClearPage: () => dispatch(clearPage())
});

const styler = withStyles(theme => ({
     padding: {
          padding: theme.spacing.unit * 2
     },
     dense: {
          flexGrow: 1
     }, userField: {
          padding: theme.spacing.unit * 2
     }, zIndex: {
          zIndex: 1200
     }
}))

class DCCustomerAllocation extends Component {

    constructor(props) {
        super(props);
        this.state = {
            error: ''
        }

        props.onSearchUser("", false);
        props.onSearchChemist("", false);
        this.handleDCUserChange = this.handleDCUserChange.bind(this);
        this.handleDCChemistChange = this.handleDCChemistChange.bind(this);
        this.handleUserChecked = this.handleUserChecked.bind(this);
        this.handleChemistChecked = this.handleChemistChecked.bind(this);
        this.handleSaveButtonClick = this.handleSaveButtonClick.bind(this);
    }

    handleDCUserChange(value) {
        const { onChangeDCUser, onSearchUser } = this.props;

        onChangeDCUser(value);
        onSearchUser(value)
    }

    handleDCChemistChange(value) {
        const { onChangeDCChemist, onSearchChemist } = this.props;

        onChangeDCChemist(value);
        onSearchChemist(value)
    }

    handleUserChecked(user, checked) {
        const { userChecked, onChangeCheckedUser,onLoad} = this.props;

        // console.log(checked);

        let modedUserChecked = userChecked.filter(({ value }) => value != user.value);

        if (checked) {
            onLoad(user);
            modedUserChecked = [user];
        }

        onChangeCheckedUser(modedUserChecked);
    }

    handleChemistChecked(chemist, checked) {
        const { chemistChecked, onChangeCheckedChemist  } = this.props;

        let modedChemistChecked = chemistChecked.filter(({ value }) => value != chemist.value);

        if (checked) {
            modedChemistChecked = [...modedChemistChecked,chemist];
            console.log(modedChemistChecked);

        }


        onChangeCheckedChemist(modedChemistChecked);
    }

     handleSaveButtonClick() {
          const { chemistChecked, userChecked, onSave } = this.props;
        console.log(chemistChecked);

          onSave(userChecked, chemistChecked);
     }

    render() {
        const {
            classes,
            dcUser,
            dcChemist,
            userResults,
            chemistResults,
            userChecked,
            chemistChecked,
            onClearPage
        } = this.props;

        return (
            <Layout sidebar >
                <Toolbar variant="dense" className={classes.zIndex}>
                        <Typography variant="h5" >DC Allocation</Typography>
                        <div className={classes.dense} />
                        <Button onClick={this.handleSaveButtonClick} variant="contained" color="secondary">
                            <SaveIcon />
                            Submit
                        </Button>
                        <Grid item md="1"></Grid>
                        <Button onClick={onClearPage} margin="dense" className={classes.button} variant="contained" color="secondary">
                            <CloseIcon />
                            Cancel
                        </Button>
                </Toolbar>
                <Divider />
                <Grid container>
                        <Grid item md={5}>
                            <SearchAndCheckPanel
                                icon={
                                    <SupervisorAccountIcon />
                                }
                                label="USER"
                                keyword={dcUser}
                                onSearch={this.handleDCUserChange}
                                results={userResults}
                                onCheck={this.handleUserChecked}
                                checked={userChecked}
                            />
                        </Grid>
                    <Grid item md={7}>
                        <SearchAndCheckPanel
                            icon={
                                <SupervisorAccountIcon />
                            }
                            label="CHEMIST"
                            keyword={dcChemist}
                            onSearch={this.handleDCChemistChange}
                            results={chemistResults}
                            onCheck={this.handleChemistChecked}
                            checked={chemistChecked}
                        />
                    </Grid>
                </Grid>
            </Layout>
        )
    }
}

const alloPropType = PropTypes.arrayOf(PropTypes.shape({
     value: PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
     label: PropTypes.string
}));

DCCustomerAllocation.propTypes = {
    //  classes: PropTypes.shape({
    //  }),

    onChangeDCUser: PropTypes.func,
    dcUser: PropTypes.string,

    onChangeDCChemist: PropTypes.func,
    dcChemist: PropTypes.string,

    onSearchUser: PropTypes.func,
    onSearchChemist: PropTypes.func,

     userResults: alloPropType,
     Results: alloPropType,
    //  siteChecked: alloPropType,
    //  dsrChecked: alloPropType
}

export default connect(mapStateToProps, mapDispatchToProps)(styler(withRouter(DCCustomerAllocation)));
