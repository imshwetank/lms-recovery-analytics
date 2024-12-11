<?php

/**
 * Charts View Routes
 * These routes handle the UI views for charts and analytics
 */

return [
    'GET /dashboard' => ['ChartController', 'showDashboard'],
    'GET /analytics' => ['ChartController', 'showAnalytics'],
    'GET /reports' => ['ChartController', 'showReports'],
    'GET /branch/{id}/details' => ['ChartController', 'showBranchDetails'],
    'GET /export' => ['ChartController', 'showExportOptions']
];
