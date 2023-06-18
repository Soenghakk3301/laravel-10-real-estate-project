<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Agent\AgentPropertyController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\Backend\BlogController;
use App\Http\Controllers\Backend\PropertyController;
use App\Http\Controllers\Backend\PropertyTypeController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\StateController;
use App\Http\Controllers\Backend\TestimonialController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Frontend\CompareController;
use App\Http\Controllers\Frontend\IndexController;
use App\Http\Controllers\Frontend\WishlistController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\RedirectIfAuthenticated;
use FontLib\Table\Type\name;
use Illuminate\Support\Facades\Route;
use PHPUnit\TextUI\Configuration\IncludePathNotConfiguredException;

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

//
Route::middleware('auth')->group(function () {
    Route::get('/user/profile', [UserController::class, 'userProfile'])->name('user.profile');
    Route::post('/user/profile/store', [UserController::class, 'userProfileStore'])->name('user.profile.store');
    Route::get('/user/logout', [UserController::class, 'userLogout'])->name('user.logout');
    Route::get('/user/change/password', [UserController::class, 'userChangePassword'])->name('user.change.password');
    Route::post('/user/password/update', [UserController::class, 'userPasswordUpdate'])->name('user.password.update');
    Route::get('/user/schedule/request', [UserController::class, 'userScheduleRequest'])->name('user.schedule.request');

    Route::get('/live/chat', [UserController::class, 'liveChat'])->name('live.chat');
});

// Frontend Areas
Route::get('/', [UserController::class, 'index']);
Route::get('/property/details/{id}/{slug}', [IndexController::class, 'propertyDetails']);

// Get All Rent Property
Route::get('/rent/property', [IndexController::class, 'rentProperty'])->name('rent.property');

// Get All Buy Property
Route::get('/buy/property', [IndexController::class, 'buyProperty'])->name('buy.property');

// Get All Property Type Data
Route::get('/property/type/{id}', [IndexController::class, 'propertyType'])->name('property.type');

// Get State Details Data
Route::get('/state/details/{id}', [IndexController::class, 'stateDetails'])->name('state.details');

// Search Buy Property
Route::post('/buy/property/search', [IndexController::class, 'buyPropertySearch'])->name('buy.property.search');

// Search Rent Property
Route::post('/rent/property/search', [IndexController::class, 'RentPropertySeach'])->name('rent.property.search');

// All Property Search Option
Route::post('/all/property/search', [IndexController::class, 'allPropertySearch'])->name('all.property.search');

// Blog Details Route
Route::get('/blog/details/{slug}', [BlogController::class, 'blogDetails']);
Route::get('/blog/cat/list/{id}', [BlogController::class, 'blogCatList']);
Route::get('/blog', [BlogController::class, 'blogList'])->name('blog.list');
Route::post('/store/comment', [BlogController::class, 'storeComment'])->name('store.comment');
Route::get('/admin/blog/comment', [BlogController::class, 'adminBlogComment'])->name('admin.blog.comment');
Route::get('/admin/comment/reply/{id}', [BlogController::class, 'adminCommentReply'])->name('admin.comment.reply');
Route::post('/reply/message', [BlogController::class, 'replyMessage'])->name('reply.message');



// Schedule Message Request Route
Route::post('/store/schedule', [IndexController::class, 'storeSchedule'])->name('store.schedule');


// Chat Post Request Route
Route::post('/send-message', [ChatController::class, 'sendMsg'])->name('send.msg');
Route::get('/user-all', [ChatController::class, 'getAllUsers']);
Route::get('/user-message/{id}', [ChatController::class, 'userMsgById']);
Route::get('/agent/live/chat', [ChatController::class, 'agentLiveChat'])->name('agent.live.chat');


// Wishlist Add Route
Route::post('/add-to-wishlist/{property_id}', [WishlistController::class, 'addToWishList']);
Route::controller(WishlistController::class)->group(function () {
    Route::get('/user/wishlist', 'userWishList')->name('user.wishlist');
    Route::get('/get-wishlist-property', 'getWishlistPropety');
    Route::get('/wishlist-remove/{id}', 'wishListRemove');

    Route::post('/add-to-compare/{property_id}', [CompareController::class, 'addToCompare']);
});

// Users Compare
Route::controller(CompareController::class)->group(function () {
    Route::get('/user/compare', 'userCompare')->name('user.compare');
    Route::get('/get-compare-property', 'getCompareProperty');
    Route::post('/compare-remove/{id}', 'compareRemove');
});


// Property Sending Message from User
Route::post('/property/message', [IndexController::class, 'propertyMessage'])->name('property.message');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// Admin Areas //
Route::middleware(['auth', 'roles:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/admin/logout', [AdminController::class, 'adminLogout'])->name('admin.logout');
    Route::get('admin/profile', [AdminController::class, 'adminProfile'])->name('admin.profile');
    Route::post('admin/profile/store', [AdminController::class, 'adminProfileStore'])->name('admin.profile.store');
    Route::get('admin/change/password', [AdminController::class, 'adminChangePassword'])->name('admin.change.password');
    Route::post('admin/update/password', [AdminController::class, 'adminUpdatePassword'])->name('admin.update.password');


    // PropertyType
    Route::controller(PropertyTypeController::class)->group(function () {
        // Property Types
        Route::get('/all/type', 'allType')->name('all.type')->middleware('permission:all.type');
        Route::get('/add/type', 'addType')->name('add.type')->middleware('permission:add.type');
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
    Route::controller(PropertyController::class)->group(function () {
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

        Route::get('/admin/property/message/', 'adminPropertyMessage')->name('admin.property.message');
    });

    // Admin Routes For Perform for agents
    Route::controller(AdminController::class)->group(function () {
        Route::get('/all/agent/', 'allAgent')->name('all.agent');
        Route::get('/add/agent/', 'addAgent')->name('add.agent');
        Route::post('/store/agent/', 'storeAgent')->name('store.agent');
        Route::get('/edit/agent/{id}', 'editAgent')->name('edit.agent');
        Route::post('/update/agent', 'updateAgent')->name('update.agent');
        Route::get('/delete/agent/{id}', 'deleteAgent')->name('delete.agent');
        Route::get('/changeStatus', 'changeStatus');
    });
});

Route::get('/admin/login', [AdminController::class, 'adminLogin'])->middleware(RedirectIfAuthenticated::class)->name('admin.login');


// Agent Areas //
Route::middleware(['auth', 'roles:agent'])->group(function () {
    Route::get('/agent/dashboard', [AgentController::class, 'agentDashboard'])->name('agent.dashboard');
    Route::post('/agent/logout', [AgentController::class, 'agent']);

    Route::controller(AgentPropertyController::class)->group(function () {
        Route::get('/agent/all/property', 'agentAllProperty')->name('agent.all.property');
        Route::get('/agent/add/property', 'agentAddProperty')->name('agent.add.property');
        Route::post('/agent/store/property', 'agentStoreProperty')->name('agent.store.property');
        Route::get('/agent/edit/property/{id}', 'agentEditProperty')->name('agent.edit.property');
        Route::post('/agent/update/property', 'agentUpdateProperty')->name('agent.update.property');

        // other
        Route::post('/agent/update/property/thambnail', 'agentUpdatePropertyThambnail')->name('agent.update.property.thambnail');
        Route::post('/agent/update/property/multiImage', 'agentUpdatePropertyMultiImage')->name('agent.update.property.multiimage');
        Route::get('/agent/property/multiimg/delete/{id}', 'agentPropertyMultiimgDelete')->name('agent.property.multiimg.delete');

        Route::post('/agent/store/new/multiImage', 'agentStoreNewMultiImage')->name('agent.store.new.multiimage');
        Route::post('/agent/update/property/facilities', 'agentUpdatePropertyFacilities')->name('agent.update.property.facilities');
        Route::get('/agent/delete/property/{id}', 'agentDeleteProperty')->name('agent.delete.property');
        Route::get('/agent/details/property/{id}', 'agentDetailsProperty')->name('agent.details.property');

        // Route for the Buy Package
        Route::get('/buy/package', 'buyPackage')->name('buy.package');
        Route::get('/buy/business/plan', 'buyBusinessPlan')->name('buy.business.plan');
        Route::post('/store/business/plan', 'storeBusinessPlan')->name('store.business.plan');

        Route::get('/buy/professional/plan', 'buyProfessionalPlan')->name('buy.professional.plan');
        Route::post('/store/professional/plan', 'storeProfessionalPlan')->name('store.professional.plan');

        Route::get('/package/history', 'packageHistory')->name('package.history');
        Route::get('/agent/package/invoice/{id}', 'agentPackageInvoice')->name('agent.package.invoice');

        Route::get('/admin/package/history', 'adminPackageHistory')->name('admin.package.history');
        Route::get('/package/invoice/{id}', 'packageInvoice')->name('package.invoice');

        // Messaging Section
        Route::get('/agent/property/message/', 'agentPropertyMessage')->name('agent.property.message');
        Route::get('/agent/message/details/{id}', 'agentMessageDetails')->name('agent.message.details');

        //agent details page in frontend
        Route::get('/agent/details/{id}', [IndexController::class, 'agentDetails'])->name('agent.details');

        // Send Message from agent details Page
        Route::post('/agent/details/message', [IndexController::class, 'agentDetailsMessage'])->name('agent.details.message');


        // Schedule Request Route
        Route::get('/agent/schedule/request/', 'agentScheduleRequest')->name('agent.schedule.request');
        Route::get('/agent/details/schedule/{id}', 'agentDetailsSchedule')->name('agent.details.schedule');
        Route::post('/agent/update/schedule', 'agentUpdateSchedule')->name('agent.update.schedule');
    });
});


// Agent Routes For Authentication
Route::controller(AgentController::class)->group(function () {
    Route::get('/agent/login', 'agentLogin')->middleware(RedirectIfAuthenticated::class)->name('agent.login');
    Route::post('agent/register', 'agentRegister')->name('agent.register');
    Route::get('/agent/profile', 'agentProfile')->name('agent.profile');
    Route::post('/agent/profile/store', 'agentProfileStore')->name('agent.profile.store');
    Route::get('/agent/logout', 'agentLogout')->name('agent.logout');
});


// All State Routes
Route::controller(StateController::class)->group(function () {
    Route::get('/all/state', 'allState')->name('all.state');
    Route::get('/add/state', 'addState')->name('add.state');
    Route::post('/store/state', 'storeState')->name('store.state');
    Route::get('/edit/state/{id}', 'editState')->name('edit.state');
    Route::post('/update/state', 'updateState')->name('update.state');
    Route::get('/delete/sate/{id}', 'deleteState')->name('delete.state');
});


// All Testimonials Routes
Route::controller(TestimonialController::class)->group(function () {
    Route::get('/all/testimonials', 'allTestimonials')->name('all.testimonials');
    Route::get('/add/testimonials', 'addTestimonials')->name('add.testimonials');
    Route::post('/store/testimonials', 'storeTestimonials')->name('store.testimonials');
    Route::get('/edit/testimonials/{id}', 'editTestimonials')->name('edit.testimonials');
    Route::post('/update/testimonials', 'updateTestimonials')->name('update.testimonials');
    Route::get('/delete/testimonials', 'deleteTestimonials')->name('delete.testimonials');
});

// Blog Category All Route
Route::controller(BlogController::class)->group(function () {
    Route::get('/all/blog/category', 'allBlogCategory')->name('all.blog.category');
    Route::get('/add/blog/category', 'addBlogCategory')->name('add.blog.category');
    Route::post('/store/blog/category', 'storeBlogCategory')->name('store.blog.category');
    Route::get('/edit/blog/category/{id}', 'editBlogCategory')->name('edit.blog.category');
    Route::post('/update/blog/category', 'upudateBlogCategory')->name('update.blog.category');
    Route::get('/delete/blog/category/{id}', 'deleteBlogCategory')->name('delete.blog.category');
});

// Blog Post All Routes
Route::controller(BlogController::class)->group(function () {
    Route::get('/all/post', 'allPost')->name('all.post');
    Route::get('/add/post', 'addPost')->name('add.post');
    Route::post('/store/post', 'storePost')->name('store.post');
    Route::get('/edit/post/{id}', 'editPost')->name('edit.post');
    Route::post('/update/post', 'updatePost')->name('update.post');
    Route::get('/delete/post/{id}', 'deletePost')->name('delete.post');
});



Route::controller(SettingController::class)->group(function () {
    Route::get('/smtp/setting', 'SmtpSetting')->name('smtp.setting');
    Route::post('/update/smpt/setting', 'updateSmtpSetting')->name('update.smpt.setting');



    // Site Setting All Route
    Route::get('/site/setting', 'siteSetting')->name('site.setting');
    Route::post('/update/site/setting', 'updateSiteSetting')->name('update.site.setting');
});



Route::controller(RoleController::class)->group(function () {

    //Permissions
    Route::get('/all/permission', 'allPermission')->name('all.permission');
    Route::get('/add/permission', 'addPermission')->name('add.permission');
    Route::post('/store/permission', 'storePermission')->name('store.permission');
    Route::get('/edit/permission/{id}', 'editPermission')->name('edit.permission');
    Route::post('/update/permission', 'updatePermission')->name('update.permission');
    Route::get('/delete/permission/{id}', 'deletePermission')->name('delete.permission');

    Route::get('/import/permission', 'importPermission')->name('import.permission');
    Route::get('/export', 'export')->name('export');
    Route::post('/import', 'import')->name('import');


    // Roles
    Route::get('/all/roles', 'AllRoles')->name('all.roles');
    Route::get('/add/roles', 'AddRoles')->name('add.roles');
    Route::post('/store/roles', 'StoreRoles')->name('store.roles');
    Route::get('/edit/roles/{id}', 'editRoles')->name('edit.roles');
    Route::post('/update/roles', 'updateRoles')->name('update.roles');
    Route::get('/delete/roles/{id}', 'deleteRoles')->name('delete.roles');

    Route::get('/add/roles/permission', 'addRolesPermission')->name('add.roles.permission');
    Route::post('/role/permission/store', 'rolePermissionStore')->name('role.permission.store');
    Route::get('/all/roles/permission', 'allRolesPermission')->name('all.roles.permission');
    Route::get('/admin/edit/roles/{id}', 'adminEditRoles')->name('admin.edit.roles');
    Route::post('/admin/roles/update/{id}', 'adminRolesUpdate')->name('admin.roles.update');
    Route::get('/admin/delete/roles/{id}', 'adminDeleteRoles')->name('admin.delete.roles');
});


Route::controller(AdminController::class)->group(function () {
    Route::get('/all/admin', 'allAdmin')->name('all.admin');
    Route::get('/add/admin', 'allAdmin')->name('add.admin');
    Route::post('/store/admin', 'storeAdmin')->name('store.admin');
    Route::get('/edit/admin/{id}', 'editAdmin')->name('edit.admin');
    Route::post('/update/admin', 'updateAdmin')->name('update.admin');
    Route::get('/delete/admin/{id}', 'deleteAdmin')->name('delete.admin');
});


require __DIR__.'/auth.php';