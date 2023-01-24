<?php

namespace App\Http\Controllers\api\V1\general;

use App\Http\Controllers\Controller;
use App\Http\Requests\BasketListsRequest;
use App\Models\BasketList;
use App\Models\Category;
use App\Models\Language;
use App\Models\CategoryTranslation;
use App\Models\Course;
use App\Models\Trainer;
use App\Models\WishList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BasketListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */


    public function index(Request $request)
    {
        $r = $request->all();

        $filter['language_id'] = $this->language_id;
        $filter['user_id'] = Auth::id();
        $basketList = BasketList::query()->where('user_id', Auth::id())->get();
        if(count($basketList) != 0){
            foreach ($basketList as $item){
                $ids[] = $item->course_id;
            }
            $filter['course_ids'] = implode(',',$ids);
            $filter['limit'] = -1;
            $result = Course::getListOfCourses($filter);
            return response()->json(['success' => true, 'data' => $result['data']]);
        }
        return response()->json(['success' => false, 'message' => __('messages.empty-basket')]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(BasketListsRequest $request)
    {
        $items = BasketList::where('course_id', $request->course_id)->where('user_id', Auth::id())->get();
        if(count($items) != 0) {

                    return response()->json(['success' => false, 'message' => __('messages.already-in-basket-list')],404);

            }
        BasketList::create([
            'user_id' => Auth::id(),
            'course_id' => $request->course_id
        ]);
        return response()->json(['success' => true, 'message' => __('messages.added-on-basket-list')], 200);
     }

    public function moveToWishList(Request $request){
        if($request->all()['course_id']){
            $itemInBasket = BasketList::query()->where('course_id', $request->all()['course_id'])->where('user_id', Auth::id())->first();
            if($itemInBasket != null){
                $itemInWish = WishList::query()->where('course_id', $request->all()['course_id'])->where('user_id', Auth::id())->first();
                if($itemInWish == null){
                    WishList::query()->create([
                        'user_id' => Auth::id(),
                        'course_id' => $request->all()['course_id']
                    ]);
                    $itemInBasket->delete();
                    return response()->json(['success' => true, 'message' => __('messages.added-on-wish-list')]);
                }
                return response()->json(['success' => false, 'message' => __('messages.already-in-wish-list')]);

            }
            return response()->json(['success' => false, 'message' => __('messages.course-not-found')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BasketList  $basketList
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $item = BasketList::query()->where('course_id', $id)
            ->where('user_id', Auth::id())->first();
        if(!empty($item)){
            $item->delete();
            return response()->json(['success' => true, 'message' => __('messages.deleted')], 200);
        }
        return response()->json(['success' => false, 'message' => __('messages.forbidden')]);
    }
}
