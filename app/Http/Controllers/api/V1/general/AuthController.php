<?php

namespace App\Http\Controllers\api\V1\general;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\BasketList;
use App\Models\Course;
use App\Models\Role;
use App\Models\User;
use App\Models\WishList;
use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Doctrine\DBAL\Exception\DatabaseDoesNotExist;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\Concerns\Has;
use PHPUnit\Exception;
use Illuminate\Support\Facades\DB;
class AuthController extends Controller
{

    public function registration(UserRequest $request)
    {
        try {
            if($request['role_id'] != Role::SUPER_ADMIN && $request['role_id'] != Role::MODERATOR){
                $newUser = User::create([
                    'first_name' => $request['first_name'],
                    'last_name' => $request['last_name'],
                    'email' => $request['email'],
                    'password' => Hash::make($request['password']),
                    'slug' => SlugService::createSlug(User::class, 'slug', $request['first_name'] . '_' .$request['last_name']),
                    'role_id' => $request['role_id'],
                ]);
                if (isset($request["phone"])) {
                    $newUser->phone = $request["phone"];
                }
                if (isset($request["company_name"])) {
                    $newUser->company_name = $request["company_name"];
                }
                if (isset($request["tax_identity_number"])) {
                    $newUser->tax_identity_number = $request["tax_identity_number"];
                }
                $newUser->save();
                Auth::login($newUser);
                $token = $newUser->createToken($request["email"], ['server:update']);
                $newUser["api_token"] = $token->plainTextToken;
                $data = [
                    'success' => true,
                    'data' => new UserResource($newUser),
                ];
                return
                    response($data)->setStatusCode(200)->header('Status-Code', '200');
            }else{
                return response()->json(['success' => false, 'message' => __('messages.forbidden')]);
            }
        } catch (Exception $e) {

            throw new HttpResponseException(response()->json([
                'message' => $e->getMessage(),
            ],  $e->getCode())->header('Status-Code', $e->getCode()));
        }
    }

    public function socialLogin(Request $request){
        $validator = Validator::make($request->all(), [
            'provider' => 'required',
            'unique_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ])->header('Status-Code', 200);
        }
        $userFromDb = User::query()->where('provider', $request->provider)->where('unique_id', $request->unique_id)->first();
        if(is_null($userFromDb)){
            if(in_array(strtolower($request->provider), ['google', 'facebook'])) {
                if(isset($request->first_name, $request->last_name, $request->email, $request->role_id, $request->unique_id, $request->provider)){
                    $hasEmail = User::query()->where('email', $request->email)->exists();
                    if($hasEmail) {
                        return response()->json(['success' => false, 'message' => __('validation.exists')]);
                    }else{
                        $validateInfo = Validator::make($request->all(), [
                            'email' => 'required|email|unique:users',
                            'role_id' => 'required|integer|between:3,5',
                            'provider' => 'required',
                            'unique_id' => 'required',
                            'first_name' => 'required|min:4',
                            'last_name' => 'required|min:4',
                        ]);
                        if ($validateInfo->fails()) {
                            return response()->json([
                                'success' => false,
                                "errors" => $validateInfo->errors()
                            ])->header('Status-Code', 200);
                        }

                        $data = $validateInfo->validated();
                        if(isset($request->email) && isset($request->role_id)){
                            $newUser = User::create([
                                'first_name' => $data['first_name'],
                                'last_name' => $data['last_name'] ,
                                'email' => $data['email'],
                                'company_name' => $request->company_name ?? null,
                                'avatar' => $request->avatar ?? null,
                                'tax_identity_number' => $request->tax_identity_number ?? null,
                                'provider' => $data['provider'],
                                'unique_id' => $data['unique_id'],
                                'password' => Hash::make($data['unique_id'] . '/' . $data['provider']),
                                'slug' => SlugService::createSlug(User::class, 'slug', $data['first_name'] . '_' . $data['last_name']),
                                'role_id' => $data['role_id'],
                            ]);
                            $newUser->save();
                            Auth::login($newUser);
                            $token = $newUser->createToken($request["email"], ['server:update']);
                            $newUser["api_token"] = $token->plainTextToken;
                            $data = [
                                'success' => true,
                                'data' => new UserResource($newUser),
                            ];
                            return
                                response($data)->setStatusCode(200)->header('Status-Code', '200');
                        }
                        else {
                            return response()->json(['success' => true, 'message' => 'Account not exists']);
                        }
                    }
                }else{
                    return response()->json(['success' => true, 'message' => 'User not exists']);
                }
            }
            return response()->json(['success' => false, 'message' => __('validation.invalid-provider')], 500);
        }
        else{
            if($userFromDb->provider = $request->provider){
                Auth::login($userFromDb);
                $token = $userFromDb->createToken($userFromDb["email"], ['server:update']);
                $userFromDb["api_token"] = $token->plainTextToken;
                $data = [
                    'success' => true,
                    'data' => new UserResource($userFromDb),
                ];
                return response($data)
                    ->setStatusCode(200)->header('Status-Code', '200');
            }
            return response()->json(['success' => false, 'message' => __('validation.invalid-provider')]);
        }

    }
    public function  logout(){
        Auth::user()->tokens()->where('id', Auth::id())->delete();
        $user = Auth()->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        return response()->json([   'success' => true,
                                   'message' => __("messages.log_out"),])->header('Status-Code', '200');
    }

    public function checkOldPassword(Request $request){
        $user = Auth::user();
        if(Hash::check($request->all()['password'], $user['password'])){
            return response()->json(['success' => true,"message"=> __('validation.valid-password')]);
        }
        return response()->json(['success' => false, 'message' => __('validation.invalid-password')]);
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), ['email' => 'required|email', 'password' => 'required']);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ],)->header('Status-Code', 200);
        }
        $validUser = Auth::attempt(['email' => $request["email"], 'password' => $request["password"]]);
        if ($validUser) {
            $user = Auth::getProvider()->retrieveByCredentials(['email' => $request["email"], 'password' => $request["password"]]);
            Auth::login($user);
            $token = $user->createToken($request["email"], ['server:update']);
            $user["api_token"] = $token->plainTextToken;
            $data = [
                'success' => true,
                'data' => new UserResource($user),
            ];
            return response($data)
                ->setStatusCode(200)->header('Status-Code', '200');

        } else {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => __('messages.invalid_user'),
            ], 401)->header('Status-Code', '401'));
        }

    }


    public function forgotPassword(Request $request)
    {

        $user = User::query()->where("email",  $request->only('email'))->first();
        if(!empty($user->provider)){
            return response()->json([
                'success' => false,
                'message' => __('messages.reset_by_provider',["provider"=>ucfirst($user->provider)]),
            ], )->header('Status-Code', 200);
        }

        try {
            $response = Password::sendResetLink(
                $request->only('email')
            );
            switch ($response) {
                case Password::RESET_LINK_SENT:
                    return response()->json([ 'success' => true,"message" => __("messages.reset_link")]);
                case Password::INVALID_USER:
                    return response()->json([
                        'success' => false,
                        'message' => __('messages.reset_user_nf'),
                    ], )->header('Status-Code', 200);
            }
        } catch (Exception $e) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode())->header('Status-Code',  $e->getCode()));
        }

    }

    public function resetPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ],)->header('Status-Code', 200);
        }
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );
        return $status === Password::PASSWORD_RESET
            ? (response()->json([ 'success' => true,"message" =>__("messages.reset_success")]))
            : response()->json([
                'success' => false,
                'message' =>__('messages.unauthorized'),
            ], 401)->header('Status-Code', '401');
    }

    public function getCurrentUser()
    {
        $user = auth()->user();
        $user->avatar = isset($user->avatar) ? env("APP_URL") . "/" . $user->avatar : null;
        $userArray = json_decode(json_encode($user),true);
//        $sql1 = "select count(*) as wish_list_count
//                     from wish_lists as w
//                     INNER JOIN `courses` ON courses.id = w.course_id AND courses.status = 3
//                     WHERE w.user_id={$user->id}";
////        $sql1 = "select count(*) as wish_list_count from wish_lists as w where w.user_id={$user->id}";
////        $sql2 = "select count(*) as basket_list_count from basket_lists as b where b.user_id={$user->id}";
//        $sql2 = "select count(*) as basket_list_count
//
//                     from basket_lists as b
//                     INNER JOIN `courses` ON courses.id = b.course_id AND courses.status = 3
//                     WHERE b.user_id={$user->id}";
//        $wish_list_count = DB::select($sql1);
//        $userArray['wish_list_count'] = $wish_list_count[0]->wish_list_count;
//        $basket_list_count = DB::select($sql2);
//        $userArray['basket_list_count'] = $basket_list_count[0]->basket_list_count;

        $basketCount = BasketList::query()->with(['courses'])->where('user_id', Auth::id())->get();
        $wishCount = WishList::query()->with(['courses'])->where('user_id', Auth::id())->get();
        $basket_list_count = $this->loopCourseList($basketCount);
        $wish_list_count = $this->loopCourseList($wishCount);
        $userArray['basket_list_count'] = $basket_list_count;
        $userArray['wish_list_count'] = $wish_list_count;
        $data = ['success' => true, "data" => $userArray];

        return response()->json($data);
    }

    public function loopCourseList($data){
        $count = 0;
        foreach($data as $item){
            $course = Course::getExpiredCourses($item['courses']);
            if(!is_null($course)){
                $count += 1;
            }
        }
        return $count;
    }

    public function updateUserData(Request $request){
        $data =  [
            'first_name' => 'min:5',
            'last_name' => 'min:5',
            'email' => 'email|min:5'
        ];
        if(!is_null($request->current_password)){
            $data['current_password'] = 'required|string|min:8';
            $data['new_password'] = 'required|string|min:8';
        }
        $validated = Validator::make($request->all(),$data);
        $errors = [];
        if(!empty($validated->errors()->messages())){
            foreach (array_keys($validated->errors()->toArray()) as $error){
                $errors[] = [$error => __('validation.'.$error)];
            }
            return response()->json(['messages' => $errors], 200);
        }
        $user = User::where('id', Auth::id())->first();
        $user->first_name = $request->first_name ?? $user->first_name;
        $user->last_name = $request->last_name ?? $user->last_name;
        $user->email = $request->email ?? $user->email;
        $user->phone = $request->phone ?? $user->phone;
        $slugString = $request->first_name ?? $user->first_name . '-' . $request->last_name ?? $user->last_name;
        if($request->first_name){
            $slugString = $request->first_name;
        }else{
            $slugString = $user->first_name;
        }
        if($request->last_name){
            $slugString .= '-' .$request->last_name;
        }else{
            $slugString .= '-' . $user->last_name;
        }
        $user->slug = SlugService::createSlug(User::class, 'slug', $slugString);
        $user->tax_identity_number = $request->tax_identity_number ?? $user->tax_identity_number;
        $user->company_name = $request->company_name ?? $user->company_name;
        if(isset($validated->validated()['current_password']) && isset($validated->validated()['new_password'])){
            if(Hash::check($request->current_password, $user->password)){
                $user->password = Hash::make($request->new_password);
            }else{
                return response()->json([
                    'success' => false,
                    'message' =>__('messages.wrong_current_password'),
                ]);
            }
        }
        $user->save();
        return response()->json(['message' => __('messages.user-updated')]);
    }



    public function deleteUserAvatar(){
        $user = User::query()->find(Auth::id());
        if($user){
            $user->avatar = null;
            $user->save();
            return response()->json([
                'success' => true,
                'message' =>__('messages.deleted'),
            ]);
        }
        return response()->json([
            'success' => false,
            'message' =>__('messages.not_found'),
        ]);
    }

}
