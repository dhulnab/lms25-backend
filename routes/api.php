<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\BookBorrowingController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BorrowRequestController;
use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\EBookController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\FindBestCopyController;
use App\Http\Controllers\Api\FirebaseController;
use App\Http\Controllers\Api\ForBorrowCopyController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserInventoryController;
use App\Http\Middleware\ClientAuth;
use Illuminate\Support\Facades\Route;

Route::post('/signup', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/Admin/login', [AdminController::class, 'login']);
Route::post('/email/verify', [EmailVerificationController::class, 'emailVerification']);
Route::post('/refresh-token', [UserController::class, 'refreshToken']);


Route::middleware([ClientAuth::class])->group(function () {
    //notification
    Route::post('/store-fcm-token', [FirebaseController::class, 'store']);
    Route::get('/notifications', [FirebaseController::class, 'show']);
    //user inventory
    Route::get('/user-inventory', [UserInventoryController::class, 'userInventory']);
    Route::get('/user-requests', [UserInventoryController::class, 'UserRequest']);
    Route::get('/user-penalties', [UserInventoryController::class, 'UserPenalties']);
    //user
    Route::post('/update-user', [EmailVerificationController::class, 'requestOtp']);
    Route::put('/verify-update-user', [EmailVerificationController::class, 'verifyOtpAndUpdateUser']);
    Route::get('/home', [HomeController::class, 'index']);
    //user
    Route::get('user', [UserController::class, 'getUser']);
    Route::post('logout', [UserController::class, 'logout']);
    //book
    Route::get('find-borrow-copy/{id}', [FindBestCopyController::class, 'findBestCopy']);
    Route::get('book', [BookController::class, 'getBook']);
    Route::get('/books/search', [BookController::class, 'search']);
    Route::get('book/checkout/{id}', [PurchaseController::class, 'checkout']);
    Route::post('book/purchase-hard-copy/{id}', [PurchaseController::class, 'purchaseHardBook']);
    Route::post('book/purchase-e-book/{id}', [PurchaseController::class, 'purchaseEbook']);
    Route::get('book/{id}', [BookController::class, 'getBook']);
    Route::get('book/check/{id}', [BookController::class, 'haveCopies']);
    //category
    Route::get('category-books/{id}', [CategoriesController::class, 'categoryBooks']);
    Route::get('categories', [CategoriesController::class, 'getCategories']);
    Route::get('categories/{id}', [CategoriesController::class, 'getCategories']);
    Route::get('categories/child-categories/{id}', [CategoriesController::class, 'childCategories']);
    //for borrow copy
    Route::get('borrow-copy/{bookId}/{id?}', [ForBorrowCopyController::class, 'getForBorrowCopy']);
    //borrow request
    Route::post('create-borrow-request', [BorrowRequestController::class, 'createRequest']);
    Route::put('borrow_request/{id}', [BorrowRequestController::class, 'updateRequest']);
    Route::delete('borrow_request/{id}', [BorrowRequestController::class, 'deactivateRequest']);
    //find best copy 
    Route::get('find-borrow-copy/{id}', [FindBestCopyController::class, 'findBestCopy']);
    //pdf's
    Route::get('/book/pdf/preview/{id}', [EBookController::class, 'preview']);
    Route::post('/book/pdf/upload/{id}', [EBookController::class, 'upload']);
    Route::put('/book/pdf/update/{id}', [EBookController::class, 'update']);
    Route::delete('/book/pdf/delete/{id}', [EBookController::class, 'delete']);
});


Route::middleware([ClientAuth::class . ':admin'])->group(function () {
    Route::patch('promote-to-admin/{id}', [AdminController::class, 'promoteToAdmin']);
    //book
    Route::post('book', [BookController::class, 'addBook']);
    Route::put('book/{id}', [BookController::class, 'updateBook']);
    Route::delete('book/{id}', [BookController::class, 'deleteBook']);
    //category
    Route::post('categories', [CategoriesController::class, 'addCategories']);
    Route::put('categories/{id}', [CategoriesController::class, 'updateCategories']);
    Route::delete('categories/{id}', [CategoriesController::class, 'deleteCategory']);
    //copyForBorrow
    Route::get('borrow-copy/{bookId}/{id?}', [ForBorrowCopyController::class, 'getForBorrowCopy']);
    Route::post('borrow-copy', [ForBorrowCopyController::class, 'addForBorrowCopy']);
    Route::put('borrow-copy/{id}', [ForBorrowCopyController::class, 'updateForBorrowCopy']);
    Route::delete('borrow-copy/{id}', [ForBorrowCopyController::class, 'deleteForBorrowCopy']);
    //borrow request
    Route::get('borrow_request', [BorrowRequestController::class, 'getBorrowRequest']); //all
    Route::get('borrow_request/{id}', [BorrowRequestController::class, 'getBorrowRequest']); //specific
    Route::get('user-borrow-requests/{id}', [BorrowRequestController::class, 'getUserBorrowRequests']); //all user borrow request
    Route::post('borrow_request', [BorrowRequestController::class, 'createRequest']);
    Route::put('borrow_request/{id}', [BorrowRequestController::class, 'updateRequest']);
    Route::delete('borrow_request/{id}', [BorrowRequestController::class, 'deactivateRequest']);
    //return book copy
    Route::post('return-book-copy/{id}', [BookBorrowingController::class, 'returnCopy']);
    //receive book copy
    Route::post('receive-book-copy', [BookBorrowingController::class, 'pickBookCopy']);
    //test
});


// Route::middleware([ClientAuth::class . ':superAdmin'])->group(function () {
//     Route::patch('promote-to-admin/{id}', [AdminController::class, 'promoteToAdmin']);
// });
