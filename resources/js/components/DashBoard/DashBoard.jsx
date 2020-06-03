import React, { Component, Fragment } from 'react';
import PropTypes from 'prop-types';
import Layout from '../App/Layout';
import Link from 'react-router-dom/Link';
import { dashboardLoad } from '../../actions/Dashboard';
import { connect } from 'react-redux';
import classNames from 'classnames';

import blue from '@material-ui/core/colors/blue';
import green from '@material-ui/core/colors/green';
import purple from '@material-ui/core/colors/purple';
import orange from '@material-ui/core/colors/orange';
import brown from '@material-ui/core/colors/brown';
import pink from '@material-ui/core/colors/pink';
import yellow from '@material-ui/core/colors/yellow';

import withStyles from '@material-ui/core/styles/withStyles';
import Grid from '@material-ui/core/Grid';
import Typography from '@material-ui/core/Typography';
import Divider from '@material-ui/core/Divider';
import Card from '@material-ui/core/Card';
import CardActionArea from '@material-ui/core/CardActionArea';
import CardHeader from '@material-ui/core/CardHeader';
import Avatar from '@material-ui/core/Avatar';
import Drawer from '@material-ui/core/Drawer';
import ListItem from '@material-ui/core/ListItem';
import List from '@material-ui/core/List';
import ListItemText from '@material-ui/core/ListItemText';

import ExpandLess from '@material-ui/icons/ExpandLess';
import ExpandMore from '@material-ui/icons/ExpandMore';

import Language from '@material-ui/icons/Language';
import Person from '@material-ui/icons/Person';
import LocalHospital from '@material-ui/icons/LocalHospital';
import AccountBalance from '@material-ui/icons/AccountBalance';
import Group from '@material-ui/icons/Group';
import Store from '@material-ui/icons/Store';
import Grocery from '@material-ui/icons/LocalGroceryStore';
import AddLocation from '@material-ui/icons/AddLocation';
import ListIcon from '@material-ui/icons/List';
import AccountCircle from '@material-ui/icons/AccountCircle';
import PlaylistAddCheck from '@material-ui/icons/PlaylistAddCheck';
import SpeakerNotesOff from '@material-ui/icons/SpeakerNotesOff';
import Done from '@material-ui/icons/Done';
import Navigation from '@material-ui/icons/Navigation';
import EventAvailable from '@material-ui/icons/EventAvailable';
import VpnKey from '@material-ui/icons/VpnKey';
import CardGiftcard from '@material-ui/icons/CardGiftcard';
import PersonPin from '@material-ui/icons/PersonPin';
import CloudUpload from "@material-ui/icons/CloudUpload";
import BugReport from "@material-ui/icons/BugReport";
import ClassIcon from "@material-ui/icons/Class";
import DateRangeIcon from "@material-ui/icons/DateRange";
import LocalTaxiIcon from "@material-ui/icons/LocalTaxi";
import AttachMoneyIcon from "@material-ui/icons/AttachMoney";
import ShopTwoIcon from "@material-ui/icons/ShopTwo";
import AutorenewIcon from "@material-ui/icons/Autorenew";
import CalendarTodayIcon from '@material-ui/icons/CalendarToday';
import DirectionsWalkIcon from '@material-ui/icons/DirectionsWalk';
import DirectionsIcon from "@material-ui/icons/Directions";
import TableChartIcon from "@material-ui/icons/TableChart";
import EventBusyIcon from "@material-ui/icons/EventBusy";
import EventNote from "@material-ui/icons/EventNote";
import CompareIcon from "@material-ui/icons/CompareArrows";
import HistoryIcon from "@material-ui/icons/History";
import GPSStatusIcon from "@material-ui/icons/GpsOff";
import AddAPhotoIcon from '@material-ui/icons/AddAPhoto';
import AccountBalanceIcon from "@material-ui/icons/AccountBalance";
import LoginIcon from '@material-ui/icons/ExitToApp';
import ReceiptIcon from '@material-ui/icons/Receipt';
import AddCircleOutlineIcon from '@material-ui/icons/AddCircleOutline';
import ControlCameraIcon from '@material-ui/icons/ControlCamera';
import CallReceivedIcon from '@material-ui/icons/CallReceived';
import StoreIcon from '@material-ui/icons/Store';
import CardGiftCardIcon from "@material-ui/icons/CardGiftcard"

import Collapse from '@material-ui/core/Collapse';

const styles = theme => ({
    paper: {
        padding: Math.round(theme.spacing.unit / 2),
        marginTop: theme.spacing.unit * 2,
    },
    margin: {
        margin: theme.spacing.unit
    },
    icon: {
        textShadow: '2px 2px 4px #fff',
        fontSize: '2em',
        textAlign: 'center'
    },
    iconContainer: {
        background: '#fff',
        borderRadius: '25%'
    },
    cardHeader: {
        padding: theme.spacing.unit
    },
    fontWhite: {
        background: theme.palette.common.white
    },

    blue: {
        color: blue[800]
    },
    green: {
        color: green[800]
    },
    purple: {
        color: purple[800]
    },
    orange: {
        color: orange[800]
    },
    brown: {
        color: brown[800]
    },
    pink: {
        color: pink[800]
    },
    yellow: {
        color: yellow[800]
    },

    fontWhite: {
        color: theme.palette.common.white
    },
    background: {
        height: '100vh',
        padding: 60,
        paddingLeft: theme.spacing.unit * 20 + 60,
        paddingTop: 0,
        [theme.breakpoints.down('sm')]: {
            paddingLeft: 0,
        },
    },
    center: {
        textAlign: 'center',
        padding: 0,
        margin: theme.spacing.unit,
        borderRadius: theme.spacing.unit / 2,
        color: '#fff',
        borderTop: '1px solid #F2F2F2',
        boxShadow: '0 1px 1px 0 rgba(0, 0, 0, 0.17)'
    },
    title: {
        fontSize: '0.85em',
        color: '#282F33',
        fontWeight: 600,
        fontSmoothing: 'antialiased'
    },
    drawer: {
        padding: 0,
        flexShrink: 1,
        width: theme.spacing.unit * 20,
        height: 'calc( 100vh - 56px)',
        backgroundColor: '#000',
        paddingTop: 56,
        [`${theme.breakpoints.up('xs')} and (orientation: landscape)`]: {
            height: 'calc( 100vh - 48px)',
            paddingTop: 48,
        },
        [theme.breakpoints.up('sm')]: {
            height: 'calc( 100vh - 42px)',
            paddingTop: 42,
        },
        [theme.breakpoints.down('sm')]: {
            display:'none'
        },
    },
    cardHeader: {
        padding: theme.spacing.unit
    },
    mainListItem: {
        padding: 0,
        background: '#000',
        paddingLeft: theme.spacing.unit * 1
    },
    listItem: {
        background: '#16ccab',
        padding: theme.spacing.unit,
        paddingLeft: theme.spacing.unit * 2,
        borderTop: 'solid 2px #000'
    },
    smallSizeFonts: {
        fontSize: '.75em'
    },
    mainItem:{
        background: '#e0e0e0',
        '&:hover':{
            background: '#f0f0f0'
        }
    },
    padding:{
        padding: theme.spacing.unit*2,
        float: "right"
    }
})

const DashboardCard = withStyles(styles)(({ classes, title, Icon, color, link }) => (
    <Grid item md={4}>
        <Link style={{ textDecoration: 'none' }} to={link}>
            <Card className={classes.center}>
                <CardActionArea>
                    <CardHeader
                        avatar={
                            <Avatar className={classNames(classes.iconContainer, classes[color])} >
                                <Icon className={classes.icon} color="inherit" />
                            </Avatar>
                        }
                        titleTypographyProps={{ className: classes.title, color: 'inherit', align: "left", variant: "h6" }}
                        title={title}
                        className={classes.cardHeader}
                    />
                </CardActionArea>
            </Card>
        </Link>
    </Grid>
))

DashboardCard.propTypes = {
    count: PropTypes.number,
    icon: PropTypes.func,
    title: PropTypes.string,
    link: PropTypes.string
}

const mapStateToProps = state => ({
    ...state,
    ...state.Dashboard
})

const icons = {
    1: Language,
    2: Person,
    3: LocalHospital,
    4: AccountBalance,
    5: Group,
    6: Store,
    7: Grocery,
    8: CardGiftcard,
    9: EventAvailable,
    10: AddLocation,
    11: AccountCircle,
    12: LocalHospital,
    13: PersonPin,
    14: PlaylistAddCheck,
    15: SpeakerNotesOff,
    16: Done,
    17: ListIcon,
    18: Navigation,
    19: VpnKey,
    20: CloudUpload,
    21: BugReport,
    22: ClassIcon,
    23: DateRangeIcon,
    24: LocalTaxiIcon,
    25: AttachMoneyIcon,
    26: ShopTwoIcon,
    27: AutorenewIcon,
    28: CalendarTodayIcon,
    29: DirectionsWalkIcon,
    30: DirectionsIcon,
    31: TableChartIcon,
    32: EventBusyIcon,
    33: EventNote,
    34: CompareIcon,
    35: HistoryIcon,
    36: GPSStatusIcon,
    37: AddAPhotoIcon,
    38: AccountBalanceIcon,
    39: LoginIcon,
    40: ReceiptIcon,
    41: AddCircleOutlineIcon,
    42: ControlCameraIcon,
    43: CallReceivedIcon,
    44: StoreIcon,
    45: CardGiftCardIcon
}

const colors = ["blue", "green", "purple", "orange", "brown", "pink", "yellow"];

const MainCategory = ({ title, items }) => (
    <div>
        <Typography variant="h6">
            {title}
        </Typography>
        <Divider />
        <Grid container>
            {items.map(({ title, link, id }, index) => {
                return (
                    <DashboardCard
                        key={index}
                        title={title}
                        Icon={icons[id]}
                        link={'/' + link}
                        color={colors[Math.floor(Math.random() * colors.length)]}
                    />
                )
            })}
        </Grid>
    </div>
);

const DrawerItem = ({ open, items, title, onCollapse,classes }) => {

    if(!items.length)
        return null;

    return (
        <Fragment>
            <ListItem onClick={onCollapse} className={classes.mainItem} button divider dense >
                <ListItemText primary={title} />
                {open ? <ExpandLess /> : <ExpandMore />}
            </ListItem>
            <Collapse in={open} timeout="auto" unmountOnExit>
                {items.map((mainItem, i) => (
                    <List key={i}>
                        <ListItem className={classes.mainListItem}>
                            <ListItemText classes={{ primary: classes.fontWhite }} primary={mainItem.title.toUpperCase()} />
                        </ListItem>
                        {mainItem.items.map((childItem, j) => (
                            <Link key={j} style={{ textDecoration: 'none' }} to={'/' + childItem.link}>
                                <ListItem className={classes.listItem}>
                                    <ListItemText classes={{ primary: classNames(classes.fontWhite, classes.smallSizeFonts) }} primary={childItem.title} />
                                </ListItem>
                            </Link>
                        ))}
                    </List>
                ))}
            </Collapse>
        </Fragment>
    )
}

class DashBoard extends Component {

    constructor(props){
        super(props);

        this.state = {
            type:""
        }
    }

    componentDidMount() {
        const { dispatch } = this.props;

        dispatch(dashboardLoad());
    }

    handleCollapse(_type) {
        return () => {
            const { type } = this.state;

            this.setState({ type: type == _type ? undefined : _type })
        }
    }

    render() {

        const { classes, items } = this.props;
        const { type } = this.state;
        console.log(items)

        let howManySections = 0;

        if(items['sales']&&items['sales'].length){
            howManySections++;
        }
        if(items['main']&&  items['main'].length){
            howManySections++;
        }
        if(items['common']&&  items['common'].length){
            howManySections++;
        }

        return (
            <Layout className={classes.background}>
                <Drawer classes={{ paper: classes.drawer }} variant="permanent" open>
                    <List dense>
                        {/* <DrawerItem classes={classes} onCollapse={this.handleCollapse('sales')} open={type=='sales'||howManySections==1} title="Sales" items={items['sales']} /> */}
                        <DrawerItem classes={classes} onCollapse={this.handleCollapse('main')} open={type=='main'||howManySections==1} title="Main" items={items['main']} />
                        <DrawerItem classes={classes} onCollapse={this.handleCollapse('common')} open={type=='common'||howManySections==1} title="Common" items={items['common']} />
                        {/* <DrawerItem classes={classes} onCollapse={this.handleCollapse('distributor')} open={type=='distributor'||howManySections==1} title="Distributor" items={items['distributor']} /> */}
                    </List>
                </Drawer>
                <Grid container>
                    {
                        items['main'].length ?
                            <Grid md={6} className={classes.padding} item>
                                <Typography variant="h5" align="center">Dashboard</Typography>
                                {
                                    items['main'].map((mainItem, i) => (
                                        <MainCategory key={i} title={mainItem.title} items={mainItem.items} />
                                    ))
                                }
                            </Grid>
                            : null
                    }
                    <Grid md={6} className={classes.padding} item>
                        {/* {  items['sales'].length ?[
                            <Typography variant="h5" key={-1} align="center">Sales</Typography>,
                            items['sales'].map((mainItem, i) => (
                                <MainCategory key={i} title={mainItem.title} items={mainItem.items} />
                            ))
                        ]:null} */}
                        {/* {
                        items['distributor'].length ?[
                            <Typography variant="h5" className={classes.margin} key={-1} align="center">Distributor</Typography>,
                            items['distributor'].map((mainItem, i) => (
                                <MainCategory key={i} title={mainItem.title} items={mainItem.items} />
                            ))
                        ]:null
                        } */}
                    </Grid>
                    {
                        items['common'].length ?
                            <Grid md={12} className={classes.padding} item>
                                <Typography variant="h5" align="center">Common</Typography>
                                {
                                    items['common'].map((mainItem, i) => (
                                        <MainCategory key={i} title={mainItem.title} items={mainItem.items} />
                                    ))
                                }
                            </Grid>
                            : null
                    }
                </Grid>
            </Layout >
        )
    }
}

export default withStyles(styles)(connect(mapStateToProps)(DashBoard));
