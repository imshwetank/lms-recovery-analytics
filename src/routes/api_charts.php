<?php

/**
 * Charts API Routes
 * Prefix: /api/charts
 */

return [
    'GET /data' => ['ChartController', 'getData'],
    'GET /branch/{id}' => ['ChartController', 'getBranchData'],
    'GET /type/{type}' => ['ChartController', 'getTypeData'],
    'POST /export' => ['ChartController', 'exportData'],
    'GET /summary' => ['ChartController', 'getSummary'],
    'GET /trends' => ['ChartController', 'getTrends'],
    'GET /comparison' => ['ChartController', 'getComparison']
];
