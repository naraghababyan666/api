<?php

namespace App\Http\Controllers\api\V1\general;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseHelpers;
use App\Mail\UpstartMail;
use App\Models\Notification;
use App\Notifications\UpstartNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;


class NotificationController extends Controller
{
    use ApiResponseHelpers;
    public function getNotifications()
    {
        if (Auth::check()) {
            $notifications = Notification::query()->where("user_id", auth()->id())->orderBy('created_at', 'desc')->get();
            $unread_count = 0;
            foreach ($notifications as $not){
                if($not['status'] == 0){
                    $unread_count++;
                }
            }
            return response()->json(['success' => true, 'count' => $unread_count, 'data' => $notifications]);

        } else {
            return response()->json([
                'success' => false,
                'message' => __("messages.forbidden"),
            ], 403)->header('Status-Code', '403');
        }
    }

    public function getUnreadNotificationsCount(){
        cache()->flush();
        $notificationsCount = Notification::query()->where('user_id', Auth::id())->where('status', 0)->count();
        return response()->json(['success' => true, 'unread' => $notificationsCount]);
    }

    public function getLastNotifications(){
        $notifications = Notification::query()->where('user_id', Auth::id())->orderByDesc('created_at')->select('id', 'title', 'message', 'type', 'created_at', 'status', 'item', 'item_id')->get();
        $count = count($notifications);
        foreach ($notifications as $notification){
            if($notification['type'] == 'approved'){
                $notification['url'] = 'course/'.$notification['item_id'];
            }else if($notification['type'] == 'declined'){
                $notification['url'] = 'dashboard/courses/create/'.$notification['item_id'];
            }
        }
        $notifications = array_slice(json_decode(json_encode($notifications)), 0, 5);

        return response()->json(['success' => true, 'notifications'=> $notifications, 'count' => $count]);
    }


    public function getNewNotifications()
    {
        $notifications = Notification::query()->where("user_id", auth()->id())->where("status", Notification::NEW_NOTIFICATION)->get();
//            return response()->json([
//                'success' => true,
//                'data' => $notifications,
//            ])->header('Status-Code', '200');
        return $this->respondWithSuccess($notifications);
    }

    public function removeNotification($id){
        $notification = Notification::query()->where('id', '=', $id)->where('user_id', '=', Auth::id())->first();
        if(is_null($notification)){
            return response()->json(['success' => false, 'message' => __('messages.notification-not-found')], 402);
        }
        $notification->delete();
        return response()->json(['success' => true, 'message' => __('messages.notification-remove')]);
    }

    public function markAsRead(){
        $notification = Notification::query()->where('user_id', Auth::id())->get();
        if(count($notification)!== 0){
            Notification::query()->where('user_id', Auth::id())->update(['status' => 1]);
            return response()->json(['success' => true, 'message' => __('messages.notification-marked-as-read')]);
        }
        return response()->json(['success' => false, 'message' => __('messages.no-notifications')]);
    }

    public static function store($data)
    {
        $notification =  Notification::create($data);
        if($data["type"]=="email"){
            Mail::to($data['email'])->send(new UpstartMail($data));
        }
        return $notification;
    }

    public static function changeNotificationStatus(Request $request){
        $notification = Notification::where('id', $request['id'])->where('user_id', Auth::id())->first();
        if($notification){
            if($notification->status == 0){
                $notification->status = 1;
                $notification->save();
                return response()->json(['success'=> true, 'message' => __('messages.notification-status-changed')]);
            }else{
                return response()->json(['success'=> true, 'message' => __('messages.notification-status-changed')]);
            }
        }else{
            return response()->json(['success'=> false, 'message' => __('messages.notification-not-found')]);
        }
    }
}
