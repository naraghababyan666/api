<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
         $userData =  parent::toArray($request);
         if(isset($userData["updated_at"])){
             unset($userData["updated_at"]);
         }
         if(isset($userData["created_at"])){
             unset($userData["created_at"]);
         }
         if(isset($userData["email_verified_at"])){
             unset($userData["email_verified_at"]);
         }
        return $userData;
    }
}
