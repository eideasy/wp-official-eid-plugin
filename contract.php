<?php
defined('ABSPATH') or die('No script kiddies please!');

require_once(ABSPATH . 'wp-load.php');

if (!class_exists("IdContract")) {

    class IdContract {

        public function showContract() {
            if (!session_id()) {
                session_start();
            }

            $newHtml = NULL;

            if (array_key_exists('issubmit', $_POST) && $_POST['issubmit'] == "yes") {
                $newHtml = IdContract::submitForm();
            } elseif (array_key_exists('signature_created', $_POST) && $_POST['signature_created'] == "yes") {
                $newHtml = IdContract::signatureCreated();
            } elseif (array_key_exists('mid_sign_refresh_form', $_POST) && $_POST['mid_sign_refresh_form'] == "yes") {
                $newHtml = IdContract::waitMidSigning();
            }

            if ($newHtml == null) {
                return IdContract::getContractHtml();
            } else {
                return $newHtml;
            }
        }

        function waitMidSigning() {
            $signatureResult = IdCardLogin::curlCall("api/v1/sign/finishmidsign/" . $_SESSION['signing_contract_id'], []);
            if ($signatureResult['status'] == "OUTSTANDING_TRANSACTION") {
                return IdContract::midRefreshWaitSignature($_SESSION['challenge']);
            } elseif (($signatureResult['status'] == "SIGNATURE")) {
                return '<a href="https://api.smartid.dev/sign/getsignedfile/' . $signatureResult['bdocUrl']
                        . '">Document successfully signed. Download signed contract from here</a>';
            }
        }

        function midRefreshWaitSignature($challengeCode) {
            $refreshForm = '<div>Please enter PIN2 in your mobile to sign the contract. Challenge code is <b>' . $challengeCode . '</b></div>'
                    . '<form id="mid_sign_refresh_form" action="" method="post">'
                    . '<input type="hidden" name="mid_sign_refresh_form" value="yes">'
                    . '</form>'
                    . '<script>'
                    . 'var form = document.getElementById("mid_sign_refresh_form");'
                    . 'setTimeout(function(){ form.submit(); }, 5000);'
                    . '</script>';
            return $refreshForm;
        }

        public function signatureCreated() {
            $params = [
                "contract_id" => $_SESSION['signing_contract_id']
            ];

            if ($_SESSION['login_source'] == "id-card") {
                $params["signature_id"] = $_POST['signature_id'];
                $params["signature_value"] = $_POST['signature_value'];
                $signatureResult = IdCardLogin::curlCall("api/v1/sign/finishidsign", $params);
            } else if ($_SESSION['login_source'] == "mobile-id") {

                $signatureResult = IdCardLogin::curlCall("api/v1/sign/startmidsign/" . $_SESSION['signing_contract_id'], $params);
                if ($signatureResult['status'] == "OK") {
                    $_SESSION['challenge'] = $signatureResult["challenge"];
                    return IdContract::midRefreshWaitSignature($signatureResult["challenge"]);
                } else {
                    return "Mobile-id signing failed because of: " . $signatureResult['message'];
                }
            } else {
                return "Signing failed, cannot determine if logged in with id-card or mobile-id";
            }

            if ($signatureResult["status"] === "error") {
                return "<b>Signing failed because of: " . $signatureResult['message'] . "</b><br>" . IdContract::getContractHtml();
            }

            if (array_key_exists("status", $signatureResult) && $signatureResult["status"] === "OK") {
                return '<a href="https://api.smartid.dev/sign/getsignedfile/' . $signatureResult['bdocUrl']
                        . '">Document successfully signed. Download signed contract from here</a>';
            } else {
                return "Technical problem signing the document, please contact help@idapi.ee";
            }
        }

        public function submitForm() {
            //During first form page view nothing happens yet                  
            if (array_key_exists('issubmit', $_POST) && $_POST['issubmit'] == "yes") {
                $contractId = $_SESSION['contract_id'];
                $validateResult = IdContract::validateTags($contractId, $_POST);
                //chekc if there was form validation errors
                if ($validateResult != null) {
                    return $validateResult . IdContract::getContractHtml();
                }
                $pdfLocation = IdContract::generatePdf($contractId, $_POST);
                if ($pdfLocation["status"] === "error") {
                    return "<b>Form submit failed because of: " . $pdfLocation['message'] . "</b><br>" . IdContract::getContractHtml();
                }
                if ($pdfLocation['status'] === "OK") {
                    $_SESSION['signing_contract_id'] = $pdfLocation['contract_id'];
                    return '<a href="https://api.smartid.dev/storage/pdf/' . $pdfLocation['pdfUrl']
                            . '">Download PDF to be signed from here</a>' . IdContract::getSigningCode();
                } else {
                    return "Something went wrong and PDF cannt be created, Please contact info@idapi.ee";
                }
            } else {
                return NULL;
            }
        }

        private function getSigningCode() {
            $contractId = $_SESSION['signing_contract_id'];
            if ($_SESSION['login_source'] == "mobile-id") {
                return IdContract::getMidSigningCode($contractId);
            }
            ?>
            <button onclick="startSigning()">Start signing</button>
            <script type="text/javascript" src="<?php echo IdCardLogin::getPluginBaseUrl() ?>/js/hwcrypto.js"></script>
            <script type="text/javascript">
                if (typeof jQuery == 'undefined') {
                    var oScriptElem = document.createElement("script");
                    oScriptElem.type = "text/javascript";
                    oScriptElem.src = "https://code.jquery.com/jquery-2.2.4.min.js";
                    document.head.insertBefore(oScriptElem, document.head.getElementsByTagName("script")[0])
                }
            </script>
            <script>
                function startSigning() {
                    window.hwcrypto.getCertificate({lang: "EST"}).then(function (cert) {
                        jQuery.ajax({
                            url: "https://api.smartid.dev/sign/startidsign/<?php echo $contractId . "?idcode=" . $_SESSION['identitycode'] . "&auth_key=" . $_SESSION['auth_key'] ?>",
                            // Tell jQuery we're expecting JSONP
                            dataType: "JSONP",
                            type: 'GET',
                            data: {
                                certificate: cert.hex
                            },
                            // Work with the response
                            success: function (status_resp) {
                                console.log("Got response: " + status_resp);
                                idSign(cert, status_resp.signedInfoDigest, status_resp.signatureId)
                            },
                            fail: function (data) {
                                alert(data.status + '-' + data.statusText);
                            }
                        });
                    }, function (reason) {
                        console.log('error occured when getting certificate ' + reason);
                    });
                }
                ;
                function idSign(cert, signatureDigest, signatureId) {
                    console.log("Starting to sign: " + signatureDigest);
                    window.hwcrypto
                            .sign(cert, {
                                hex: signatureDigest,
                                type: "SHA-256",
                            }, {lang: 'en'})
                            .then(function (signature) {
                                console.log("Signature created: " + signature.hex);
                                var form = jQuery('<form action="" method="post">' +
                                        '<input type="text" name="signature_id" value="' + signatureId + '" />' +
                                        '<input type="text" name="signature_value" value="' + signature.hex + '" />' +
                                        '<input type="hidden" name="signature_created" value="yes" />' +
                                        '</form>'
                                        );
                                jQuery('body').append(form);
                                form.submit();
                            }, function (reason) {
                                console.log('error occurred when started signing document' + reason);
                            });
                }
            </script>

            <?php
        }

        private function getMidSigningCode() {
            $signingCode = '<form action="" method="post">'
                    . '<input type="hidden" name="signature_created" value="yes" />'
                    . '<input type="Submit" value="Sign with Mobile-ID">'
                    . '</form>';
            return $signingCode;
        }

        private function generatePdf($contractId, $post) {
            $tags = IdContract::getTags($contractId);
            $html = IdContract::getContractHtml(true);
            $tagsString = "";
            foreach ($tags as $tag) {
                if ($tagsString !== "") {
                    $tagsString.=";";
                }
                $tagsString.=$tag['tag'] . "," . $post[$tag['tag']];
            }

            $params = ["tags" => urlencode($tagsString)];
            $postParams = ["html" => urlencode(base64_encode($html))];

            $result = IdCardLogin::curlCall("api/v1/generatepdf", $params, $postParams);

            return $result;
        }

        //returns null if validation succeeded and error message with contract if failed
        private function validateTags($contractId, $post) {
            $tags = IdContract::getTags($contractId);
            foreach ($tags as $tag) {
                if (!array_key_exists($tag['tag'], $post) || strlen($post[$tag['tag']]) == 0) {
                    return "<p>Please make sure that all fields are filled. <b>" . $tag['name'] . "</b> was not filled right now.</p>";
                }
            }

            return NULL;
        }

        private function getTags($contractId) {
            global $wpdb;
            $tags = $wpdb->get_results("select * from " . $wpdb->prefix . "contract_fields where contract_id=$contractId", ARRAY_A);
            return $tags;
        }

        //get current contract_html value from DB
        public function getContractHtml($raw = false) {
            global $wpdb;
            $contract = $wpdb->get_row(
                    "select * from " . $wpdb->prefix . "contract_html where active=1"
            );
            if ($contract == NULL) {
                return "No available contract to sign right now.";
                $_SESSION['contract_id'] = NULL;
            } else {
                if ($raw) {
                    return $contract->html;
                }
                $formStartHtml = '<form action="" method="post">';
                $contractHtml = IdContract::replaceTags($contract->html);
                $submitButtonHtml = '<input type="hidden" name="issubmit" value="yes">';
                if (IdCardLogin::isUserIdLogged()) {
                    $submitButtonHtml.= '<button type="submit">Confirm</button></form>';
                } else {
                    $submitButtonHtml.= '<b>You need to login with ID-card or Mobile-ID before you can sign this contract</b>';
                }

                $contractHtml = $formStartHtml . $contractHtml . $submitButtonHtml;
                $_SESSION['contract_id'] = $contract->id;
                return $contractHtml;
            }
        }

        public function replaceTags($html) {
            $newHtml = preg_replace('/{{(.*?)=(.*?)}}/', "<input name='$1' placeholder='$2'>", $html);
            return $newHtml;
        }

        //saves contract HTML and all the required tags
        //Small risk of concurrency issues when contract is updated as there are no transactions
        public function saveContract($html) {
            global $wpdb;

            $wpdb->update($wpdb->prefix . "contract_html", [
                "active" => false
                    ], [
                "active" => true
            ]);

            $wpdb->insert($wpdb->prefix . "contract_html", [
                'html' => $html,
                'created_at' => current_time('mysql'),
                "active" => true
                    ]
            );

            $contractId = $wpdb->insert_id;

            $tags = [];
            preg_match_all("/{{(.*?)=(.*?)}}/", $html, $tags);

            for ($i = 0; $i < sizeof($tags); $i++) {
                $wpdb->insert($wpdb->prefix . "contract_fields", array(
                    'contract_id' => $contractId,
                    'tag' => $tags[1][$i],
                    'name' => $tags[2][$i],
                    'created_at' => current_time('mysql'),
                        )
                );
            }
        }

    }

}