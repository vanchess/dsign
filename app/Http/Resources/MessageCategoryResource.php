<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'type'          => 'messageCategory',
            'id'            => (string)$this->id,
            'attributes'    => [
                'name' => $this->name,
                'title' => $this->title,
                'short_title' => $this->short_title,
                'type_id' => $this->type_id,
                'description' => $this->description,
                'order' => $this->order,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ],
           // 'relationships' => new EmployeeRelationshipResource($this),
           // 'links'         => [
           //     'self' => route('users.show', ['user' => $this->id]),
           // ],
            
        ];
    }
}