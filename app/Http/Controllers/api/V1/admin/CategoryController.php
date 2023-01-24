<?php

namespace App\Http\Controllers\api\V1\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Language;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use PHPUnit\Exception;

use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{

    public function getCategories(Request $request)
    {
        $language = $this->language_id;
        Category::$language = $language;
        $categories = Category::with([
                "children"
            ])->whereNull(["parent_id"])
                ->whereHas('translation')->orderBy('ordering', 'asc')->get();
//        $categories = $categories->sortBy(function ($categories){
//          return $categories->title;
//        });

        return response(new UserResource($categories))
            ->setStatusCode(200)->header('Status-Code', '200');
    }

    public function getCategory($id, Request $request)
    {
        $language = $this->language_id;
        Category::$language = $language;
        $category = Category::with(["translation","children"])->find($id);
        return response(new UserResource($category))
            ->setStatusCode(200)->header('Status-Code', '200');
    }

    public function createCategories(CategoryRequest $request)
    {
                try {
                    if($request['categories']){
                        foreach ($request["categories"] as $category) {
                            $model = new Category();
                            $categoryModel = $this->saveCategory($model, $category);
                            if (!empty($categoryModel->id)) {
                                foreach ($category["category_info"] as $info) {
                                    $categoryTranslation = new  CategoryTranslation();
                                    $categoryTranslation->title = $info["title"];

                                    $categoryTranslation->language_id = $info["language_code"];
                                    $categoryTranslation->category_id = $categoryModel->id;
                                    $categoryTranslation->save();
                                }
                            }
                        }
                        return response(['success' => true, 'message' => __("messages.category_created")])
                            ->setStatusCode(200)->header('Status-Code', '200');
                    }else{
                        return response()->json(['success' => false, 'message' => __('messages.category-required')]);
                    }

                } catch (Exception $e) {
                    throw new HttpResponseException(response()->json([
                        'message' => $e->getMessage(),
                    ], $e->getCode())->header('Status-Code', $e->getCode()));

                }
    }


    public function updateCategories(Request $request)
    {


                    $category = $request->all();

                        if (isset($category["id"])) {
                            $model = Category::query()->find($category["id"]);
                            $categoryModel = $this->saveCategory($model, $category);
                            if (!empty($categoryModel->id)) {
                                foreach ($category["category_info"] as $info) {
                                    $categoryTranslation = CategoryTranslation::query()->where("category_id", $category["id"])->where("language_id",$info["language_code"] )->first();
                                    if (!empty($categoryTranslation)) {
                                        $categoryTranslation->title = $info["title"];
                                        $categoryTranslation->save();
                                    }
                                }
                            } else {
                                throw new HttpResponseException(response()->json([
                                    'success' => false,
                                    'message' => __('messages.fail'),
                                ], 500)->header('Status-Code', '500'));
                            }
                        } else {
                            throw new HttpResponseException(response()->json([
                                'success' => false,
                                'message' => __('messages.category-not-found'),
                            ], 404)->header('Status-Code', '404'));
                        }

                    return response(__("messages.category_updated"))
                        ->setStatusCode(200)->header('Status-Code', '200');

    }
        public function getCategoryItem($id){


            $sql = "select tr.language_id, tr.title, c.id, tr.title, c.parent_id, c.ordering
                from category_translations as tr
                left join categories as c ON c.id = tr.category_id
                where tr.category_id = {$id}";
            $result = DB::select($sql);

            if(!empty($result)){
                $data = [
                    'id'=>$result[0]->id,
                    'parent_id'=>$result[0]->parent_id,
                    'ordering'=>$result[0]->ordering,
                    'titles'=>[
                        1=>'',
                        2=>'',
                    ],
                    ];

                foreach($result as $item){
                    $data['titles'][$item->language_id] = $item->title;
                }
                return response()->json(['data' => $data], 200);

            }else{
                $data = [
                    'success' => false,
                    "message" => __("Data not found")
                ];
                return response($data)
                    ->setStatusCode(200)->header('Status-Code', '200');
            }




        }

    public function deleteCategories($id)
    {

        $model = Category::query()->find($id);
        if($model){
            $model->delete();
            $data = [
                'success' => true,
                "message" => __("messages.delete_category")
            ];
            return response($data)
                ->setStatusCode(200)->header('Status-Code', '200');
        }else{
            $data = [
                'success' => false,
                "message" => __("Something wwent wrong")
            ];
            return response($data)
                ->setStatusCode(200)->header('Status-Code', '200');
        }

    }

    private function saveCategory($categoryModel, $category)
    {
        try {
            if(!isset($category["ordering"])){
                $category["ordering"] = 0;
            }
            if(!isset($category["parent_id"])){
                $category["parent_id"] = null;
            }
            $categoryModel->ordering = $category["ordering"];
            if ($category["parent_id"] > 0) {
                if (Category::isExists($category["parent_id"])) {
                    $categoryModel->parent_id = $category["parent_id"];
                } else {
                    throw new HttpResponseException(response()->json([
                        'success' => false,
                        'message' => __('messages.parent-category-not-found'),
                    ], 200)->header('Status-Code', '200'));
                }
            } elseif ($category["parent_id"] == 0) {
                $categoryModel->parent_id = null;
            }

            if (!empty($category["icon"])) {
                if ($file = $category["icon"]) {
                    $this->validate($category, ['icon' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',]);
                    $filename= 'images/icons/'.date('YmdHi').'.'.$file->extension();
                    $file-> move(public_path('images/icons'), $filename);
                    $categoryModel->icon = $filename;
                }
            }
            $categoryModel->save();
            return $categoryModel;
        } catch (Exception $e) {
            throw new HttpResponseException(response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode())->header('Status-Code', $e->getCode()));

        }
    }

    public function getCategoriesForFilter(Request $request)
    {
        $language = $this->language_id;
        Category::$language = $language;

        $sql = "SELECT c.id, c_tr.title,c.parent_id from categories as c left join category_translations as c_tr on c_tr.category_id=c.id where c_tr.language_id={$language} order by c.ordering,c.parent_id ASC";
        $data = DB::select($sql);
        $result = [];
        foreach($data as $key=>$val){

          if(!$val->parent_id){
              $result[$val->id] = ['id'=>$val->id,'text'=>$val->title];
          }else{
              if(!isset( $result[$val->parent_id]['children']) ){
                  $result[$val->parent_id]['children'] = [];
              }
              $result[$val->parent_id]['children'][] =  ['id'=>$val->id,'text'=>$val->title,'children'=>[]];
          }
        }

        foreach($result as $k=>$v ){
            if(empty($v['children'])){
                unset($v['children']);
            }
         $new_result[] =  $v;
        }

        return response()->json([
            'success' => true,
            'data' => $new_result,
        ])->header('Status-Code', '200');



    }
}
