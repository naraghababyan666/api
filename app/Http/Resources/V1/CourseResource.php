<?php

namespace App\Http\Resources\V1;

use App\Models\Course;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $data =  parent::toArray($request);
        $del = ["path","links","first_page_url", "prev_page_url", "last_page_url", "next_page_url","from","to","last_page"];
        foreach ($del as  $value) {
            if(isset($data[$value])){
                unset($data[$value]);
            }
        }
        return $data;
    }
}
