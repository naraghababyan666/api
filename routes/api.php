<?php

use App\Http\Controllers\api\V1\general\QuizQuestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\api\V1\admin\AdminController;
use \App\Http\Controllers\api\V1\general\SiteController;
use \App\Http\Controllers\api\V1\general\QuizController;
use \App\Http\Controllers\api\V1\general\ReviewController;
use App\Http\Controllers\api\V1\general\AuthController;
use \App\Http\Controllers\api\V1\trainingCenter\CourseController;
use App\Http\Controllers\api\V1\trainingCenter\LessonController;
use App\Http\Controllers\api\V1\moderators\ModeratorController;
use App\Http\Controllers\api\V1\admin\CategoryController;
use App\Http\Controllers\api\V1\general\BasketListController;
use App\Http\Controllers\api\V1\general\WishListController;
use App\Http\Controllers\api\V1\general\NotificationController;
use App\Http\Controllers\api\V1\trainer\TrainerController;
use \App\Http\Controllers\api\V1\trainer\TrainerMetaController;
use \App\Http\Controllers\api\V1\trainingCenter\ResourceController;
use \App\Http\Controllers\api\V1\general\PaymentController;
use Laravel\Socialite\Facades\Socialite;

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


Route::group(["prefix" => "v1"], function () {
    Route::post("social/login", [AuthController::class, 'socialLogin']);
    Route::post("registration", [AuthController::class, 'registration'] );
    Route::post("login", [AuthController::class, 'l ogin'] )->name("login");
    Route::post("subscribe", [SiteController::class, 'subscribe'] );
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'] );
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
    Route::get("get-languages", [SiteController::class, 'getLanguages'] );
    Route::get("course-levels", [SiteController::class, 'getCourseLevels'] );
    Route::get("course-statuses", [SiteController::class, 'getCourseStatuses'] );
    Route::get("course-types", [SiteController::class, 'getCourseTypes'] );
    Route::get('categories', [CategoryController::class, 'getCategories'] );
    Route::get('categories-for-filter', [CategoryController::class, 'getCategoriesForFilter'] );
    Route::get('user-review/{id}', [CourseController::class, 'getUserReview']);
    Route::get('categories/{id}', [CategoryController::class, 'getCategory'] );
    Route::put("quiz/update", [QuizController::class, 'updateQuiz']);
    Route::get("home-page-statistics", [SiteController::class, 'homePageStatistics']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get("courses/get-user-courses", [CourseController::class, 'getUserCourses']);
    });
    Route::get("courses", [CourseController::class, 'getCourses'] );
    Route::get("autocompletes/{text}", [SiteController::class, 'autocompleteText']);
    Route::get("get-roles", [SiteController::class, 'getRoles']);
    Route::get("search", [SiteController::class, 'searchFilter']);
    Route::get("{id}/courses", [CourseController::class, 'getTrainerCourses']);
    Route::get('trainer/meta/{id}', [TrainerMetaController::class, 'getTrainerMeta']);
    Route::get("course/{course_id}/reviews", [CourseController::class, 'getReviewsByCourseId']);
    Route::get('lessons', [LessonController::class, 'getLessons'] );
    Route::get('related-courses/{id}', [CourseController::class, 'relatedCourses']);
    Route::get("course-details/{id}", [CourseController::class, 'courseByIdForGuest']);

        Route::middleware('auth:sanctum')->group(function () {
        Route::get('course/generate-certificate/{id}', [CourseController::class, 'Certificate']);

        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('check-password', [AuthController::class, 'checkOldPassword']);
        Route::post('pass-quize', [CourseController::class, 'passQuize']);

        Route::get("trainers/get-user-trainers", [TrainerController::class, 'getUserTrainers']);
        Route::get("change-notification-status", [NotificationController::class, 'changeNotificationStatus'] );
        Route::get("get-notifications", [NotificationController::class, 'getNotifications'] );
        Route::get("get-unread-notifications-count", [NotificationController::class, 'getUnreadNotificationsCount']);
        Route::get("get-last-notifications", [NotificationController::class, 'getLastNotifications']);
        Route::get("get-new-notifications", [NotificationController::class, 'getNewNotifications'] );
        Route::delete('notification/remove/{id}', [NotificationController::class, 'removeNotification']);
        Route::get("course-preview/{id}", [CourseController::class, 'courseByIdForPreview']);
        Route::post("mark-as-read", [NotificationController::class, 'markAsRead'] );
        Route::post("course/set-rate", [ReviewController::class, 'setRateCourse']);
        Route::post("course/remove-rate", [ReviewController::class, 'removeRateCourse']);
        Route::delete("review/delete/{id}", [ReviewController::class, 'deleteReviewById']);
        Route::get("get-current-user", [AuthController::class, 'getCurrentUser']);
        Route::post("/update/user", [AuthController::class, 'updateUserData']);
        Route::get("courses/{id}", [CourseController::class, 'getCourse']);
        Route::post("course/lesson/pass", [CourseController::class, 'setLessonPass']);
        Route::post("course/join/{id}", [CourseController::class, 'joinToCourse']);

        Route::delete("user/delete-avatar", [AuthController::class, 'deleteUserAvatar']);
        Route::prefix("quiz")->group(function () {
            Route::get("/{id}/questions", [QuizController::class, 'getAllQuestions']);
            Route::post("delete/{id}", [QuizController::class, 'deleteQuiz']);
            Route::prefix("question")->group(function () {
                Route::get('/{id}', [QuizQuestionController::class, 'getQuizQuestionById']);
                Route::post('/create', [QuizQuestionController::class, 'store']);
                Route::post('/update/{id}', [QuizQuestionController::class, 'updateQuizQuestion']);
                Route::post('/delete/{id}', [QuizQuestionController::class, 'deleteQuizQuestion']);
            });
        });

        Route::middleware('isTrainer')->group(function(){
            Route::post('trainer/meta/save', [TrainerMetaController::class, 'saveTrainerMeta']);
        });
        Route::post('file-upload', [SiteController::class, 'fileUpload'] );

        Route::middleware('trainerOrTrainingCenter')->group(function(){
            Route::get('students/list', [PaymentController::class, 'studentsList']);

            Route::get('students/courses-list/{id}', [PaymentController::class, 'studentCourses']);

            Route::get('trainer/dashboard', [TrainerController::class, 'getStatistics']);
            Route::get('trainer/stats', [TrainerController::class, 'getStatsForStats']);



            Route::post('trainer/generate-certificate', [TrainerController::class, 'generateCertificate']);
            Route::get('trainer/certificate-info/{id}', [TrainerController::class, 'getCertificateInfo']);

        });
        Route::get("my-learnings", [CourseController::class, 'getMyLearningCourses']);

        Route::get("wish/list", [WishListController::class, 'index'] );
        Route::post("wish/add", [WishListController::class, 'store'] );
        Route::delete("wish/remove/{id}", [WishListController::class, 'destroy'] );

        Route::get("basket/list", [BasketListController::class, 'index']);
        Route::post("basket/add", [BasketListController::class, 'store']);
        Route::post("basket/remove/{id}", [BasketListController::class, 'destroy']);


        Route::middleware(["trainingCenterOrModerator"])->group(function () {
            Route::group(["prefix" => "courses"], function () {
                Route::post("create", [CourseController::class, 'createCourse']);
                Route::post("update", [CourseController::class, 'updateCourse']);
                Route::delete("delete/{id}", [CourseController::class, 'deleteCourse']);
                Route::post("create-section", [CourseController::class, 'createSection']);
                Route::delete("delete-section", [CourseController::class, 'deleteSection']); //-------------------------------------------
                Route::post("update-section", [CourseController::class, 'updateSection']);
            });
            Route::group(["prefix" => "resources"], function () {
            Route::post("create", [ResourceController::class, 'createResource']);
            Route::put("update", [ResourceController::class, 'updateResource']);
            Route::delete("delete/{id}", [ResourceController::class, 'deleteResource']);
            Route::get("/", [ResourceController::class, 'getResources']);
            Route::get("/{id}", [ResourceController::class, 'getResource']);
            });
            Route::group(["prefix" => "lessons"], function () {
                Route::post("/create", [LessonController::class, 'create']);
                Route::put("/update", [LessonController::class, 'update'] );
                Route::delete("/delete/{id}", [LessonController::class, 'delete'] );
                Route::get('/get-section-lessons/{id}', [LessonController::class, 'getSectionLessons'] );
                Route::get('/{id}', [LessonController::class, 'getLesson'] );
            });
        });

        Route::middleware('isAdmin')->group(function(){
            Route::post('/list', [CategoryController::class, 'trainerList']);

            Route::group(["prefix" => "moderators", "namespace" => "\moderators"], function () {
                Route::get("/", [ModeratorController::class, 'getModerators']);
                Route::post("create", [ModeratorController::class, 'createModerator']);
                Route::put("update/{id}", [ModeratorController::class, 'updateModerator']);
                Route::delete("delete", [ModeratorController::class, 'deleteModerator']);
                Route::get("/{id}", [ModeratorController::class, 'getModerator']);
            });
        });

        Route::middleware('superadminAndModerator')->group(function () {
            Route::post("update-status/{id}", [AdminController::class, 'updateStatus']);
            Route::get("/admin/course/{id}", [AdminController::class, 'getCourseDetails']);

            Route::get('/user/list', [AdminController::class, 'userList']);
            Route::get('/user/{id}', [AdminController::class, 'getUserById']);
            Route::delete('/user/{id}/delete', [AdminController::class, 'deleteUser']);
            Route::post('/user/create', [AdminController::class, 'adminOrModeratorCreate']);

            Route::get('/admin/basket/list', [AdminController::class, 'coursesInBasketList']);
            Route::get('/admin/wish/list', [AdminController::class, 'coursesInWishList']);
            Route::get('/trainer/list', [AdminController::class, 'trainerList']);
            Route::get('/trainer/{id}', [AdminController::class, 'trainerById']);

            Route::post('/categories/create', [CategoryController::class, 'createCategories']);
            Route::get('/category/{id}', [CategoryController::class, 'getCategoryItem']);
            Route::post('/categories/update', [CategoryController::class, 'updateCategories']);
            Route::delete('/categories/delete/{id}', [CategoryController::class, 'deleteCategories']);

            Route::get('/course/list', [AdminController::class, 'courseList']);
            Route::get('/course/{id}', [AdminController::class, 'getCourseById']);

            Route::post('/course/{id}/change-status', [AdminController::class, 'changeCourseStatus']);
        });
    });

});
