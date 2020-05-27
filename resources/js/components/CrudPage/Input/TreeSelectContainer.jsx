import React from 'react';
import PropTypes from 'prop-types';

class Container extends React.Component {
    render() {
        const { style, decorators, terminal, onClick, node } = this.props;

        return (
            <div 
                style={style.container}>
                <decorators.Header node={node}
                    style={style.header} onClick={onClick} />
                <div style={style.toggle} onClick={onClick}
                ref={ref => this.clickableRef = ref} >
                {!terminal ? this.renderToggle() : null}
                </div>
            </div>
        );
    }

    renderToggle(){
        const {node,decorators,style,onClick} = this.props;

        const toggleProps = {
            onClick
        }

        return (node.toggled)?<decorators.MoreToggle {...toggleProps} />:<decorators.LessToggle {...toggleProps} />
    }
}

Container.propTypes = {
    style: PropTypes.object.isRequired,
    decorators: PropTypes.object.isRequired,
    terminal: PropTypes.bool.isRequired,
    onClick: PropTypes.func.isRequired,
    animations: PropTypes.oneOfType([
        PropTypes.object,
        PropTypes.bool
    ]).isRequired,
    node: PropTypes.object.isRequired
};

export default Container;