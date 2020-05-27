import createMuiTheme from '@material-ui/core/styles/createMuiTheme';

const theme = createMuiTheme({
    typography: {
        useNextVariants: true,
        fontFamily:'"Open Sans", sans-serif'
    },
    palette: {
        background: {
            default: '#f0f0f0'
        },
        primary: {
            main: '#275c87',
            dark: '#0e2a41',
            light:'#3782a9'
        },
        common:{
            black:'#404040'
        }
    },
    overrides: {
        MuiButton: {
            raisedPrimary: {
                color: 'white',
            },
        },
        MuiAppBar:{
            root:{
                height:'50px',
                fontSize:'20px'
            }
        },
        MuiTableRow:{
            head:{
                height:'30px'
            },
            root:{
                height:'30px'
            }
        },
        MuiOutlinedInput:{
            inputMarginDense:{
                paddingTop:"8px",
                paddingBottom:"8px"
            }
        },
        MuiInputLabel:{
            marginDense:{
                transform:"translate(9px, 8px) scale(1)!important",
            },
            shrink:{
                transform:"translate(14px, -6px) scale(0.75)!important",
            }
        },
        MuiFormLabel:{
            root:{
                fontSize:'.85em'
            }
        }
    }
});

export default theme;
