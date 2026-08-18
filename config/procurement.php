<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Quote Threshold
    |--------------------------------------------------------------------------
    | PRs with a total estimated value at or above this amount (ZMW) require
    | a minimum of 3 supplier quotes before a PO can be raised.
    */
    'quote_threshold' => env('PROCUREMENT_QUOTE_THRESHOLD', 10000),

    /*
    |--------------------------------------------------------------------------
    | Committee Evidence Threshold
    |--------------------------------------------------------------------------
    | If all received quotes exceed this value, evidence that the Procurement
    | Committee convened must be attached before proceeding.
    */
    'committee_threshold' => env('PROCUREMENT_COMMITTEE_THRESHOLD', 50000),

    /*
    |--------------------------------------------------------------------------
    | Minimum Quotes Required
    |--------------------------------------------------------------------------
    */
    'min_quotes_required' => 3,

];
