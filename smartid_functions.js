function startSmartIdLogin(loginUri) {
    let w = 800;
    let h = 800;
    // Fixes dual-screen position, Most browsers, Firefox
    let dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : screen.left;
    let dualScreenTop = window.screenTop !== undefined ? window.screenTop : screen.top;

    let width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    let height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

    let left = ((width / 2) - (w / 2)) + dualScreenLeft;
    let top = ((height / 2) - (h / 2)) + dualScreenTop;

    let win = window.open(loginUri, "eID Easy", 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
    let pollTimer = window.setInterval(function () {
        try {
            let url = win.document.URL;
            if (!url.startsWith("http")) {
                return;
            }

            //now the login has completed processing and we are back on the main page
            console.log("Login finished");
            window.clearInterval(pollTimer);

            setTimeout(function () {
                win.close();
                window.location = url;
            }, 150);
        } catch (e) {
        }
    }, 1);
}