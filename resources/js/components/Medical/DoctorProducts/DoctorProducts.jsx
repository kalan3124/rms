import React , {Component} from 'react';

import Grid from '@material-ui/core/Grid';
import Paper from '@material-ui/core/Paper';
import Typography from '@material-ui/core/Typography';
import Divider from '@material-ui/core/Divider';

import Layout from '../../App/Layout';

class DoctorProducts extends Component{
    render(){
        return(
            <Layout sidebar>
                <Grid container>
                    <Grid md={8} item>
                        <Paper>
                            <Typography variant="h6" align="center">Doctor Products Allocations</Typography>
                            <Divider/>
                            
                        </Paper>
                    </Grid>
                </Grid>
            </Layout>
        )
    }
}

export default DoctorProducts;