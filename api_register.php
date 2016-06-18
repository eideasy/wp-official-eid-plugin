<div class="container">
    <div id="loginBlock" class="col-md-offset-3 col-md-6" style="display: <?php echo array_key_exists("auth_key", $_SESSION) ? "none" : "block" ?>">
        <h1>Please authenticate yourself before activating the ID-API service</h1>
        <div id="idlogin"></div>
        <script src="https://wpidkaartproxy.dev/js/button.js"></script>
        <script>
            new Button({clientId: 'new_api'}, function (token) {
                document.getElementById("register_form").style.display = "block";
                document.getElementById("loginBlock").style.display = "none";
                document.getElementById("auth_key").value = token;
                document.getElementById("form_auth_key").value = token;
            });

            function apiRegister() {
                jQuery.ajax('https://wpidkaartproxy.dev/api/v1/register_api', {
                    dataType: "jsonp",
                    data: {
                        auth_key: document.getElementById("form_auth_key").value,
                        domain: document.getElementById("domain").value
                    },
                    success: function (result) {
                        console.log("Register api call done, client_id=" + result.client_id);
                        if (result.status === "OK") {                            
                            document.getElementById("form_client_id").value = result.client_id;
                            document.getElementById("form_secret").value = result.secret;
                            document.getElementById("registerSuccessForm").submit();
                        }
                    }
                });
            }
        </script>
        <form id="registerSuccessForm" action="" method="post">
            <input name="status" value="activation_done" type="hidden">
            <input id="form_client_id" name="form_client_id" value="" type="hidden">
            <input id="form_secret" name="form_secret" value="" type="hidden">
            <input id="form_auth_key" name="form_auth_key" value="<?php echo (array_key_exists("auth_key", $_SESSION) ? $_SESSION['auth_key'] : "") ?>" type="hidden">
        </form>
    </div>
</div>

<div id="register_form" class="container" style="display: <?php echo array_key_exists("auth_key", $_SESSION) ? "block" : "none" ?>">
    <input id="auth_key" type="hidden">
    <input id="domain" name="domain" type="hidden" value="<?php echo get_site_url() ?>">   
    <h3>By registering you accept the below terms and conditions</h3>
    <button id="registerbutton" class="page-title-action" style="height:70px;font-size:200%" onclick="apiRegister()">Click here to get the API key</button>
    <br>
    <div>
        <?php include("terms.html"); ?>
    </div>
</div>

<script>
    window.onload = function () {
        if (typeof jQuery === 'undefined') {
            console.log("jQuery missing, loading...");
            var headTag = document.getElementsByTagName("head")[0];
            var jqTag = document.createElement('script');
            jqTag.type = 'text/javascript';
            jqTag.src = 'https://code.jquery.com/jquery-2.2.4.min.js';
            jqTag.onload = myJQueryCode;
            headTag.appendChild(jqTag);
        } else {
            console.log("Great jQuery present, all good");
        }
    };
</script>




