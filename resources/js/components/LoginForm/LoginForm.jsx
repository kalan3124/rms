import React, { Component } from 'react';
import PropTypes from 'prop-types';
import Avatar from '@material-ui/core/Avatar';
import Button from '@material-ui/core/Button';
import CssBaseline from '@material-ui/core/CssBaseline';
import FormControl from '@material-ui/core/FormControl';
import FormControlLabel from '@material-ui/core/FormControlLabel';
import FormHelperText from '@material-ui/core/FormHelperText';
import Checkbox from '@material-ui/core/Checkbox';
import Input from '@material-ui/core/Input';
import InputLabel from '@material-ui/core/InputLabel';
import Paper from '@material-ui/core/Paper';
import Typography from '@material-ui/core/Typography';
import CircularProgress from '@material-ui/core/CircularProgress';
import DoneOutlineIcon from '@material-ui/icons/DoneOutline';
import WarningIcon from '@material-ui/icons/Warning';
import withStyles from '@material-ui/core/styles/withStyles';
import { APP_URL, APP_NAME } from '../../constants/config';
import {
  usernameChange,
  passwordChange,
  rememberChange,
  attemptLogin,
  newPasswordChange
} from '../../actions/LoginForm';
import { connect } from 'react-redux';
import withRouter from 'react-router-dom/withRouter'

const styles = theme => ({
  main: {
    width: 'auto',
    display: 'block', // Fix IE 11 issue.
    marginLeft: theme.spacing.unit * 3,
    marginRight: theme.spacing.unit * 3,
    [theme.breakpoints.up(400 + theme.spacing.unit * 3 * 2)]: {
      width: 400,
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
    background: '#16ccab'
  },
})

const mapStateToProps = state => ({
  ...state,
  ...state.LoginForm
})
class LoginForm extends Component {

  constructor(props) {
    super(props);

    this.state = {
      msg: "",
      err: false,
      validate: false
    };
  }

  componentWillReceiveProps(nextProps) {
    if (this.props.success || nextProps.success) {
      this.props.history.go(APP_URL);
    }
    document.getElementById('stage').setAttribute('style', 'display:none');

  }

  handleUsernameChange(e) {
    const { dispatch } = this.props;
    e.preventDefault();

    dispatch(usernameChange(e.target.value))
  }

  handlePasswordChange(e) {
    const { dispatch } = this.props;
    e.preventDefault();

    dispatch(passwordChange(e.target.value));
  }

  handleNewPasswordChange(e) {
    const { dispatch, newPassword } = this.props;
    e.preventDefault();
    dispatch(newPasswordChange(e.target.value));
    this.validate(newPassword);
  }

  handleRememberChange(e) {
    const { dispatch } = this.props;
    e.preventDefault();

    dispatch(rememberChange(e.target.checked));
  }

  handleSubmit(e) {
    const { dispatch, username, password, remember, newPassword } = this.props;
    const { validate } = this.state;
    e.preventDefault();

    if (!validate)
      dispatch(attemptLogin(username, password, remember, newPassword));
  }

  validate(e) {
    var pass = e;
    var regs = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{7,}$/;

    var test = regs.test(pass);
    if (!test) {
      this.setState({
        msg: 'field should have Minimum 8 characters, at least one uppercase letter, one lowercase letter, one number and one special character',
        err: true,
        validate: true
      })

    } else {
      this.setState({
        msg: "",
        err: false,
        validate: false
      })
    }
  }

  render() {
    const {
      classes,
      username,
      password,
      remember,
      loading,
      message,
      error,
      success,
      passwordError,
      usernameError,
      status,
      newPassword
    } = this.props;

    return (
      <main className={classes.main}>
        <CssBaseline />
        <Paper className={classes.paper}>
          <Avatar className={classes.avatar}>
            {loading ?
              <CircularProgress />
              :
              <img className={classes.avatarImage} src={APP_URL + 'images/fatboys.jpg'} />
            }
          </Avatar>
          <Typography component="h1" variant="h5">
            {APP_NAME}
          </Typography>
          <form onSubmit={(this.handleSubmit).bind(this)} className={classes.form}>
            {success || error ? <FormHelperText error={success ? false : error}>{message}</FormHelperText> : null}
            <FormControl error={!!usernameError} margin="normal" required fullWidth>
              <InputLabel htmlFor="username">UserName</InputLabel>
              <Input onChange={(this.handleUsernameChange).bind(this)} value={username} id="username" name="username" autoComplete="username" autoFocus />
              {usernameError ? <FormHelperText error={true}>{usernameError}</FormHelperText> : null}
            </FormControl>
            <FormControl error={!!passwordError} margin="normal" required fullWidth>
              <InputLabel htmlFor="password">{status ? 'Old Password' : 'Password'}</InputLabel>
              <Input readOnly={status ? true : false} name="password" type="password" id="password" onChange={(this.handlePasswordChange).bind(this)} value={password} autoComplete="current-password" />
              {passwordError ? <FormHelperText error={true}>{passwordError}</FormHelperText> : null}
            </FormControl>
            {
              status ?
                <FormControl error={!!passwordError} margin="normal" required fullWidth>
                  <InputLabel htmlFor="password">New Password</InputLabel>
                  <Input name="newpassword" type="password" id="password" onChange={(this.handleNewPasswordChange).bind(this)} value={newPassword} autoComplete="current-password" />
                  {passwordError ? <FormHelperText error={true}>{passwordError}</FormHelperText> : null}
                  {this.state.msg ? <FormHelperText error={this.state.err}>{this.state.msg}</FormHelperText> : null}
                </FormControl> : null
            }
            <FormControlLabel
              control={<Checkbox value="remember" onChange={(this.handleRememberChange).bind(this)} checked={remember} color="primary" />}
              label="Remember me"
            />
            <Button
              type="submit"
              fullWidth
              variant="contained"
              color="primary"
              className={classes.submit}
            >
              {status ? 'Update Password' : 'Sign in'}
            </Button>
          </form>
        </Paper>
      </main>
    );
  }
}


LoginForm.propTypes = {
  classes: PropTypes.object.isRequired,
};

export default withStyles(styles)(connect(mapStateToProps)(withRouter(LoginForm)));
