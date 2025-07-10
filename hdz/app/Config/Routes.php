<?php namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override('App\Controllers\Kb::error404');
$routes->setAutoRoute(true);

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->add('maintenance-mode', 'Pages::maintenance',[
    'as' => 'maintenance'
]);
$routes->add('/', 'Kb::home', [
    'as' => 'home',
    'filter' => 'userAuth'
]);
$routes->add('category/(:num)-(:any)', 'Kb::category/$1', [
    'as' => 'category',
    'filter' => 'userAuth'
]);
$routes->add('article/(:num)-(:any)', 'Kb::article/$1', [
    'as' => 'article',
    'filter' => 'userAuth'
]);
$routes->add('search', 'Kb::search',[
    'as' => 'search',
    'filter' => 'userAuth'
]);
$routes->add('download/article-(:num)-(:num)', 'Kb::download/$1/$2',[
    'as' => 'download_article',
    'filter' => 'userAuth'
]);
$routes->add('submit-ticket', 'Ticket::selectDepartment',[
    'as' => 'submit_ticket',
    'filter' => 'userAuth'
]);
$routes->add('ajax/agents/(:num)', 'Ticket::getAgentsByDepartment/$1',[
    'as' => 'client_ajax_agents',
    'filter' => 'userAuth'
]);
$routes->add('submit-ticket/(:num)-(:any)', 'Ticket::create/$1',[
    'as' => 'submit_ticket_department',
    'filter' => 'userAuth'
]);
$routes->add('submit-ticket/confirmation/(:num)/(:any)', 'Ticket::confirmedTicket/$1/$2',[
    'as' => 'ticket_preview',
    'filter' => 'userAuth'
]);
#Guest
$routes->add('login','UserAuth::login',[
    'as' => 'login',
    'filter' => 'userAuth:visitor'
]);
$routes->add('forgot-password','UserAuth::forgot',[
    'as' => 'forgot_password',
    'filter' => 'userAuth:visitor'
]);
#User
$routes->add('tickets','Ticket::clientTickets',[
    'as' => 'view_tickets',
    'filter' => 'userAuth:user'
]);
$routes->add('tickets/(:num)','Ticket::clientTickets',[
    'filter' => 'userAuth:user'
]);
$routes->add('tickets/show/(:num)', 'Ticket::clientShow/$1',[
    'as' => 'show_ticket',
    'filter' => 'userAuth:user'
]);
$routes->add('account/profile', 'UserAuth::profile',[
    'as' => 'profile',
    'filter' => 'userAuth:user'
]);
$routes->add('account/logout', 'UserAuth::logout',[
    'as' => 'logout',
    'filter' => 'userAuth:user'
]);

#Imap
$routes->add('imap_fetcher', 'MailFetcher::imap');
#Pipe
$routes->add('mail_pipe', 'MailFetcher::pipe');

#Install
$routes->add('install', 'Install::home',[
    'as' => 'install_home'
]);
$routes->add('install/install-wizard', 'Install::install',[
    'as' => 'install_wizard'
]);
$routes->add('install/install-wizard/success', 'Install::installComplete',[
    'as' => 'install_complete'
]);
$routes->add('install/upgrade-wizard', 'Install::upgrade',[
    'as' => 'upgrade_wizard'
]);
#Staff
$routes->group(Helpdesk::STAFF_URI, [
    'filter' => 'staffAuth'
], function($routes){
    $routes->add('login','Staff\Auth::login',[
        'filter' => 'staffAuth:login',
        'as' => 'staff_login'
    ]);
    $routes->add('/', function () {
        return redirect()->route('staff_dashboard_admin');
    }, [
        'as' => 'staff_dashboard'
    ]);
    $routes->add('tickets', 'Staff\Tickets::manage/main',[
        'as' => 'staff_tickets'
    ]);
    $routes->add('tickets/overdue', 'Staff\Tickets::manage/overdue',[
        'as' => 'staff_tickets_overdue'
    ]);
    $routes->add('tickets/answered', 'Staff\Tickets::manage/answered',[
        'as' => 'staff_tickets_answered'
    ]);
    $routes->add('tickets/closed', 'Staff\Tickets::manage/closed',[
        'as' => 'staff_tickets_closed'
    ]);
    $routes->add('tickets/search', 'Staff\Tickets::manage/search',[
        'as' => 'staff_tickets_search'
    ]);
    $routes->add('tickets/view/(:num)', 'Staff\Tickets::view/$1',[
        'as' => 'staff_ticket_view'
    ]);
    $routes->add('tickets/create', 'Staff\Tickets::create',[
        'as' => 'staff_ticket_new'
    ]);
    $routes->add('tickets/kb','Staff\Misc::getKB',[
        'as' => 'staff_ajax_kb'
    ]);
    $routes->add('ajax/agents/(:num)','Staff\Misc::getAgentsByDepartment/$1',[
        'as' => 'staff_ajax_agents'
    ]);
    $routes->add('upload/editor', 'Staff\Misc::uploadEditor',[
        'as' => 'staff_editor_uploader',
    ]);
    $routes->add('canned-responses', 'Staff\Tickets::cannedResponses',[
        'as' => 'staff_canned',
    ]);
    $routes->add('canned-responses/edit/(:num)', 'Staff\Tickets::editCannedResponses/$1',[
        'as' => 'staff_canned_edit',
    ]);
    $routes->add('canned-responses/new', 'Staff\Tickets::newCannedResponse',[
        'as' => 'staff_new_canned'
    ]);
    $routes->add('kb/categories', 'Staff\Kb::categories',[
        'as' => 'staff_kb_categories'
    ]);
    $routes->add('kb/category/new', 'Staff\Kb::newCategory',[
        'as' => 'staff_kb_new_category'
    ]);
    $routes->add('kb/category/edit/(:num)', 'Staff\Kb::editCategory/$1',[
        'as' => 'staff_kb_edit_category'
    ]);
    $routes->add('kb/articles', 'Staff\Kb::articles',[
        'as' => 'staff_kb_articles'
    ]);
    $routes->add('kb/articles/category/(:num)', 'Staff\Kb::articles/$1');
    $routes->add('kb/article/new', 'Staff\Kb::newArticle',[
        'as' => 'staff_kb_new_article'
    ]);
    $routes->add('kb/article/edit/(:num)','Staff\Kb::editArticle/$1',[
        'as' => 'staff_kb_edit_article'
    ]);
    $routes->add('departments','Staff\Departments::manage',[
        'as' => 'staff_departments'
    ]);
    $routes->add('departments/edit/(:num)', 'Staff\Departments::edit/$1', [
        'as' => 'staff_department_id'
    ]);
    $routes->add('departments/new', 'Staff\Departments::create',[
       'as' => 'staff_department_new',
    ]);
    $routes->add('agents','Staff\Agents::manage',[
       'as' => 'staff_agents'
    ]);
    $routes->add('agents/edit/(:num)','Staff\Agents::edit/$1',[
        'as' => 'staff_agents_edit'
    ]);
    $routes->add('agents/new','Staff\Agents::create',[
        'as' => 'staff_agents_new'
    ]);
    $routes->add('users','Staff\Users::manage',[
        'as' => 'staff_users'
    ]);
    $routes->add('users/new','Staff\Users::newAccount',[
        'as' => 'staff_users_new'
    ]);
    $routes->add('users/import','Staff\Users::importUsers',[
        'as' => 'staff_users_import'
    ]);
    $routes->add('users/download-template','Staff\Users::downloadTemplate',[
        'as' => 'staff_users_template'
    ]);
    $routes->add('users/edit/(:num)','Staff\Users::editAccount/$1',[
        'as' => 'staff_users_edit'
    ]);
    $routes->add('setup/general', 'Staff\Settings::general',[
        'as' => 'staff_general_settings'
    ]);
    $routes->add('setup/security', 'Staff\Settings::security',[
        'as' => 'staff_security_settings'
    ]);
    $routes->add('setup/auto-assignment', 'Staff\AutoAssignmentController::index', [
        'as' => 'staff_auto_assignment'
    ]);
    $routes->add('setup/auto-assignment/update', 'Staff\AutoAssignmentController::updateSettings', [
        'as' => 'staff_auto_assignment_update'
    ]);
    $routes->add('setup/auto-assignment/staff-departments', 'Staff\AutoAssignmentController::staffDepartments', [
        'as' => 'staff_auto_assignment_staff'
    ]);
    $routes->add('setup/auto-assignment/department-stats/(:num)', 'Staff\AutoAssignmentController::departmentStats/$1', [
        'as' => 'staff_auto_assignment_stats'
    ]);
    $routes->add('setup/auto-assignment/reset-counters', 'Staff\AutoAssignmentController::resetCounters', [
        'as' => 'staff_auto_assignment_reset'
    ]);
    $routes->add('setup/auto-assignment/run-migration', 'Staff\AutoAssignmentController::runMigration', [
        'as' => 'staff_auto_assignment_migration'
    ]);
    $routes->add('account/log-out','Staff\Auth::logout',[
        'as' => 'staff_logout'
    ]);
    $routes->add('account/profile','Staff\Auth::profile',[
        'as' => 'staff_profile'
    ]);
    $routes->add('tools/custom-fields','Staff\Tools::customFields',[
        'as' => 'staff_custom_fields'
    ]);
    $routes->add('tools/custom-fields/new','Staff\Tools::customFieldsCreate',[
        'as' => 'staff_new_custom_field'
    ]);
    $routes->add('tools/custom-fields/edit/(:num)', 'Staff\Tools::customFieldsEdit/$1',[
        'as' => 'staff_edit_custom_field'
    ]);
    $routes->add('dashboard', 'Staff\Dashboard::index', [
        'as' => 'staff_dashboard_admin'
    ]);
});

//API
$routes->presenter('api/users', ['controller' => 'Api\Users','filter'=>'apiAuth']);
$routes->presenter('api/departments', ['controller' => 'Api\Departments','filter'=>'apiAuth']);
$routes->presenter('api/tickets', ['controller' => 'Api\Tickets', 'filter'=>'apiAuth']);
$routes->presenter('api/messages', ['controller' => 'Api\Messages','filter'=>'apiAuth']);
$routes->presenter('api/attachments', ['controller' => 'Api\Attachments','filter'=>'apiAuth']);
$routes->presenter('api/staff', ['controller' => 'Api\Staff','filter'=>'apiAuth']);
$routes->post('api/staff/auth', 'Api\Staff::auth',['filter' => 'apiAuth']);
/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
