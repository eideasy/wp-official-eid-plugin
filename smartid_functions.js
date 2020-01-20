function startSmartIdLogin(loginUri) {
    var w = 800;
    var h = 800;
    var left = (screen.width / 2) - (w / 2);
    var top = (screen.height / 2) - (h / 2);
    var win = window.open(loginUri, "Smart ID login", 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
    var pollTimer = window.setInterval(function () {
        try {
            if (win.document.URL.indexOf("code=") !== -1) {
                console.log("Login finished");
                window.clearInterval(pollTimer);
                var url = win.document.URL;
                code = gup(url, 'code');
                if (code.length > 1) {
                    win.close();
                }
                window.location = url;
            }
        } catch (e) {
        }
    }, 1);
}

function gup(url, name) {
    name = name.replace(/[[]/, "\[").replace(/[]]/, "\]");
    var regexS = "[\?&]" + name + "=([^&#]*)";
    var regex = new RegExp(regexS);
    var results = regex.exec(url);
    if (results == null) {
        return "";
    } else {
        return results[1];
    }
}


