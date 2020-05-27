/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


// Array functions
Array.prototype.mapToObject = function(key){
    let newObj = {};
    this.forEach(item=>{
        newObj[item[key]] = item;
    })
    return newObj;
}


Array.prototype.uniqueWith = function(key){
    let newObj = {};

    this.forEach(function(item) {
        newObj[item[key]] = item;
    })

    return Object.values(newObj);

}