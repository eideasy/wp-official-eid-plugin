<?php

function eideasyOptions() {
    return [
        'methods' => [
            [
                'inputName' => 'ee-id-card_enabled',
                'optionName' => 'smartid_idcard_enabled',
                'label' => 'Estonian ID-card',
                'actionType' => 'ee-id-card',
                'buttonId' => 'smartid-id-login',
                'filterName' => 'ee-id-card-login',
                'image' => '/img/eid_idkaart_mark.png',
            ],
            [
                'inputName' => 'ee-mobile-id_enabled',
                'optionName' => 'smartid_mobileid_enabled',
                'label' => 'Estonian Mobile-ID',
                'actionType' => 'ee-mobile-id',
                'buttonId' => 'smartid-mid-login',
                'filterName' => 'ee-mobile-id-login',
                'image' => '/img/eid_mobiilid_mark.png',
            ],
            [
                'inputName' => 'lv-id-card_enabled',
                'optionName' => 'lveid_enabled',
                'label' => 'Latvian ID-card',
                'actionType' => 'lv-id-card',
                'buttonId' => 'smartid-lveid-login',
                'filterName' => 'lv-id-card-login',
                'image' => '/img/latvia-id-card.png',
            ],
            [
                'inputName' => 'eparaksts-mobile_enabled',
                'optionName' => 'eideasy-eparaksts-mobile_enabled',
                'label' => 'Latvian eParaksts Mobile',
                'actionType' => 'lv-eparaksts-mobile-login',
                'buttonId' => 'eideasy-eparaksts-mobile-login',
                'filterName' => 'eideasy-eparaksts-mobile-login',
                'image' => '/img/eparaksts-mobile.png',
            ],
            [
                'inputName' => 'lt-id-card_enabled',
                'optionName' => 'smartid_lt-id-card_enabled',
                'label' => 'Lithuanian ID-card',
                'actionType' => 'lt-id-card',
                'buttonId' => 'smartid-lt-id-card-login',
                'filterName' => 'lt-id-card-login',
                'image' => '/img/lithuania_eid.png',
            ],
            [
                'inputName' => 'lt-mobile-id_enabled',
                'optionName' => 'smartid_lt-mobile-id_enabled',
                'label' => 'Lithuanian mobile ID',
                'actionType' => 'lt-mobile-id',
                'buttonId' => 'smartid-lt-mobile-id-login',
                'filterName' => 'lt-mobile-id-login',
                'image' => '/img/lt-mobile-id.png',
            ],
            [
                'inputName' => 'be-id-card_enabled',
                'optionName' => 'smartid_be-id-card_enabled',
                'label' => 'Belgium ID-card',
                'actionType' => 'be-id-card',
                'buttonId' => 'smartid-be-id-card-login',
                'filterName' => 'be-id-card-login',
                'image' => '/img/belgia-id-card.svg',
            ],
            [
                'inputName' => 'pt-id-card_enabled',
                'optionName' => 'smartid_pt-id-card_enabled',
                'label' => 'Portugal ID-card',
                'actionType' => 'pt-id-card',
                'buttonId' => 'smartid-pt-id-card-login',
                'filterName' => 'pt-id-card-login',
                'image' => '/img/portugal-id-card.png',
            ],
            [
                'inputName' => 'smart-id_enabled',
                'optionName' => 'smartid_smartid_enabled',
                'label' => ' Smart-ID',
                'actionType' => 'smart-id',
                'buttonId' => 'smartid-smartid-login',
                'filterName' => 'smart-id-login',
                'image' => '/img/Smart-ID_login_btn.png',
            ],
        ]
    ];
}
