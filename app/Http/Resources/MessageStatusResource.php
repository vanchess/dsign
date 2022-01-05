<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageStatusResource extends JsonResource
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
            'type'          => 'messageStatus',
            'id'            => (string)$this->id,
            'attributes'    => [
                'name' => $this->name,
                'lable' => $this->lable,
                'description' => $this->description,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ],
           // 'relationships' => new EmployeeRelationshipResource($this),
            'links'         => [
                'self' => route('msg-status.show', ['msg_status' => $this->id]),
            ],
            
        ];
    }
}
