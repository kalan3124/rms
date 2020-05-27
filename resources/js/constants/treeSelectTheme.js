export default {
    tree: {
        base: {
            listStyle: 'none',
            backgroundColor: '#fff',
            margin: 0,
            padding: 0,
            color: 'rgba(0, 0, 0, 0.87)',
            fontFamily: '"Open Sans", sans-serif',
            fontSize: '14px'
        },
        node: {
            width:'100%',
            base: {
                position: 'relative',
                paddingBottom:'4px',
            },
            link: {
                cursor: 'pointer',
                position: 'relative',
                padding: '0px 5px',
                display: 'block'
            },
            activeLink: {
                background: '#31363F'
            },
            toggle: {
                height: 14,
                display:'inline-block'
            },
            header: {
                base: {
                },
                connector: {
                },
                title: {
                    lineHeight: '24px',
                    verticalAlign: 'middle'
                },
                width:'80%',
                display:'inline-block'
            },
            subtree: {
                listStyle: 'none',
                paddingLeft: '19px',
                borderTop:'1px solid rgba(0,0,0,0.3)',
            },
            loading: {
                color: '#E2C089'
            }
        }
    }
};