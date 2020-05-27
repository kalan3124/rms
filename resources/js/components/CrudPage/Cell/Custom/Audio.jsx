import React from 'react';
import { APP_URL } from '../../../../constants/config';

export default ({value})=>{
    if(!value) return null;

    return (
        <div>
            <audio src={APP_URL+'storage/sounds/'+value+'.mp3'}  controls>
                    Your browser does not support the
                    <code>audio</code> element.
            </audio>
        </div>
    );
}