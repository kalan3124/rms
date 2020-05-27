import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { closeSidebar, expandSection, collapseSection, changeSidebar } from '../../actions/Sidebar';
import { connect } from 'react-redux';

import Collapse from '@material-ui/core/Collapse';
import ExpandLess from '@material-ui/icons/ExpandLess';
import ExpandMore from '@material-ui/icons/ExpandMore';
import  withStyles from '@material-ui/core/styles/withStyles'
import Drawer from '@material-ui/core/Drawer';
import List from '@material-ui/core/List';
import ListItem from '@material-ui/core/ListItem';
import Divider from '@material-ui/core/Divider';
import ListItemText from '@material-ui/core/ListItemText';

import Link from 'react-router-dom/Link';
import withRouter from 'react-router-dom/withRouter';

import classNames from 'classnames';
import { APP_URL } from '../../constants/config';

const styles = theme => ({
    drawerList: {
        overflowY: 'auto',
    },
    drawerContainer: {
        paddingTop: '60px'
    },
    listItem: {
        paddingLeft: theme.spacing.unit,
        cursor: 'pointer'
    },
    expandedListItem: {
        paddingLeft: theme.spacing.unit,
        background:'rgba(0,0,0,0.5)',
        color: theme.palette.common.white
    },
    listItemIcon: {
        color: theme.palette.grey[300],
        margin: 0
    },
    selectedListItem: {
        background: theme.palette.primary.main
    },
    selectedListItemIcon: {
        color: theme.palette.common.white
    },
    iconList: {
        minHeight: '100%',
        padding: 0
    },
    mainTitle: {
        paddingLeft: theme.spacing.unit
    },
    listItem: {
        paddingLeft: theme.spacing.unit * 2,
        minWidth: theme.spacing.unit * 26
    },
    iconListItem: {
        textAlign: 'center',
        paddingLeft: theme.spacing.unit
    },
    font: {
        fontSize: '1em',
        color:theme.palette.common.white,
        textShadow:'2px 2px 4px #000',
    },
    fontWhite: {
        color: theme.palette.common.white
    },
    hiddenMobile:{
        [theme.breakpoints.down('sm')]: {
            display:'none'
        },
    },
    drawer: {
        padding: 0,
        width: theme.spacing.unit * 20,
        background: '#000',
        paddingTop: 56,
        paddingBottom: 56,
        [`${theme.breakpoints.up('xs')} and (orientation: landscape)`]: {
            paddingTop: 48,
            paddingBottom: 48,
        },
        [theme.breakpoints.up('sm')]: {
            paddingTop: 50,
            paddingBottom: 50,
        },
        overflowY:'auto'
    },
    mainListItem: {
        padding: 0,
        background: '#000',
        paddingLeft: theme.spacing.unit * 1
    },
    listItem: {
        background: '#134f5c',
        padding: theme.spacing.unit,
        paddingLeft: theme.spacing.unit * 2,
        borderTop: 'solid 2px #000'
    },
    smallSizeFonts:{
        fontSize:'.75em'
    },
    padding:{
        paddingBottom:100
    }
})

const mapStateToProps = state => ({
    ...state,
    ...state.Sidebar
})

class Sidebar extends Component {

    componentDidMount() {
        const { match } = this.props;
        
        if(match.path.slice(0,12)=='/:type/:menu')
            this.handleSidebarChange(match.params.type,match.params.menu);
        else if(match.path=='/')
            this.handleSidebarChange(undefined);
    }

    handleSidebarChange(type,menu) {
        this.props.dispatch(changeSidebar(type,menu))
    }

    handleDrawerClose() {
        const { dispatch, sidebarMenu } = this.props;
        if (sidebarMenu)
            dispatch(closeSidebar())
    }

    handleToggleSection(id, expanded) {
        const { dispatch } = this.props;

        if (expanded) {     
            dispatch(collapseSection(id));
        } else {
            dispatch(expandSection(id));
        }
    }

    renderItem(item, id, parentId, first = false) {
        const { expandedSections, classes } = this.props;


        if (typeof item.link != 'undefined') {
            return (
                <Link  key={id} style={{ textDecoration: 'none' }} to={'/' + parentId +'/'+item.link}>
                    <ListItem className={classes.listItem}>
                        <ListItemText classes={{primary: classNames(classes.fontWhite,classes.smallSizeFonts) }} primary={item.title} />
                    </ListItem>
                </Link>
            )
        } else {
            let expanded = expandedSections.includes(id) || first;
            return (
                <div className={classes.padding} key={id}>
                    <ListItem onClick={e => this.handleToggleSection(id, expanded)} className={classes.mainListItem}>
                        <ListItemText classes={{primary:classes.fontWhite}} primary={item.title.toUpperCase()} />
                        {expanded ? <ExpandLess /> : <ExpandMore />}
                    </ListItem>
                    <Divider />
                    <Collapse in={expanded} timeout="auto" unmountOnExit>
                        <List disablePadding>
                            {
                                Object.keys(item.items).map(childId => (
                                    this.renderItem(item.items[childId], childId, id)
                                ))
                            }
                        </List>
                    </Collapse>
                    <Divider />
                </div>
            )
        }
    }

    render() {
        const { classes, items, sidebarMenu,systemType,hiddenMobile } = this.props;

        if (typeof sidebarMenu == 'undefined') return null;

        if (!Object.keys(items).length) return null;

        return (
            <Drawer classes={{paper: classNames(classes.drawer,hiddenMobile?classes.hiddenMobile:undefined)}} onClose={(this.handleDrawerClose).bind(this)} open={true} variant="permanent">
                {this.renderItem(items[systemType][sidebarMenu], systemType+'/'+sidebarMenu, sidebarMenu, true)}
            </Drawer>
        )
    }
}

Sidebar.propTypes = {
    items: PropTypes.object.isRequired
}

export default withStyles(styles)(connect(mapStateToProps)(withRouter(Sidebar)));