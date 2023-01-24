<?php

namespace App\Http\Controllers\api\V1\general;

use App\Http\Controllers\Controller;
use App\Http\Requests\WishListsRequest;
use App\Models\BasketList;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Course;
use App\Models\Trainer;
use App\Models\WishList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishListController extends Controller
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
        $wishList = WishList::where('user_id', Auth::id())->get();
        if (count($wishList) != 0) {
            foreach ($wishList as $item) {
                $ids[] = $item->course_id;
            }
            $filter['course_ids'] = implode(',', $ids);
            $filter['limit'] = -1;
            $result = Course:: getListOfCourses($filter);
            return response()->json(['success' => true, 'data' => $result['data']]);

        }



        return response()->json(['success' => false, 'message' => __('messages.empty-wish')]);
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
    public function store(WishListsRequest $request)
    {
        $items = WishList::where('course_id', $request->course_id)->where('user_id',Auth::id())->get();
        if(count($items) != 0){
           return response()->json(['success' => false, 'message' => __('messages.already-in-wish-list')],404);

        }
        WishList::create([
            'user_id' => Auth::id(),
            'course_id' => $request->course_id
        ]);
        return response()->json(['success' => true, 'message' => __('messages.added-on-wish-list')], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WishList  $wishList
     * @return \Illuminate\Http\Response
     */
    public function show(WishList $wishList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WishList  $wishList
     * @return \Illuminate\Http\Response
     */
    public function edit(WishList $wishList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WishList  $wishList
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WishList $wishList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JSonResponse
     */
    public function destroy($id)
    {
       $item = WishList::query()->where('course_id', $id)->where('user_id',Auth::id())->first();
       if(!empty($item)){
           $item->delete();
           return response()->json(['success' => true, 'message' => __('messages.deleted')], 200);
       }else{
           return response()->json(['success' => false, 'message' => __('messages.forbidden')],403);

       }
    }
}
