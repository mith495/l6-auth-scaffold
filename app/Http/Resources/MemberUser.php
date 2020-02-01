<?php

namespace App\Http\Resources;

use Illuminate\Support\Arr;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberUser extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->user_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'active' => Arr::get($this, 'active', false),
            'roles' => Arr::get($this, 'roles', []),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
