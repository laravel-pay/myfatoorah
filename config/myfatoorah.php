<?php

return [
    /**
     * API Token Key
     * Live Token: https://myfatoorah.readme.io/docs/live-token
     * Test Token: https://myfatoorah.readme.io/docs/test-token
     */
    'api_key' => env('MYFATOORAH_API_KEY'),
    /**
     * Test Mode
     * Accepted value: 'true' for the test mode or 'false' for the live mode
     */
    'test_mode' => env('MYFATOORAH_TEST_MODE', true),
    /**
     * Country ISO Code
     * Accepted value: KWT, SAU, ARE, QAT, BHR, OMN, JOD, or EGY.
     */
    'country_iso' => env('MYFATOORAH_COUNTRY_ISO', 'KWT'),

    'enable_routes' => true,
];
