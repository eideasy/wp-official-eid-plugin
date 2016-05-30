<?php

defined('ABSPATH') or die('No script kiddies please!');

require_once(ABSPATH . 'wp-load.php');

if (!class_exists("IdContract")) {

    class IdContract {

        public function showContract() {
            if (!session_id()) {
                session_start();
            }

            $newHtml = IdContract::submitForm();

            if ($newHtml == null) {
                return IdContract::getContractHtml();
            } else {
                return $newHtml;
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
                if (array_key_exists("error", $pdfLocation)) {
                    return "<b>Form submit failed because of: " . $pdfLocation['error'] . "</b><br>" . IdContract::getContractHtml();
                }
                return '<a href="https://idiotos.eu/storage/pdf/' . $pdfLocation['pdfUrl'] . '">Download PDF to be signed from here</a>';
            } else {
                return NULL;
            }
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

            $params = "site_secret=" . get_option("site_secret") .
                    "&tags=" . $tagsString .
                    "&site_url=" . urlencode(get_site_url()) .
                    "&auth_key=" . $_SESSION['auth_key'] .
                    "&idcode=" . $_SESSION['identitycode'];

            $ch = curl_init();
            $url = "https://idiotos.eu/api/v1/generatepdf?" . $params;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "html=" . urlencode(base64_encode($html)));

            $curlResult = curl_exec($ch);
            $result = json_decode($curlResult, true);
            curl_close($ch);
            
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