<?php

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
            //First view nothing happens yet                  
            if (array_key_exists('issubmit', $_POST) && $_POST['issubmit'] == "yes") {
                $contractId = $_SESSION['contract_id'];
                $validateResult = IdContract::validateTags($contractId, $_POST);
                return $validateResult . IdContract::getContractHtml();
            } else {
                return NULL;
            }
        }

        //returns null if validation succeeded and error message with contract if failed
        private function validateTags($contractId, $post) {
            global $wpdb;
            $tags = $wpdb->get_results("select * from " . $wpdb->prefix . "contract_fields where contract_id=$contractId", ARRAY_A);            
            foreach ($tags as $tag) {
                if (!array_key_exists($tag['tag'], $post) || strlen($post[$tag['tag']])==0) {
                    return "<p>Please make sure that all fields are filled. <b>" . $tag['name'] . "</b> was not filled right now.</p>";
                }
            }
            return NULL;
        }

        //get current contract_html value from DB
        public function getContractHtml() {

            global $wpdb;
            $contract = $wpdb->get_row(
                    "select * from " . $wpdb->prefix . "contract_html where active=1"
            );
            if ($contract == NULL) {
                return "No available contract to sign right now.";
                $_SESSION['contract_id'] = NULL;
            } else {
                $formStartHtml = '<form action="" method="post">';
                $contractHtml = IdContract::replaceTags($contract->html);
                $submitButtonHtml = '<input type="hidden" name="issubmit" value="yes">' .
                        '<button type="submit">Confirm</button></form>';
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