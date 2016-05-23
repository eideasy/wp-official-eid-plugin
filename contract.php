<?php

if (!class_exists("IdContract")) {

    class IdContract {

        public function showContract() {
            return IdContract::getContractHtml();
        }

        //get current contract_html value from DB
        public function getContractHtml() {

            global $wpdb;
            $contract = $wpdb->get_row(
                    "select * from " . $wpdb->prefix . "contract_html where active=1"
            );
            if ($contract == NULL) {
                return "No contract to sign available right now";
                $_SESSION['contract_id'] = NULL;
            } else {
                return IdContract::replaceTags($contract->html);
                $_SESSION['contract_id'] = $contract->id;
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