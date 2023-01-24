<?php

namespace App\Http\Controllers\api\V1\moderators;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Exception;

class ModeratorController extends Controller
{

    public function getModerators()
    {

        $moderators = User::query()->where("role_id", '=', Role::MODERATOR)->get();
        return response()->json([
            'success' => true,
            'data' => $moderators,
        ])->header('Status-Code', '200');
    }

    public function getModerator($id)
    {

        $moderator = User::query()->where("role_id", Role::MODERATOR)->where("id", $id)->get();
        return response()->json([
            'success' => true,
            'data' => $moderator,
        ])->header('Status-Code', '200');

    }

    public function createModerator(UserRequest $request)
    {

        try {
            $moderator = User::create([
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'email' => $request['email'],
                'password' => Hash::make($request['password']),
                'role_id' => Role::MODERATOR,
            ]);
            if (isset($request["company_name"])) {
                $moderator->company_name = $request["company_name"];
            }
            if (isset($request["phone"])) {
                $moderator->phone = $request["phone"];
            }
            if (isset($request["tax_identity_number"])) {
                $moderator->tax_identity_number = $request["tax_identity_number"];
            }
            $moderator->save();

            $data = [
                'success' => true,
                'data' => new UserResource($moderator),
            ];
            return
                response($data)->setStatusCode(200)->header('Status-Code', '200');
        } catch (Exception $e) {
            return response()->json([

                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode())->header('Status-Code',  $e->getCode());
        }
    }

    public function updateModerator($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['string', 'max:255'],
            'last_name' => ['string', 'max:255'],
            'company_name' => ['string', 'max:255'],
            'email' => ['string', 'email', 'max:255'],
            'password' => ['string', 'min:8'],
            'tax_identity_number' => ['integer'],]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ], 401)->header('Status-Code', '401');
        }
        if ($id) {
            $moderator = User::query()->where("id",$id)->first();
            if (!empty($moderator)) {
                $moderator->email = $request['email'] ?? $moderator->email;
                $moderator->first_name = $request['first_name'] ?? $moderator->first_name;
                $moderator->last_name = $request['last_name'] ?? $moderator->last_name;
                $moderator->phone = $request['phone'] ?? $moderator->phone;
                $moderator->last_name = $request['last_name'] ?? $moderator->last_name;
                $moderator->password = $request['password'] ? Hash::make($request['password']) : $moderator->password;
                $moderator->company_name = $request['company_name'] ?? $moderator->company_name;
                $moderator->tax_identity_number = $request['tax_identity_number'] ?? $moderator->tax_identity_number;
                $moderator->save();
                $data = [
                    'success' => true,
                    'data' => new UserResource($moderator),
                ];
                return response($data)->setStatusCode(200)->header('Status-Code', '200');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __("messages.user_not_found"),
                ], 401)->header('Status-Code', '401');
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => __("messages.user_not_found"),
            ], 401)->header('Status-Code', '401');
        }
    }

    public function deleteModerator(Request $request)
    {
        if (!empty($request["id"])) {

            $moderator = User::query()->find($request["id"]);
            if ($moderator) {
                $moderator->delete();
                $data = [
                    'success' => true,
                    'message' => __("messages.moderator_delete"),
                ];
                return response($data)
                    ->setStatusCode(200)->header('Status-Code', '200');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __("messages.user_not_found"),
                ], 401)->header('Status-Code', '401');
            }
        }
    }
}
