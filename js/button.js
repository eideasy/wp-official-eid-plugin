function Button(options, callback) {

    var img = options.img;
    var width = options.width;

    window.onmessage = function (data) {
        if (JSON.stringify(data.data).length == 34) {
            callback(data.data);
        } else {
            console.log("Probably in customize.php, skipping the login: "+JSON.stringify(data.data).length);
        }

    }

    var idid = document.getElementById('idid');
    idid.innerHTML = '<img style="cursor:pointer" src="https://idid.ee/img/login' + img + '.png" width="' + width + '"></img>';
    idid.onclick = function () {
        window.open('https://idid.ee/idcard/oauth2?client_id=' + options.clientId);
    };

}
;