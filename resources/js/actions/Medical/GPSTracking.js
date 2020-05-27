import {
    GPS_USER_CHANGE,
    GPS_DATA_LOADED,
    GPS_DATE_CHANGE,
    GPS_TIME_CHANGE
} from '../../constants/actionTypes';
import {
    alertDialog
} from '../Dialogs';
import agent from '../../agent';
import { APP_URL } from '../../constants/config';

export const changeUser = user => ({
    type: GPS_USER_CHANGE,
    payload: {
        user
    }
})

export const changeDate = date=>({
    type:GPS_DATE_CHANGE,
    payload:{date}
})

export const dataLoaded = (coordinates,startTime,checkin,checkout) => ({
    type: GPS_DATA_LOADED,
    payload: {
        coordinates,startTime,checkin,checkout
    }
})

export const changeTime = (currentTime) => ({
    type: GPS_TIME_CHANGE,
    payload: {
        currentTime:Math.round(currentTime)
    }
})

export const search = (user, date) => dispatch => {
    if (!user || !date) {
        dispatch(alertDialog("Please fill all inputs to search.", "error"));
        return;
    }

    agent.GPS.search(user, date).then(({coordinates,startTime,checkin,checkout}) => {
        let sortedCoordinates = coordinates.sort((a,b)=>(a.time - b.time)).map(coord=>{
            let icon = undefined;
            let infoWindow = undefined;
            let color = undefined;
            let backColor = undefined;


            if(typeof coord.type !=='undefined'){

                switch (coord.type.toString()) {
                    case "1":
                        icon = APP_URL + "images/map-productive.png";
                        backColor = 'green';
                        color = 'white';
                        break;
                    case "0":
                        icon = APP_URL + "images/map-unproductive.png";
                        backColor = 'red';
                        color = 'white';
                        break;
                    case "2":
                        icon = APP_URL + "images/checkin.svg";
                        backColor = 'black';
                        color = 'white';
                        break;
                    case "3":
                        icon = APP_URL + "images/checkout.svg";
                        backColor = 'black';
                        color = 'white';
                        break;
                    default:
                        break;
                }

                infoWindow = {
                    // content:"<h5>"+coord.title+"</h5>"+(coord.description?"<hr/>"+coord.description:"")
                    content: "<h5 style='color:" + color + ";background-color:" + backColor + "'>" + coord.title + "</h5>" + (coord.description ? "<hr/>" + coord.description : "")
                }
            }

            return {
                ...coord,
                lat:parseFloat(coord.lat),
                lng: parseFloat(coord.lng),
                time: typeof coord.time=='number'?coord.time: parseInt(coord.time),
                accurazy: typeof coord.accurazy =='number'?coord.accurazy: parseInt(coord.accurazy),
                batry: typeof coord.batry =='number'?coord.batry: parseInt(coord.batry),
                marker: typeof coord.type !='undefined',
                icon,
                infoWindow
            };
        })
        dispatch(dataLoaded(sortedCoordinates,startTime,checkin,checkout));
    }).catch(err => {
        console.error(err);
        dispatch(alertDialog(err.response.data.message, "error"));
    })
};
