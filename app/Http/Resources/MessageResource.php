<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Http\Resources\MessageCategoryCollection;
use App\Http\Resources\MessageStatusResource;
use App\Http\Resources\OrganizationResource;

class MessageResource extends JsonResource
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
            'type'          => 'message',
            'id'            => (string)$this->id,
            'attributes'    => [
                'subject' => $this->subject,
                'text' => $this->text,
                'user_id' => $this->user_id,
                'status_id' => $this->status_id,
                'organization_id' => $this->organization_id,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ],
            'relationships' => [
                'to_users' => [
                    'data' => new UserCollection($this->whenLoaded('to')),
                ],
                'user' => [
                    'data' => new UserResource($this->from),
                ],
                'category' => [
                    'data' => new MessageCategoryCollection($this->category),
                ],
                'status' => [
                    'data' => new MessageStatusResource($this->status),
                ],
                'organization' => [
                    'data' => new OrganizationResource($this->organization),
                ],
            ],
            'links'         => [
                'self' => route('msg.show', ['msg' => $this->id]),
            ],

        ];
    }
}
