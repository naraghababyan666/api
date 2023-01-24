<?php

namespace App\Http\Controllers\api\V1\trainer;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\TrainerMetaRequest;
use App\Models\Role;
use App\Models\Trainer;
use App\Models\TrainerMeta;
use \App\Models\User;
use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isNull;

class TrainerMetaController extends Controller
{
    public function saveTrainerMeta(TrainerMetaRequest $request){
        $data = $request->validated();
        if(Auth::user()->role_id == Role::STUDENT){
            $user = Auth::user();
            $user->phone = $request->all()['phone'];
            $user->first_name = $request->all()['first_name'];
            $user->last_name = $request->all()['last_name'];
            $user->save();
            return response()->json(['success' => true, 'data' => $user]);
        }else{
            $trainerData = TrainerMeta::query()->where('user_id', Auth::id())->with('user')->first();
            if(isset($request->all()['links'])){
                foreach ( $request->all()['links'] as $item => $value){
                    if(!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)){
                        return response()->json(['success' => false, 'message' => __('validation.url', $item)]);
                    }
                }
            }
            if(is_null($trainerData)){
                $newData = TrainerMeta::create([
                    'user_id' => Auth::id(),
                    'headline' => $data['headline']??null,
                    'bio' => $data['bio']??null,
                    'links' => json_encode($request->all()['links'])??null
                ]);

                $user = User::query()->where('id', $newData['user_id'])->select('id', 'first_name', 'last_name', 'email', 'avatar',"role_id","company_name","tax_identity_number")->first();

                $user->first_name = $request->first_name ?? $user->first_name;
                $user->last_name = $request->last_name ?? $user->last_name;
                $user->tax_identity_number = $request->tax_identity_number ?? $user->tax_identity_number;
                $user->company_name = $request->company_name ?? $user->company_name;
                $user->phone = $request->phone ?? $user->phone;
                $user->save();
                $info = TrainerMeta::query()->where('id', '=', $newData->id)->first();
                return $this->extracted($user, $info);
            }else{
                $trainerData->update([
                    'headline' => $data['headline']??$trainerData->headline,
                    'bio' => $data['bio']??$trainerData->bio,
                ]);
                if(isset($request->all()['links'])){
                    $trainerData->update(['links' => json_encode($request->all()['links'])??$trainerData->links]);
                }
                $trainerData->save();

                $user = User::query()->where('id', Auth::id())->select('id', 'first_name', 'last_name', 'email', 'avatar',"role_id","company_name","tax_identity_number")->first();
                $user->first_name = $request->first_name ?? $user->first_name;
                $user->last_name = $request->last_name ?? $user->last_name;
                $user->tax_identity_number = $request->tax_identity_number ?? $user->tax_identity_number;
                $user->company_name = $request->company_name ?? $user->company_name;
                $user->phone = $request->phone ?? $user->phone;
                $user->save();
                $info = TrainerMeta::query()->where('id', '=', $trainerData->id)->first();
                return $this->extracted($user, $info);
            }
        }

    }

    public function getTrainerMeta($id){
        $data = TrainerMeta::query()->where('user_id', $id)->first();
        $user = User::query()->where('id', '=', $id)->select('email', 'first_name', 'phone', 'last_name', 'avatar','role_id', 'company_name', 'tax_identity_number')->first();
        if(is_null($data)){
            if(is_null($user)){
                return response()->json(['success' => false, 'message' => __('messages.user_not_found')]);
            }
            $data =  TrainerMeta::create([
                'user_id' => $id,
                'headline' => null,
                'bio' => null,
                'links' => null
            ]);
        }
        $trainers = [];
        $trainerList = Trainer::where('user_id', $id)->get();
        foreach ($trainerList as $trainer){
            $trainerInfo = [
                'first_name' => $trainer['first_name'],
                'last_name' => $trainer['last_name'],
                'bio' => $trainer['bio'],
                'avatar' =>$trainer['avatar'],
            ];
            $trainers[] = $trainerInfo;
        }
        $data['trainers'] = $trainers;
        $data['links'] = json_decode($data['links'],true);
        $data['email'] = $user['email'];
        $data['first_name'] = $user['first_name'];
        $data['phone'] = $user['phone'];
        $data['last_name'] = $user['last_name'];
        $data['avatar'] = $user['avatar']?env("APP_URL")."/".$user['avatar']:null;
        $data['avatar_path'] = $user['avatar'];
        $data['role_id'] = $user['role_id'];
        $data['company_name'] = $user['company_name'];
        $data['tax_identity_number'] = $user['tax_identity_number'];

        return response()->json(['success' => true,'data' => $data]);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model|object|\Illuminate\Database\Eloquent\Builder|null $user
     * @param \Illuminate\Database\Eloquent\Model|object|\Illuminate\Database\Eloquent\Builder|null $info
     * @return \Illuminate\Http\JsonResponse
     */
    public function extracted(\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder|null $user, \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder|null $info): \Illuminate\Http\JsonResponse
    {
        $info['user_id'] = $user['id'];
        $info['first_name'] = $user['first_name'];
        $info['last_name'] = $user['last_name'];
        $info['email'] = $user['email'];
        $info['phone'] = $user['phone'];
        $info['avatar'] = $user['avatar']?env("APP_URL")."/".$user['avatar']:null;
        $info['role_id'] = $user['role_id'];

        if($info['role_id'] == 4){
            $info['company_name'] = $user['company_name'];
            $info['tax_identity_number'] = $user['tax_identity_number'];
        }

        return response()->json(['success' => true, 'data' => $info]);
    }
}
