<?php

function eideasyOptions() {
    return [
        'methods' => [
            [
                'inputName' => 'ee-id-card_enabled',
                'optionName' => 'smartid_idcard_enabled',
                'label' => 'Estonian ID-card',
            ],
            [
                'inputName' => 'ee-mobile-id_enabled',
                'optionName' => 'smartid_mobileid_enabled',
                'label' => 'Estonian Mobile-ID',
            ],
            [
                'inputName' => 'lv-id-card_enabled',
                'optionName' => 'lveid_enabled',
                'label' => 'Latvian ID-card',
            ],
            [
                'inputName' => 'eparaksts-mobile_enabled',
                'optionName' => 'eideasy-eparaksts-mobile_enabled',
                'label' => 'Latvian eParaksts Mobile',
            ],
            [
                'inputName' => 'lt-id-card_enabled',
                'optionName' => 'smartid_lt-id-card_enabled',
                'label' => 'Lithuanian ID-card',
            ],
            [
                'inputName' => 'lt-mobile-id_enabled',
                'optionName' => 'smartid_lt-mobile-id_enabled',
                'label' => 'Lithuanian mobile ID',
            ],
            [
                'inputName' => 'be-id-card_enabled',
                'optionName' => 'smartid_be-id-card_enabled',
                'label' => 'Belgium ID-card',
            ],
            [
                'inputName' => 'pt-id-card_enabled',
                'optionName' => 'smartid_pt-id-card_enabled',
                'label' => 'Portugal ID-card',
            ],
            [
                'inputName' => 'smart-id_enabled',
                'optionName' => 'smartid_smartid_enabled',
                'label' => ' Smart-ID',
            ],
        ]
    ];
}
