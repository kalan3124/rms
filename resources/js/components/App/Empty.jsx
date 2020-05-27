import React from 'react';
import Layout from './Layout';
import withStyles from '@material-ui/core/styles/withStyles';
import { APP_URL } from '../../constants/config';
import Typography from '@material-ui/core/Typography';
import Divider from '@material-ui/core/Divider';
import Grid from '@material-ui/core/Grid';
import Button from '@material-ui/core/Button';
import HomeIcon from '@material-ui/icons/Home';
import Link from 'react-router-dom/Link';

const styles = theme => ({
  penguin: {
    position: 'fixed',
    bottom: 0,
    left: 0
  },
  ultraHighFont: {
    fontWeight: 700
  },
  paragraph: {
    fontSize: '1.2em'
  },
  rightButton: {
    float: 'right',
    margin: theme.spacing.unit * 2
  }
})

export default withStyles(styles)(({ classes }) => (
  <Layout>
    <img className={classes.penguin} src={APP_URL + "images/soldier-penguin.png"} />
    <Typography color="secondary" className={classes.ultraHighFont} align="center" variant="h1">404</Typography>
    <Typography color="primary" className={classes.ultraHighFont} align="center" variant="h6">Not Found</Typography>
    <Divider />
    <Grid justify="flex-end" container>
      <Grid md={4} item>
        <Typography className={classes.paragraph} color="textPrimary" align="left" variant="caption">You are going on a wrong way. Your system is not in here. Please click below button to go home.</Typography>
        <Divider />
        <Button className={classes.rightButton} variant="contained" color="primary"><Link as="span" to="/"> <HomeIcon /> Home</Link></Button>
      </Grid>
    </Grid>
  </Layout>
))
