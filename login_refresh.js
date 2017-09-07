document.addEventListener('DOMContentLoaded', function () {
    if (window.opener && window.opener !== window) {
        console.log("I am in a popup, I should close myself if there are no errors to show");
        window.close();
    }
    if (self == top) {
        console.log("Reloading page to finish login");
        location = removeURLParameter(self.location.href, 'code');
    } else {
        console.log("Running in iFrame, need to break free to finish login.");
        top.location = removeURLParameter(self.location.href, 'code');
    }

});


function removeURLParameter(url, parameter) {
    //prefer to use l.search if you have a location/link object
    var urlparts = url.split('?');
    if (urlparts.length >= 2) {

        var prefix = encodeURIComponent(parameter) + '=';
        var pars = urlparts[1].split(/[&;]/g);

        //reverse iteration as may be destructive
        for (var i = pars.length; i-- > 0;) {
            //idiom for string.startsWith
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                pars.splice(i, 1);
            }
        }

        url = urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : "");
        return url;
    } else {
        return url;
    }
}


