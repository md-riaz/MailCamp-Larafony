<?php

/**
 * Web Routes
 * Define all application routes
 */

return [
    // Authentication routes
    'GET /login' => 'AuthController@showLogin',
    'POST /login' => 'AuthController@login',
    'GET /register' => 'AuthController@showRegister',
    'POST /register' => 'AuthController@register',
    'GET /logout' => 'AuthController@logout',
    
    // Dashboard
    'GET /' => 'DashboardController@index',
    'GET /dashboard' => 'DashboardController@index',
    
    // SMTP Settings
    'GET /smtp-settings' => 'SmtpSettingController@index',
    'POST /smtp-settings' => 'SmtpSettingController@save',
    'POST /smtp-settings/test' => 'SmtpSettingController@test',
    
    // Templates
    'GET /templates' => 'TemplateController@index',
    'GET /templates/create' => 'TemplateController@create',
    'POST /templates' => 'TemplateController@store',
    'GET /templates/:id/edit' => 'TemplateController@edit',
    'POST /templates/:id' => 'TemplateController@update',
    'DELETE /templates/:id' => 'TemplateController@delete',
    
    // Campaigns
    'GET /campaigns' => 'CampaignController@index',
    'GET /campaigns/create' => 'CampaignController@create',
    'POST /campaigns' => 'CampaignController@store',
    'GET /campaigns/:id' => 'CampaignController@show',
    'POST /campaigns/:id/recipients' => 'CampaignController@importRecipients',
    'POST /campaigns/:id/launch' => 'CampaignController@launch',
    
    // Tracking
    'GET /track/open/:campaign_id/:recipient_id/:token' => 'TrackingController@trackOpen',
    'GET /track/click/:campaign_id/:recipient_id/:token' => 'TrackingController@trackClick',
    'GET /unsubscribe/:token' => 'TrackingController@unsubscribe',
];
