import React,{Component} from 'react';
import PropTypes from "prop-types";
import Header from './Header';
import Sidebar from './Sidebar';
import { loadSidebar } from '../../actions/Layout';
import {connect} from 'react-redux';
import Dialogs from './Dialogs';
import  withStyles  from '@material-ui/core/styles/withStyles';
import { toggleSideBar } from '../../actions/Header';
import {TinyButton as ScrollUpButton} from "react-scroll-up-button";
// import SnowStorm from 'react-snowstorm';
// import { APP_URL } from '../../constants/config';

const mapStateToProps = state=>({
    ...state,
    ...state.Layout
})

const mapDispatchToProps = dispatch =>({
    onToggleSidebar:()=>dispatch(toggleSideBar()),
    onLoadSidebar:()=>dispatch(loadSidebar())
})

const styles = theme=>({
    layout:{
        paddingRight:theme.spacing.unit*4,
        paddingLeft:theme.spacing.unit*24,
        paddingBottom:theme.spacing.unit*4,
        [theme.breakpoints.down('sm')]: {
            paddingRight:0,
            paddingLeft:0,
            paddingBottom:0,
        },
    },
    footer:{
        position:"fixed",
        bottom:0,
        right:0,
        background:"rgba(65,110,189,0.5)",
        fontSize:'.75em',
        paddingLeft:theme.spacing.unit,
        borderTopLeftRadius:theme.spacing.unit,
        pointerEvents:"none"
    }
})

class Layout extends Component{

    componentDidMount(){
        const {onLoadSidebar} = this.props;
        onLoadSidebar();
    }

    filterLinks(sidebarItems){
        if (typeof sidebarItems=='undefined')  sidebarItems = this.props.sidebarItems;

        let filteredItems = [];

        Object.keys(sidebarItems).forEach(id=>{

            let item = sidebarItems[id];

            if(typeof item.link!='undefined'){
                filteredItems.push(item);
            }else {
                let childFilteredItems = this.filterLinks(item.items);
                filteredItems = [...filteredItems,...childFilteredItems];
            }
        })

        return filteredItems;
    }

    render(){
        const {children,className,classes,sidebar,onToggleSidebar} = this.props;

        // let filteredItems = this.filterLinks();


        return(
            <div className={sidebar?classes.layout:className}>
                <Header onClickLogo={onToggleSidebar} />
                {/* <SnowStorm
                    excludeMobile={false}
                    snowColor="#E0FFFF"
                    usePositionFixed={true}
                /> */}
                {this.renderSidebar()}
                <div style={{paddingTop:'60px'}}>
                {children}
                <Dialogs/>
                </div>
                <div className={classes.footer}>© {this.getYear()} Ceylon Linux (PVT) LTD| All Rights Reserved.</div>
                <ScrollUpButton />
                {/* <img height="64" className="santaImage" src={APP_URL+"svg/santa_penguin.svg"} /> */}
            </div>
        )
    }

    renderSidebar(){
        const {sidebar,sidebarItems} = this.props;

        if(!sidebar) return null;

        return (
            <Sidebar items={sidebarItems}/>
        );
    }

    getYear() {
        return new Date().getFullYear();
    }
}

Layout.propTypes = {
    sidebar: PropTypes.bool
};

export default connect(mapStateToProps,mapDispatchToProps)(withStyles(styles)(Layout))
