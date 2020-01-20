function startSmartIdLogin(loginUri) {
    var w = 800;
    var h = 800;
    var left = (screen.width / 2) - (w / 2);
    var top = (screen.height / 2) - (h / 2);
    var win = window.open(loginUri, "Smart ID login", 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
    var pollTimer = window.setInterval(function () {
        try {
            var url = win.document.URL;
            if (!url.startsWith("http")) {
                return;
            }

            //now the login has completed processing and we are back on the main page
            console.log("Login finished");
            window.clearInterval(pollTimer);

            setTimeout(function () {
                win.close();
                window.location = url;
            }, 50);
        } catch (e) {
        }
    }, 1);
}