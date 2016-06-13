function Button(options, callback) {

    window.onmessage = function (data) {
        if (JSON.stringify(data.data).length === 34 && event.origin === 'https://api.idapi.ee') {
            clearInterval(refreshIntervalId);
            callback(data.data);
        } else {
            console.log("Probably in customize.php, skipping the login: " + JSON.stringify(data.data).length);
        }

    }

    var idLogin = document.getElementById('idLogin');
    idLogin.onclick = function () {

        var myPopup = window.open('https://api.idapi.ee/idcard/oauth2?client_id=' + options.clientId, 'myWindow');

        refreshIntervalId = setInterval(function () {
            var message = 'Hello!  The time is: ' + (new Date().getTime());
            console.log('Attempting ID-card login');
            myPopup.postMessage(message, "https://api.idapi.ee"); //send the message and target URI
        }, 2000);

    };

}