<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\Backend\PropertyController;
use App\Http\Controllers\Backend\PropertyTypeController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [UserController::class, 'index']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/user/profile', [UserController::class, 'userProfile'])->name('user.profile');
    Route::post('/user/profile/store', [UserController::class, 'userProfileStore'])->name('user.profile.store');
    Route::get('/user/logout', [UserController::class, 'userLogout'])->name('user.logout');
    Route::get('/user/change/password', [UserController::class, 'userChangePassword'])->name('user.change.password');
    Route::post('/user/password/update', [UserController::class, 'userPasswordUpdate'])->name('user.password.update');
});


// Admin Areas //
Route::middleware(['auth', 'role:admin'])->group(function() {
   Route::get('/admin/dashboard', [AdminController::class, 'AdminDashboard'])->name('admin.dashboard');
   Route::get('/admin/logout', [AdminController::class, 'adminLogout'])->name('admin.logout');
   Route::get('admin/profile', [AdminController::class, 'adminProfile'])->name('admin.profile');
   Route::post('admin/profile/store', [AdminController::class, 'adminProfileStore'])->name('admin.profile.store');
   Route::get('admin/change/password', [AdminController::class, 'adminChangePassword'])->name('admin.change.password');
   Route::post('admin/update/password', [AdminController::class, 'adminUpdatePassword'])->name('admin.update.password');


   // Propertye
   Route::controller(PropertyTypeController::class)->group(function() {
      // Property Type
      Route::get('/all/type', 'allType')->name('all.type');
      Route::get('/add/type', 'addType')->name('add.type');
      Route::post('/store/type', 'storeType')->name('store.type'); 
      Route::get('/edit/type/{id}', 'editType')->name('edit.type');
      Route::post('/update/type', 'updateType')->name('update.type');
      Route::get('/delete/type/{id}', 'deleteType')->name('delete.type');


      // Amenities
      Route::get('/all/amenities', 'allAmenities')->name('all.amenities');
      Route::get('/add/amenities', 'addAmenities')->name('add.amenities');
      Route::post('/store/amenities', 'storeAmenities')->name('store.amenities');
      Route::get('/edit/amenities/{id}', 'editAmenities')->name('edit.amenities');
      Route::post('/update/amenities', 'updateAmenities')->name('update.amenities');
      Route::get('/delete/amenities/{id}', 'deleteAmenities')->name('delete.amenities');
   });

   // Property
   Route::controller(PropertyController::class)->group(function() {
      Route::get('/all/property', 'allProperty')->name('all.property');
      Route::get('/add/property', 'addProperty')->name('add.property');
      Route::post('/store/property', 'storeProperty')->name('store.property');
      Route::get('/edit/property/{id}', 'editProperty')->name('edit.property');
      Route::post('/update/property', 'updateProperty')->name('update.property');
      Route::get('/delete/property/{id}', 'deleteProperty')->name('delete.property');


      Route::post('/update/property/thambnail', 'updatePropertyThambnail')->name('update.property.thambnail');
      Route::post('/update/property/multiImage', 'updatePropertyMultiImage')->name('update.property.multiImage');

      Route::get('/delete/property/multiImage/{id}', 'deletePropertyMultiImage')->name('delete.property.multiImage');
      Route::post('/store/new/multiImage', 'storeNewMultiImage')->name('store.new.multiImage');
      Route::post('/update/property/facilities', 'updatePropertyFacilities')->name('update.property.facilities');

      Route::get('/details/property/{id}', 'detailsProperty')->name('details.property');

      Route::post('/inactive/property', 'InactiveProperty')->name('inactive.property');
      Route::post('/active/property', 'ActiveProperty')->name('active.property');
   });
});

Route::get('/admin/login', [AdminController::class, 'adminLogin'])->middleware(RedirectIfAuthenticated::class)->name('admin.login');


// User Areas



// Agent Areas //

Route::middleware(['auth', 'role:agent'])->group(function() {
   Route::get('/agent/dashboard', [AgentController::class, 'AgentDashboard'])->name('agent.dashboard');
   Route::post('/agent/logout', [AgentController::class, 'agent']);
});


Route::controller(AgentController::class)->group(function() {
   Route::get('/agent/login', 'agentLogin')->middleware(RedirectIfAuthenticated::class)->name('agent.login');
   Route::post('agent/register', 'agentRegister')->name('agent.register');
   Route::get('/agent/profile', [AgentController::class, 'agentProfile'])->name('agent.profile');
   Route::post('/agent/profile/store', [AgentController::class, 'agentProfileStore'])->name('agent.profile.store');
});


require __DIR__.'/auth.php';