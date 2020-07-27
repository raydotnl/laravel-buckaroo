<?php

return [
    'buckaroo_website_key' => env('BUCKAROO_WEBSITE_KEY', ''),
    'buckaroo_secret_key' => env('BUCKAROO_SECRET_KEY', ''),
    'buckaroo_url' => env('BUCKAROO_URL', 'https://checkout.buckaroo.nl'),
    'buckaroo_push_url' => env('BUCKAROO_PUSH_URL', ''),
    'buckaroo_return_url' => env('BUCKAROO_RETURN_URL', ''),
    'buckaroo_return_cancel_url' => env('BUCKAROO_RETURN_CANCEL_URL', ''),
    'buckaroo_return_error_url' => env('BUCKAROO_RETURN_ERROR_URL', ''),
    'buckaroo_return_reject_url' => env('BUCKAROO_RETURN_REJECT_URL', ''),
];
