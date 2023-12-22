<?php
use App\Http\Resources\StaffResource;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CustomerTypeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\LawTextController;
use App\Http\Controllers\NotarizedDocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CostController;
use App\Http\Controllers\CostTypeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\EnvConfigController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\ReviewController;



Route::get('/documentCountsByCategory/{minDate}/{maxDate}', [StatisticController::class, 'getDocumentCountsByCategory']);
Route::get('/revenueByDateRange/{minDate}/{maxDate}', [StatisticController::class, 'getRevenueByDateRange']);
Route::get('/revenueByCategory/{minDate}/{maxDate}', [StatisticController::class, 'getRevenueByCategory']);
Route::get('/documentCountToday', [StatisticController::class, 'getDocumentCountToday']);


Route::post('/update-env-config', 'EnvConfigController@updateEnvConfig');





/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/admin', function (Request $request) {
    return response()->json(new StaffResource($request->user()));

});
Route::apiResource('customerTypes', CustomerTypeController::class);
Route::apiResource('customers', CustomerController::class);
Route::post('/login', [CustomerController::class, 'login']);


Route::post('/admin/login', [StaffController::class, 'login']);
// thêm, sửa vai trò cho nhân viên (roles[1,2,...])
Route::post('/staff/{idStaff}/roles', [StaffController::class, 'addRoleToStaff']);
Route::put('/staff/{idStaff}/roles', [StaffController::class, 'updateRoleToStaff']);
// Lấy nhân viên theo tên vai trò
Route::get('get-staff-with-permission/{idPer}', [StaffController::class, 'getStaffWithPermission']);

Route::apiResource('staffs', StaffController::class);
// get all permissions
Route::get('permissions', [RoleController::class, 'getPermissions']);
// thêm, sửa quyền cho vai trò (permissions[1,2,...])
Route::post('/roles/{idRole}/permissions', [RoleController::class, 'addPermissionToRole']);
Route::put('/roles/{idRole}/permissions', [RoleController::class, 'updatePermissionToRole']);
Route::apiResource('roles', RoleController::class);

Route::apiResource('categories', CategoryController::class);
Route::apiResource('forms', FormController::class);
Route::post('forms/{id}', [FormController::class, 'update']); 

Route::apiResource('lawTexts', LawTextController::class);
Route::post('lawTexts/{id}', [LawTextController::class, 'update']); 

Route::get('notarizedDocuments/status-{status}/user-{userId}', [NotarizedDocumentController::class, 'getNotarizedDocuments']);
Route::get('notarizedDocuments/user-{userId}', [NotarizedDocumentController::class, 'getDocumentsByUser']);
Route::get('notarizedDocuments/customer-{customerId}', [NotarizedDocumentController::class, 'getDocumentsByCustomer']);

Route::apiResource('notarizedDocuments', NotarizedDocumentController::class);
Route::post('notarizedDocuments/{id}', [NotarizedDocumentController::class, 'update']); 
Route::post('generateDocument/{id}', [NotarizedDocumentController::class, 'generateDocument']);
Route::put('/cancelDocument/{id}', [NotarizedDocumentController::class, 'cancelDocument']);
Route::put('/update-status/{id}/{status}', [NotarizedDocumentController::class, 'updateStatus']);


Route::get('check-customer/{customer_id}', [ReviewController::class, 'checkCustomerNotarizedDocument']); 
Route::apiResource('reviews', ReviewController::class); 
Route::apiResource('invoices', InvoiceController::class); 
Route::apiResource('costTypes', CostTypeController::class);
Route::apiResource('costs', CostController::class);
Route::apiResource('storages', StorageController::class);
Route::post('storages/{id}', [StorageController::class, 'update']); 
Route::get('myAppointments', [AppointmentController::class, 'getMyAppointments']); 
Route::apiResource('appointments', AppointmentController::class);


// Thông báo
Route::post('/sendNotification', [NotificationController::class, 'sendNotification']);
Route::post('/notification/updateStatus-{id}', [NotificationController::class, 'updateStatus']);
Route::get('/getNotification/user-{id}', [NotificationController::class, 'getNotifications']);




//address
Route::get('/cities', [AddressController::class, 'city']);
// lấy tất cả huyện thuộc tỉnh /id tỉnh
Route::get('/cities/{city_id}', [AddressController::class, 'district']); 
// lấy tất cả xã thuộc huyện /id huyện
Route::get('/districts/{district_id}', [AddressController::class, 'ward']);


Route::post('/update-drive-config', [EnvConfigController::class, 'updateDriveConfig']);
Route::post('/update-mail-config', [EnvConfigController::class, 'updateMailConfig']);
Route::get('/get-bank-info', [EnvConfigController::class, 'getBankInfo']);
Route::post('update-bank-info', [EnvConfigController::class, 'updateBankInfo']);





