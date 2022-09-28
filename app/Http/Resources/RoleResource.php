<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'type'          => 'role',
            'id'            => (string)$this->id,
            'attributes'    => [
                'name' => $this->name,
            ],
           // 'relationships' => new EmployeeRelationshipResource($this),
            'links'         => [
           //     'self' => route('users.show', ['user' => $this->id]),
            ],

        ];
    }
}
