<?php

return [
    ['GET',  '/',                              'AuthController@showLogin',        []],
    ['GET',  '/login',                         'AuthController@showLogin',        []],
    ['POST', '/login',                         'AuthController@login',            []],
    ['POST', '/logout',                        'AuthController@logout',           ['admin','staff','viewer']],

    ['GET',  '/dashboard',                     'DashboardController@index',       ['admin','staff','viewer']],

    ['GET',  '/sales',                         'SalesController@index',           ['admin','staff','viewer']],
    ['GET',  '/sales/create',                  'SalesController@create',          ['admin','staff']],
    ['GET',  '/sales/exchange',                'SalesController@exchangeForm',    ['admin','staff']],
    ['POST', '/sales/exchange',                'SalesController@exchangeStore',   ['admin','staff']],
    ['POST', '/sales',                         'SalesController@store',           ['admin','staff']],
    ['GET',  '/sales/{id}',                    'SalesController@show',            ['admin','staff','viewer']],
    ['GET',  '/sales/{id}/receipt',            'SalesController@receipt',         ['admin','staff','viewer']],
    ['POST', '/sales/{id}/cancel',             'SalesController@cancel',          ['admin']],

    ['GET',  '/stock-ins',                     'StockInController@index',         ['admin','staff','viewer']],
    ['GET',  '/stock-ins/create',              'StockInController@create',        ['admin','staff:stockin']],
    ['POST', '/stock-ins',                     'StockInController@store',         ['admin','staff:stockin']],
    ['GET',  '/stock-ins/override',            'StockInController@overrideForm',  ['admin','staff:stockin']],
    ['POST', '/stock-ins/override',            'StockInController@overrideStore', ['admin','staff:stockin']],
    ['GET',  '/stock-ins/{id}',                'StockInController@show',          ['admin','staff','viewer']],
    ['POST', '/stock-ins/{id}/return',         'StockInController@processReturn', ['admin','staff:stockin']],
    ['POST', '/stock-ins/{id}/cancel',         'StockInController@cancel',        ['admin']],

    ['GET',  '/stock-returns',                 'StockReturnController@index',     ['admin','staff','viewer']],
    ['GET',  '/stock-returns/create',          'StockReturnController@create',    ['admin','staff:stockin']],
    ['POST', '/stock-returns',                 'StockReturnController@store',     ['admin','staff:stockin']],
    ['GET',  '/stock-returns/{id}',            'StockReturnController@show',      ['admin','staff','viewer']],
    ['GET',  '/stock-returns/{id}/print',      'StockReturnController@printSlip', ['admin','staff','viewer']],

    ['GET',  '/inventory',                     'InventoryController@index',       ['admin','staff','viewer']],
    ['GET',  '/inventory/movements',            'InventoryController@movements',   ['admin','staff','viewer']],
    ['GET',  '/inventory/{variantId}/movements','InventoryController@movements',   ['admin','staff','viewer']],

    ['GET',  '/catalog',                       'CatalogController@index',         ['admin','staff','viewer']],
    ['POST', '/catalog/variants',              'CatalogController@storeVariant',  ['admin']],
    ['POST', '/catalog/variants/{id}',         'CatalogController@updateVariant', ['admin']],
    ['POST', '/catalog/variants/{id}/toggle',  'CatalogController@toggleVariant', ['admin']],
    ['POST', '/catalog/prices/{variantId}',    'CatalogController@updatePrice',   ['admin']],

    ['GET',  '/reports',                       'ReportController@index',         ['admin','staff','viewer']],
    ['GET',  '/reports/print',                 'ReportController@print',         ['admin','staff','viewer']],
    ['GET',  '/reports/deliveries',            'ReportController@deliveries',    ['admin','staff','viewer']],
    ['GET',  '/reports/print_deliveries',      'ReportController@printDeliveries',['admin','staff','viewer']],
    ['GET',  '/reports/comprehensive/print',   'ReportController@printComprehensive', ['admin','staff','viewer']],
    ['GET',  '/reports/utang/print',           'ReportController@utang',              ['admin','staff','viewer']],
    ['GET',  '/reports/{type}/export',         'ReportController@export',             ['admin','staff','viewer']],
    ['POST', '/reports/coins',                 'ReportController@storeCoins',    ['admin','staff']],
    ['POST', '/reports/coins/{id}/delete',      'ReportController@deleteCoins',   ['admin','staff']],

    ['GET',  '/audit-logs',                    'AuditLogController@index',       ['admin']],

    ['GET',  '/expenses',                      'ExpenseController@index',        ['admin','staff']],
    ['POST', '/expenses',                      'ExpenseController@store',        ['admin','staff']],
    ['POST', '/expenses/{id}/delete',          'ExpenseController@delete',       ['admin','staff']],

    ['GET',  '/users',                         'UserController@index',           ['admin']],
    ['POST', '/users',                         'UserController@store',           ['admin']],
    ['POST', '/users/{id}',                    'UserController@update',          ['admin']],
    ['POST', '/users/{id}/reset-password',     'UserController@resetPassword',   ['admin']],
    ['POST', '/users/{id}/toggle',             'UserController@toggle',          ['admin']],

    ['GET',  '/settings',                      'SettingsController@index',       ['admin']],
    ['POST', '/settings',                      'SettingsController@update',      ['admin']],

    // --- TBT MODULE ROUTES ---
    ['GET',  '/transactions/new',              'TransactionController@create',   ['admin','staff']],
    ['POST', '/transactions',                  'TransactionController@store',    ['admin','staff']],
    
    ['GET',  '/deliveries',                     'CustomerDeliveryController@index',   ['admin','staff','viewer']],
    ['GET',  '/deliveries/create',              'CustomerDeliveryController@create',  ['admin','staff']],
    ['POST', '/deliveries',                     'CustomerDeliveryController@store',   ['admin','staff']],
    ['GET',  '/deliveries/{id}',                'CustomerDeliveryController@show',    ['admin','staff','viewer']],
    ['GET',  '/deliveries/{id}/receipt',        'CustomerDeliveryController@receipt', ['admin','staff','viewer']],
    ['POST', '/deliveries/{id}/pay',            'CustomerDeliveryController@pay',     ['admin','staff']],
    
    ['GET',  '/tbt',                           'TbtController@index',            ['admin','staff','viewer']],
    ['POST', '/tbt/{id}/release',              'TbtController@release',          ['admin','staff']],

    // --- UTANG MODULE ROUTES ---
    ['GET',  '/utang',                         'UtangController@index',          ['admin','staff','viewer']],
    ['GET',  '/utang/print',                   'UtangController@print',          ['admin','staff','viewer']],
    ['POST', '/utang/{id}/pay',                'UtangController@pay',            ['admin','staff']],
    
    ['GET',  '/reports/daily-stock',           'ReportController@dailyStockSummary', ['admin','staff','viewer']],
];
