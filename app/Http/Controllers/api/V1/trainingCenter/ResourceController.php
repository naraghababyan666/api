<?php

namespace App\Http\Controllers\api\V1\trainingCenter;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CourseResource;
use App\Http\Traits\ApiResponseHelpers;
use App\Models\Resource;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ResourceController extends Controller
{
    use ApiResponseHelpers;

    public function createResource(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'path' => ['string', 'max:255', 'required'],
            'title' => ['string', 'max:255', 'required'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ], 200)->header('Status-Code', '401');
        }
        $resource = Resource::create([
            "title" => $request["title"],
            "path" => $request["path"],
            "user_id" => auth()->id()
        ]);

        return $this->respondWithSuccess($resource);

    }

    public function updateResource(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['string', 'max:255', 'required'],
            'id' => ['integer', 'required'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ], 200)->header('Status-Code', '401');
        }
        $resource = Resource::query()->where("id", $request["id"])->where("user_id", auth()->id())->first();
        $resource->title = $request["title"];
        $resource->save();
        return $this->respondWithSuccess($resource);

    }
    public function deleteResource($id){

        $resource = Resource::query()->find($id);
        if ($resource) {

            try {
                $resource->delete();
                $data = [
                    'success' => true,
                    'message' => __("messages.deleted"),
                ];
                return response($data)
                    ->setStatusCode(200)->header('Status-Code', '200');
            } catch
            (\Exception $e) {
                throw new HttpResponseException(response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], $e->getCode())->header('Status-Code', $e->getCode()));
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => __("messages.not_found"),
            ])->header('Status-Code', 200);
        }
    }

    public function getResource($id){
        $resource = Resource::query()->where("id",$id)->where("user_id",auth()->id())->first();
        if ($resource) {
            return $this->respondWithSuccess($resource);
        }
        return response()->json([
            'success' => false,
            'message' => __("messages.not_found"),
        ], 200)->header('Status-Code', 200);
    }

    public function getResources(Request $request){
      $limit =  $request["limit"]??20;
        $resource = Resource::query()->where("user_id",auth()->id())->paginate($limit);
        if ($resource) {
            return $this->respondWithSuccess(new CourseResource($resource));
        }
        return response()->json([
            'success' => false,
            'message' => __("messages.not_found"),
        ], 200)->header('Status-Code', 200);
    }

}
