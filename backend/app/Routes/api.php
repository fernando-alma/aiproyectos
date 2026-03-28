<?php

use App\Backend\Controllers\AuthController;
use App\Backend\Controllers\DashboardController;
use App\Backend\Controllers\ProjectController;
use App\Backend\Controllers\TaskController;
use App\Backend\Controllers\MemberController;
use App\Backend\Controllers\AdminController; 
/**
 * Definición de Rutas de la API
 * 'ruta' => [Controlador, Método, Método HTTP, Protegido (bool)]
 */
return [
    // --- AUTENTICACIÓN (Públicas) ---
    'auth/register'         => ['controller' => AuthController::class, 'method' => 'register',       'httpMethod' => 'POST'],
    'auth/login'            => ['controller' => AuthController::class, 'method' => 'login',          'httpMethod' => 'POST'],
    'login'                 => ['controller' => AuthController::class, 'method' => 'login',          'httpMethod' => 'POST'], // Legacy compat

    // --- AUTENTICACIÓN (Protegidas) ---
    'auth/logout'           => ['controller' => AuthController::class, 'method' => 'logout',         'httpMethod' => 'POST', 'protected' => true],
    'auth/me'               => ['controller' => AuthController::class, 'method' => 'me',             'httpMethod' => 'GET',  'protected' => true],
    'auth/change-password'  => ['controller' => AuthController::class, 'method' => 'changePassword', 'httpMethod' => 'POST', 'protected' => true],

    // --- DASHBOARDS ---
    'dashboards'            => ['controller' => DashboardController::class, 'method' => 'getDashboards', 'httpMethod' => 'GET'], // Público
    'dashboard/get'         => ['controller' => DashboardController::class, 'method' => 'getDashboard',  'httpMethod' => 'GET'], // Público
    'dashboard/create'      => ['controller' => DashboardController::class, 'method' => 'create',        'httpMethod' => 'POST', 'protected' => true],
    'dashboard/update'      => ['controller' => DashboardController::class, 'method' => 'update',        'httpMethod' => 'POST', 'protected' => true],
    'dashboard/delete'      => ['controller' => DashboardController::class, 'method' => 'delete',        'httpMethod' => 'POST', 'protected' => true],

    // --- PROYECTOS ---
    'project/getProjects'         => ['controller' => ProjectController::class, 'method' => 'getProjects',      'httpMethod' => 'GET'], // Público
    'project/get'                 => ['controller' => ProjectController::class, 'method' => 'getProject',       'httpMethod' => 'GET'], // Público
    'project/create'              => ['controller' => ProjectController::class, 'method' => 'create',           'httpMethod' => 'POST', 'protected' => true],
    'project/update'              => ['controller' => ProjectController::class, 'method' => 'update',           'httpMethod' => 'POST', 'protected' => true],
    'project/delete'              => ['controller' => ProjectController::class, 'method' => 'delete',           'httpMethod' => 'POST', 'protected' => true],
    'project/stats'               => ['controller' => ProjectController::class, 'method' => 'getStats',         'httpMethod' => 'GET'],
    'project/files'               => ['controller' => ProjectController::class, 'method' => 'getFiles',         'httpMethod' => 'GET'],
    'project/members'             => ['controller' => ProjectController::class, 'method' => 'getMembers',       'httpMethod' => 'GET'],
    'project/tasks'               => ['controller' => ProjectController::class, 'method' => 'getTasks',         'httpMethod' => 'GET'],
    'project/activity'            => ['controller' => ProjectController::class, 'method' => 'getActivity',      'httpMethod' => 'GET'],
    'project/createProjectMember' => ['controller' => ProjectController::class, 'method' => 'createProjectsMember', 'httpMethod' => 'POST', 'protected' => true],
    'project/sendJoinRequest'     => ['controller' => ProjectController::class, 'method' => 'sendJoinRequest',  'httpMethod' => 'POST', 'protected' => true],
    'project/getJoinRequests'     => ['controller' => ProjectController::class, 'method' => 'getJoinRequests',  'httpMethod' => 'GET',  'protected' => true],
    'project/approveJoinRequest'  => ['controller' => ProjectController::class, 'method' => 'approveJoinRequest', 'httpMethod' => 'POST', 'protected' => true],
    'project/rejectJoinRequest'   => ['controller' => ProjectController::class, 'method' => 'rejectJoinRequest',  'httpMethod' => 'POST', 'protected' => true],
    'project/removeMember'        => ['controller' => ProjectController::class, 'method' => 'removeMember',       'httpMethod' => 'POST', 'protected' => true], // NUEVO: Para expulsar
    'project/getFollowedProjects' => ['controller' => ProjectController::class, 'method' => 'getFollowedProjects', 'httpMethod' => 'GET',  'protected' => true],

    // --- TAREAS (Todas Protegidas) ---
    'task/create'       => ['controller' => TaskController::class, 'method' => 'create',       'httpMethod' => 'POST', 'protected' => true],
    'task/getTasks'     => ['controller' => TaskController::class, 'method' => 'getTasks',     'httpMethod' => 'GET',  'protected' => true],
    'task/get'          => ['controller' => TaskController::class, 'method' => 'getTask',      'httpMethod' => 'GET',  'protected' => true],
    'task/update'       => ['controller' => TaskController::class, 'method' => 'update',       'httpMethod' => 'POST', 'protected' => true],
    'task/delete'       => ['controller' => TaskController::class, 'method' => 'delete',       'httpMethod' => 'POST', 'protected' => true],
    'task/updateStatus' => ['controller' => TaskController::class, 'method' => 'updateStatus', 'httpMethod' => 'POST', 'protected' => true],

    // --- MIEMBROS ---
    'member/getAll'       => ['controller' => MemberController::class, 'method' => 'getMembers',   'httpMethod' => 'GET'],
    'member/createMember' => ['controller' => MemberController::class, 'method' => 'createMember', 'httpMethod' => 'POST', 'protected' => true],
    'member/delete'       => ['controller' => MemberController::class, 'method' => 'delete',       'httpMethod' => 'POST', 'protected' => true],

    // ==========================================================
    // --- SUPER ADMIN --- (Nuevas Rutas para el Paso 5)
    // ==========================================================
    'admin/users'      => ['controller' => AdminController::class, 'method' => 'getUsers',   'httpMethod' => 'GET',  'protected' => true],
    'admin/changeRole' => ['controller' => AdminController::class, 'method' => 'changeRole', 'httpMethod' => 'POST', 'protected' => true],
    'admin/stats'      => ['controller' => AdminController::class, 'method' => 'getStats',   'httpMethod' => 'GET',  'protected' => true],
];