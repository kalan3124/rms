import React, { Component } from 'react';
import PropTypes from 'prop-types';
import Avatar from '@material-ui/core/Avatar';
import Button from '@material-ui/core/Button';
import Layout from "../App/Layout";
import CssBaseline from '@material-ui/core/CssBaseline';
import FormControl from '@material-ui/core/FormControl';
import FormControlLabel from '@material-ui/core/FormControlLabel';
import FormHelperText from '@material-ui/core/FormHelperText';
import Checkbox from '@material-ui/core/Checkbox';
import Input from '@material-ui/core/Input';
import InputLabel from '@material-ui/core/InputLabel';
import Paper from '@material-ui/core/Paper';
import Divider from '@material-ui/core/Divider';
import Typography from '@material-ui/core/Typography';
import Grid from '@material-ui/core/Grid';
import withStyles from '@material-ui/core/styles/withStyles';
import { connect } from 'react-redux';
import withRouter from 'react-router-dom/withRouter';
import TextField from '@material-ui/core/TextField';
import { APP_URL, APP_NAME } from '../../constants/config';
import { fetchData, changePassword, updateData,fetchOtherData,changeLockTime,changeAttempts } from '../../actions/Medical/UserChangePassword';

const styles = theme => ({
    main: {
        width: 'auto',
        display: 'block', // Fix IE 11 issue.
        marginLeft: theme.spacing.unit * 3,
        marginRight: theme.spacing.unit * 3,
        [theme.breakpoints.up(400 + theme.spacing.unit * 3 * 2)]: {
            width: 500,
            height: 500,
            marginLeft: 'auto',
            marginRight: 'auto',
        },
    },
    paper: {
        marginTop: theme.spacing.unit * 8,
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        padding: `${theme.spacing.unit * 2}px ${theme.spacing.unit * 3}px ${theme.spacing.unit * 3}px`,
    },
    avatar: {
        margin: theme.spacing.unit,
        background: theme.palette.common.white,
        border: '2px solid ' + theme.palette.grey[100],
        width: theme.spacing.unit * 12,
        height: theme.spacing.unit * 12,
    },
    avatarImage: {
        width: '100%',
    },
    form: {
        width: '100%', // Fix IE 11 issue.
        marginTop: theme.spacing.unit,
    },
    submit: {
        marginTop: theme.spacing.unit * 3,
        background: theme.palette.primary.main
    },
});

export const mapStateToProps = state => ({
    ...state,
    ...state.UserChangePassword
});

export const mapDispatchToProps = dispatch => ({
    onChangePassword: password => dispatch(changePassword(password)),
    onChangeLockTime: lock_time => dispatch(changeLockTime(lock_time)),
    onChangeAttempts: attempts => dispatch(changeAttempts(attempts)),
    onLoadData: () => dispatch(fetchData()),
    onLoadOtherData: () => dispatch(fetchOtherData()),
    onUpdateData: (password,lock_time,attempts) => dispatch(updateData(password,lock_time,attempts)),
});

class UserChangePassword extends Component {

    constructor(props) {
        super(props);
        this.handleChangePassword = this.handleChangePassword.bind(this);
        this.handleChangeLockTime = this.handleChangeLockTime.bind(this);
        this.handleChangeLockAttempt = this.handleChangeLockAttempt.bind(this);
        this.onHandleUpdate = this.onHandleUpdate.bind(this);

        this.state = {
            msg: "",
            err: false
        };
    }

    componentDidMount() {
        const { onLoadData,onLoadOtherData } = this.props;
        onLoadData();
        onLoadOtherData();
    }

    backToHome() {
        window.location.href = APP_URL;
    }

    handleChangePassword(e) {
        const { onChangePassword } = this.props;
        onChangePassword(e.target.value);
        // console.log(e.target.value);
    }

    handleChangeLockTime(e){
        const { onChangeLockTime } = this.props;
        onChangeLockTime(e.target.value);
    }

    handleChangeLockAttempt(e){
        const { onChangeAttempts } = this.props;
        onChangeAttempts(e.target.value);
    }

    onHandleUpdate() {
        const { password,lock_time,attempts, onUpdateData } = this.props;
        // this.validate(password);
        onUpdateData(password,lock_time,attempts)

    }

    onHad(){
        const{password,lock_time,attempts, onUpdateData} = this.props;
        onUpdateData(password);
    }

    validate(e) {
        var pass = e;
        var regs = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

        var test = regs.test(pass);
        if (!test) {
            this.setState({
                msg: 'field should have Minimum 8 characters, at least one uppercase letter, one lowercase letter, one number and one special character',
                err: true
            })

        } else {
            this.setState({
                msg: "",
                err: false
            })
            this.onHad();
        }
    }

    render() {
        const {
            classes,
            name,
            code,
            roll,
            password,
            lock_time,
            attempts
        } = this.props;

        return (
            <Layout sidebar={true}>
                <main className={classes.main}>
                    <CssBaseline />
                    <Paper className={classes.paper}>
                        <Avatar className={classes.avatar}>
                            <img className={classes.avatarImage} src={APP_URL + 'images/user_logo.png'} />
                        </Avatar>
                        <Divider />
                        <Grid container>
                            <Grid item md={6}>
                                <TextField readOnly={true} margin="dense" variant="outlined" type={'text'} name="" value={name} label="User Name" />
                            </Grid>
                            <Grid item md={6}>
                                <TextField readOnly={true} margin="dense" variant="outlined" type={'text'} name="" value={code} label="User Code" />
                            </Grid>
                            <Grid item md={6}>
                                <TextField readOnly={true} margin="dense" variant="outlined" type={'text'} name="" value={roll} label="User Type" />
                            </Grid>
                            <Grid item md={6}>
                                <TextField margin="dense" error={this.state.err} helperText={this.state.msg} variant="outlined" type={'password'} name="" label="Password" value={password ? password : ''} onChange={this.handleChangePassword} />
                            </Grid>

                            {/* <Grid item md={6} hidden={roll == 1 ? false : true}>
                                <TextField margin="dense"  variant="outlined" type={'number'} name="" label="Lockout Time (Sec)" value={lock_time ? lock_time : ''} onChange={this.handleChangeLockTime} />
                            </Grid>
                            <Grid item md={6} hidden={roll == 1 ? false : true}>
                                <TextField margin="dense"   variant="outlined" type={'number'} name="" label="Duration (Days)" value={attempts ? attempts : ''} onChange={this.handleChangeLockAttempt} />
                            </Grid> */}

                            <Grid item md={5}>
                                <Button
                                    type="submit"
                                    fullWidth
                                    variant="contained"
                                    color="primary"
                                    onClick={this.onHandleUpdate}
                                >
                                    Update
                                        </Button>
                            </Grid>
                            <Grid item md={2}>

                            </Grid>
                            <Grid item md={5}>
                                <Button
                                    fullWidth
                                    variant="contained"
                                    color="secondary"
                                    onClick={this.backToHome}
                                >
                                    Cancel
                                        </Button>
                            </Grid>
                        </Grid>
                    </Paper>
                </main>
            </Layout>
        );
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(withRouter(UserChangePassword)));
