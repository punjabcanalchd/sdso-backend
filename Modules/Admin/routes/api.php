<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;
use Modules\Admin\Http\Controllers\DashboardController;
use Modules\Admin\Http\Controllers\MasterManagement\CircleController;
use Modules\Admin\Http\Controllers\MasterManagement\DesignationController;
use Modules\Admin\Http\Controllers\MasterManagement\DistrictController;
use Modules\Admin\Http\Controllers\MasterManagement\DivisionController;
use Modules\Admin\Http\Controllers\MasterManagement\OfficeController;
use Modules\Admin\Http\Controllers\MasterManagement\OfficeHierarchyController;
use Modules\Admin\Http\Controllers\MasterManagement\StateController;
use Modules\Admin\Http\Controllers\MasterManagement\SubDivisionController;
use Modules\Admin\Http\Controllers\MenuController;
use Modules\Admin\Http\Controllers\Others\PageController;
use Modules\Admin\Http\Controllers\PermissionController;
use Modules\Admin\Http\Controllers\RoleController;
use Modules\Admin\Http\Controllers\UserManagement\UserController;
use Modules\Admin\Http\Controllers\UserRoleController;

// Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
//     Route::apiResource('admins', AdminController::class)->names('admin');
// });

// will be used in future after spatie integration
// Route::prefix('admin')->group(function () {
//     Route::get('/dashboard', [DashboardController::class, 'index']);
//     Route::prefix('users')->group(function () {
//         Route::get('/', [UserController::class,'index'])->middleware('permission:users.read');
//         Route::get('/{id}', [UserController::class,'show'])->middleware('permission:users.read');
//         Route::post('/', [UserController::class,'store'])->middleware('permission:users.create');
//         Route::post('/{id}/update', [UserController::class,'update'])->middleware('permission:users.update');
//         Route::post('/{id}/delete', [UserController::class,'destroy'])->middleware('permission:users.destroy');
//     });
// });

Route::middleware('auth:api')->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.get_dashboard_data');
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('admin.get_all_users');
        Route::get('/{public_id}', [UserController::class, 'show'])->name('admin.get_user_details');
        Route::post('/', [UserController::class, 'store'])->name('admin.create_new_user');
        Route::post('/{public_id}/update', [UserController::class, 'update'])->name('admin.update_user_details');
        Route::post('/{public_id}/updateStatus', [UserController::class, 'updateStatus'])->name('admin.update_user_status');
        Route::post('/{public_id}/delete', [UserController::class, 'destroy'])->name('admin.delete_user');
    });
    Route::get('roles', [RoleController::class, 'index'])->name('admin.get_all_roles');
    Route::post('roles', [RoleController::class, 'store'])->name('admin.create_new_role');
    Route::post('roles/{public_id}/update', [RoleController::class, 'update'])->name('admin.update_role');
    Route::post('roles/{public_id}/delete', [RoleController::class, 'destroy'])->name('admin.delete_role');
    Route::get('permissions', [PermissionController::class, 'index'])->name('admin.get_all_permissions');
    Route::get('permissions/tree', [PermissionController::class, 'getTree'])->name('admin.get_permissions_tree');
    Route::get('roles/{public_id}/permissions/tree', [PermissionController::class, 'getRoleTree'])->name('admin.get_role_permissions_tree');
    Route::post('roles/{public_id}/permissions', [PermissionController::class, 'syncRolePermissions'])->name('admin.sync_role_permissions');
    Route::get('permissions/allowed-actions/{slug}', [PermissionController::class, 'allowedActions'])->name('admin.allowed_actions');
    Route::post('users/{public_id}/role', [UserRoleController::class, 'assignRoleToUser'])->name('admin.assign_role_to_user');
    Route::get('users/{public_id}/role', [UserRoleController::class, 'getUserRoleAndPermissions'])->name('admin.get_user_role_and_permissions');

    Route::prefix('pages')->group(function () {
        Route::get('/', [PageController::class, 'index'])->name('admin.pages');
        Route::get('/{public_id}', [PageController::class, 'show'])->name('admin.get_page_by_public_id');
        Route::post('/', [PageController::class, 'store'])->name('admin.create_new_page');
        Route::post('/{public_id}/update', [PageController::class, 'update'])->name('admin.update_page_details');
        Route::post('/{public_id}/delete', [PageController::class, 'destroy'])->name('admin.delete_page');
    });

    Route::prefix('states')->group(function () {
        Route::get('/', [StateController::class, 'index'])->name('admin.states');
        Route::get('/all', [StateController::class, 'getAllStates'])->name('admin.all_states');
        Route::get('/{public_id}', [StateController::class, 'show'])->name('admin.get_state_by_public_id');
        Route::post('/', [StateController::class, 'store'])->name('admin.create_new_state');
        Route::post('/{public_id}/update', [StateController::class, 'update'])->name('admin.update_state_details');
    });

    Route::prefix('districts')->group(function () {
        Route::get('/', [DistrictController::class, 'index'])->name('admin.districts');
        Route::get('/all', [DistrictController::class, 'getAllStates'])->name('admin.get_all_districts');
        Route::get('/{public_id}', [DistrictController::class, 'show'])->name('admin.get_district_by_public_id');
        Route::post('/', [DistrictController::class, 'store'])->name('admin.create_new_district');
        Route::post('/{public_id}/update', [DistrictController::class, 'update'])->name('admin.update_district_details');
    });

    Route::prefix('office_hierarchy')->group(function () {
        Route::get('/', [OfficeHierarchyController::class, 'index'])->name('admin.office_hierarchy ');
        Route::get('/all', [OfficeHierarchyController::class, 'getAllOfficeLevels'])->name('admin.get_all_office_levels');
        Route::get('/{public_id}', [OfficeHierarchyController::class, 'show'])->name('admin.get_office_hierarchy_by_public_id');
        Route::post('/', [OfficeHierarchyController::class, 'store'])->name('admin.create_new_office_hierarchy');
        Route::post('/{public_id}/update', [OfficeHierarchyController::class, 'update'])->name('admin.update_office_hierarchy_details');
    });

    Route::prefix('designation')->group(function () {
        Route::get('/', [DesignationController::class, 'index'])->name('admin.designation ');
        Route::get('/{public_id}', [DesignationController::class, 'show'])->name('admin.get_designation_by_public_id');
        Route::post('/', [DesignationController::class, 'store'])->name('admin.create_new_designation');
        Route::post('/{public_id}/update', [DesignationController::class, 'update'])->name('admin.update_designation_details');
    });

    Route::prefix('circles')->group(function () {
        Route::get('/', [CircleController::class, 'index'])->name('admin.circle ');
        Route::get('/all', [CircleController::class, 'getAllCircles'])->name('admin.get_all_circles');
        Route::get('/{public_id}', [CircleController::class, 'show'])->name('admin.get_circle_by_public_id');
        Route::post('/', [CircleController::class, 'store'])->name('admin.create_new_circle');
        Route::post('/{public_id}/update', [CircleController::class, 'update'])->name('admin.update_circle_details');
    });

    Route::prefix('divisions')->group(function () {
        Route::get('/', [DivisionController::class, 'index'])->name('admin.divisions ');
        Route::get('/all', [DivisionController::class, 'getAllDivisions'])->name('admin.get_all_divisions');
        Route::get('/{public_id}', [DivisionController::class, 'show'])->name('admin.get_division_by_public_id');
        Route::post('/', [DivisionController::class, 'store'])->name('admin.create_new_division');
        Route::post('/{public_id}/update', [DivisionController::class, 'update'])->name('admin.update_division_details');
        Route::get('/{public_id}/getdivisions', [DivisionController::class, 'getDivisionsByCircle'])->name('admin.get_divisions_by_circle');
    });

    Route::prefix('subdivisions')->group(function () {
        Route::get('/', [SubDivisionController::class, 'index'])->name('admin.subdivisions ');
        Route::get('/all', [SubDivisionController::class, 'getAllDivisions'])->name('admin.get_all_subdivisions');
        Route::get('/{public_id}', [SubDivisionController::class, 'show'])->name('admin.get_subdivision_by_public_id');
        Route::post('/', [SubDivisionController::class, 'store'])->name('admin.create_new_subdivision');
        Route::post('/{public_id}/update', [SubDivisionController::class, 'update'])->name('admin.update_subdivision_details');
        Route::get('/{public_id}/getsubdivisions', [SubDivisionController::class, 'getSubdivisionsByDivision'])->name('admin.get_subdivisions_by_division');
    });

    Route::prefix('offices')->group(function () {
        Route::get('/', [OfficeController::class, 'index'])->name('admin.offices ');
        Route::get('/{public_id}', [OfficeController::class, 'show'])->name('admin.get_office_by_public_id');
    });

    // Menu routes
    Route::prefix('menus')->group(function () {
        Route::get('/sidebar', [MenuController::class, 'sidebar'])->name('admin.get_sidebar_menu');
        Route::get('/', [MenuController::class, 'index'])->name('admin.get_all_menus');
        Route::get('/dropdown', [MenuController::class, 'dropdown'])->name('admin.get_menus_dropdown');
        Route::post('/', [MenuController::class, 'store'])->name('admin.create_new_menu');
        Route::get('/{public_id}', [MenuController::class, 'show'])->name('admin.get_menu_details');
        Route::post('/{public_id}/update', [MenuController::class, 'update'])->name('admin.update_menu_details');
        Route::post('/{public_id}/status', [MenuController::class, 'updateStatus'])->name('admin.update_menu_status');
        Route::post('/{public_id}/delete', [MenuController::class, 'destroy'])->name('admin.delete_menu');
    });
});
