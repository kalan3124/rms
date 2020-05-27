import React, { Component } from 'react';
import { Treebeard, decorators } from 'react-treebeard';
import agent from '../../../agent';
import theme from '../../../constants/treeSelectTheme';
import Container from './TreeSelectContainer'

import TextField from '@material-ui/core/TextField';
import Paper from '@material-ui/core/Paper';
import Fade from '@material-ui/core/Fade';
import withStyles from '@material-ui/core/styles/withStyles'
import ClickAwayListener from '@material-ui/core/ClickAwayListener';
import Checkbox from '@material-ui/core/Checkbox';
import FormControlLabel from '@material-ui/core/FormControlLabel';
import Popper from '@material-ui/core/Popper';
import ExpandMore from '@material-ui/icons/ExpandMore';
import ExpandLess from '@material-ui/icons/ExpandLess';

import set from 'lodash.set'
import get from 'lodash.get'

decorators.Loading = (props) => {
    return (
        <div style={props.style}>
            loading...
        </div>
    );
};

decorators.MoreToggle = (props) => {
    return (
        <div>
            <ExpandMore />
        </div>
    );
};

decorators.LessToggle = (props) => {
    return (
        <div>
            <ExpandLess />
        </div>
    );
};

decorators.Container = Container;

const styles = theme => ({
    popper: {
        zIndex: 1600,
    },
    paper: {
        borderRadius: theme.spacing.unit,
        minWidth: theme.spacing.unit * 28,
        maxWidth: theme.spacing.unit * 70,
        maxHeight: theme.spacing.unit * 40,
        overflow: 'auto',
        boxShadow: '1px 1px 4px #404040'
    }
})

class TreeSelect extends Component {

    constructor(props) {
        super(props);
        this.state = {
            show: false,
            anchorEl: null,
            checked: [],
            halfChecked: [],
            data: {
                toggled: false,
                loading: true,
                synced: false,
                children: [],
                childrenLink: props.parent,
                name: props.label,
                id: props.parent,
                path: '',
                checked: false,
                halfChecked: false,
                type:'parent'
            }
        };
        this.onToggle = this.onToggle.bind(this);
        this.onShowPopup = this.onShowPopup.bind(this);
        this.onClosePopup = this.onClosePopup.bind(this);
        this.onCheckHandler = this.onCheckHandler.bind(this);
        this.renderHeader = this.renderHeader.bind(this);
    }

    loadOptions(node) {
        const { hierarchy,value } = this.props;

        let checkedNodeIds = value==''?[]:value.map(child=>child.id)

        let link = node.childrenLink;

        let isParent = typeof hierarchy[link] != 'undefined';

        let formatedTerms = {};

        if (isParent) {
            let searchTerms = hierarchy[link].term;
            if (typeof searchTerms != 'undefined') {
                formatedTerms = this.resolveParameteredObject(node.id,searchTerms);
            }
        }

        agent.Crud.dropdown(link, '', formatedTerms, false).then(data => {
            node.children = data.map((opt, i) => (
                {
                    name: opt.label,
                    childrenLink: isParent ? hierarchy[link].children : undefined,
                    loading: true,
                    synced: false,
                    toggled: false,
                    children: isParent && typeof hierarchy[link].children != 'undefined' ? [] : undefined,
                    id: node.id + '-' + opt.value,
                    path: (node.path == '' ? 'children.' : node.path + '.children.') + i,
                    checked: node.checked||checkedNodeIds.includes(node.id + '-' + opt.value),
                    halfChecked: node.halfChecked || node.checked,
                    type:node.childrenLink
                }
            ));
            node.loading = false;
            node.synced = true;
            node.toggled = true;
            this.updateNode(node)
        })
    }

    resolveParameteredObject(id, obj) {
        let path = id.split('-').slice(1)
        let formatedObj = { ...obj };
        Object.keys(formatedObj).forEach(name => {
            let pattern = formatedObj[name];
            if (pattern&&pattern.search(/\{\d+\}/) + 1) {
                formatedObj[name] = path[pattern.split(/\{(\d+)\}/)[1] - 1];
            }
        })

        return formatedObj;
    }

    renderHeader(props) {

        const { checked, halfChecked } = this.state;

        return (
            <div style={props.style}>
                <FormControlLabel
                    control={
                        <Checkbox
                            checked={props.node.checked}
                            onChange={e => { this.onCheckHandler(props.node) }}
                            indeterminate={props.node.halfChecked}
                        />
                    }
                    label={props.node.name}
                />
            </div>
        );
    }

    setParentsStatus(data, path, checked, halfChecked, toggled) {
        let modifiedNodes = { ...data };

        if (path == '') return;

        path = path.substr(9);

        let parentPaths = path.split('.children.').slice(0, -1);

        let parentPathsLength = parentPaths.length;

        for (let index = 0; index < parentPathsLength; index++) {
            let parentPath = 'children.' + parentPaths.join('.children.');
            parentPaths.pop();

            let parent = get(modifiedNodes, parentPath);

            if (typeof checked != 'undefined') parent.checked = checked;
            if (typeof halfChecked != 'undefined') parent.halfChecked = halfChecked;
            if (typeof toggled != 'undefined') parent.toggled = toggled;

            modifiedNodes = set(modifiedNodes, parentPath, parent);
        }

        if (typeof checked != 'undefined') modifiedNodes.checked = checked;
        if (typeof halfChecked != 'undefined') modifiedNodes.halfChecked = halfChecked;
        if (typeof toggled != 'undefined') modifiedNodes.toggled = toggled;

        return modifiedNodes;
    }

    setChildrenStatus(data, checked, halfChecked, toggled) {

        let modifiedNodes = { ...data };

        if (typeof modifiedNodes.children == 'undefined') return modifiedNodes;

        modifiedNodes.children = data.children.map(child => {
            if (typeof checked != 'undefined') child.checked = checked;
            if (typeof halfChecked != 'undefined') child.halfChecked = halfChecked;
            if (typeof toggled != 'undefined') child.toggled = toggled;

            if (typeof child.children != 'undefined') child = this.setChildrenStatus(child, checked, halfChecked, toggled);

            return child;
        })

        return modifiedNodes;
    }

    updateNode(modifiedNode) {
        const { data } = this.state;

        let modifiedNodes;

        if (modifiedNode.path == '') {
            modifiedNodes = { ...modifiedNode }
        } else {
            modifiedNodes = set(data, modifiedNode.path, modifiedNode);
        }

        this.setState({ data: modifiedNodes });
    }

    onToggle(node, toggled) {
        let modifiedNode = { ...node };

        modifiedNode.toggled = !modifiedNode.toggled;

        if (modifiedNode.synced) {
            this.updateNode(modifiedNode);
        } else {
            this.loadOptions(modifiedNode);
        }

    }

    onClosePopup() {
        this.setState({ show: false })
    }

    onShowPopup({ currentTarget }) {
        this.setState({ show: true, anchorEl: currentTarget })
    }

    onCheckHandler(node) {
        if (!node.checked && !node.halfChecked) {
            this.checkNode(node);
        } else {
            this.uncheckNode(node)
        }
    }

    getParentByNode(node){
        const {data} = this.state;

        const parentPaths = node.path.substr(9).split('.children.').slice(0, -1);

        return (parentPaths.length) ? get(data, 'children.' + parentPaths.join('.children.')) : {...data};
    }

    checkNode(node) {
        const { data } = this.state;

        let modifiedNodes = { ...data };
        let modifiedNode = { ...node }

        if (node.path != '') {
            
            let parent = this.getParentByNode(node);

            let sibilingSelected = true;

            parent.children.forEach(child => {
                if (!child.checked && child.id != node.id) sibilingSelected = false;
            })

            if (sibilingSelected) {
                this.checkNode(parent);
                return;
            }
        }
        modifiedNode.checked = true;
        modifiedNode.halfChecked = false;

        modifiedNode = this.setChildrenStatus(modifiedNode, true, true);

        if (modifiedNode.path != '') {
            modifiedNodes = set(modifiedNodes, modifiedNode.path, modifiedNode);

            modifiedNodes = this.setParentsStatus(modifiedNodes, modifiedNode.path, false, true);
        } else {
            modifiedNodes = { ...modifiedNode }
        }
        this.updateNode(modifiedNodes);
        this.props.onChange(this.filterChecked(modifiedNodes));
    }

    uncheckNode(node) {
        const { data } = this.state;

        let modifiedNode = { ...node };
        let modifiedNodes = { ...data };

        if (node.path != '') {
            let parent = this.getParentByNode(node)

            let sibilingsUnchecked = true;

            parent.children.forEach(child => {
                if (child.checked && child.id != node.id) sibilingsUnchecked = false;
            })

            if (sibilingsUnchecked) {
                this.uncheckNode(parent);
                return;
            }

            parent.children = parent.children.map(child => {
                child.halfChecked = false;
                return child;
            });

            modifiedNodes = parent.path == '' ? { ...parent } : set(modifiedNodes, parent.path, parent);

        }

        modifiedNode = this.setChildrenStatus(modifiedNode, false, false);
        modifiedNode.checked = false;
        modifiedNode.halfChecked = false;

        if (modifiedNode.path != '') {
            modifiedNodes = set(modifiedNodes, modifiedNode.path, modifiedNode);
            modifiedNodes = this.setParentsStatus(modifiedNodes, modifiedNode.path, false, true)
        } else {
            modifiedNodes = { ...modifiedNode }
        }

        this.updateNode(modifiedNodes);

        this.props.onChange(this.filterChecked(modifiedNodes));
    }

    filterChecked(node) {
        const {hierarchy} = this.props;

        if (typeof node == 'undefined') node = { ...this.state.data };

        let checked = Array();

        node.children.forEach(child => {
            if (child.checked && !child.halfChecked) {

                let formatedParams = hierarchy[child.type].param;

                if(typeof formatedParams=='undefined') formatedParams={};

                formatedParams = this.resolveParameteredObject(child.id,formatedParams);

                formatedParams.type = child.type;
                formatedParams.name = child.name;
                formatedParams.id = child.id;

                checked.push(formatedParams);
            }

            if (typeof child.children != 'undefined') {
                let childChecked = this.filterChecked(child);
                checked = checked.concat(childChecked);
            }
        })

        return checked;
    }

    render() {
        const { label, name, classes,value } = this.props;

        const { show, anchorEl, data } = this.state;

        decorators.Header = this.renderHeader;

        let checked = value ==''?[]:[...value];
        
        return (
            <div>
                <TextField
                    label={label}
                    id={name}
                    value={checked.length?checked.map(node=>node.name).join(","):"All Selected." }
                    margin="dense"
                    variant="outlined"
                    onClick={this.onShowPopup}
                    fullWidth
                />
                <Popper className={classes.popper} open={show} placement="top-end" anchorEl={anchorEl} transition>
                    {({ TransitionProps }) => (
                        <Fade {...TransitionProps} timeout={350}>
                            <ClickAwayListener onClickAway={this.onClosePopup}>
                                <Paper style={typeof anchorEl != 'undefined' ? { width: anchorEl.offsetWidth } : undefined} className={classes.paper}>
                                    <Treebeard
                                        data={data}
                                        onToggle={this.onToggle}
                                        decorators={decorators}
                                        style={theme}
                                        animations={false}
                                    />
                                </Paper>
                            </ClickAwayListener>
                        </Fade>
                    )}
                </Popper>


            </div>
        );
    }
}

export default withStyles(styles)(TreeSelect);