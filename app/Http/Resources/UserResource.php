<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'type'          => 'user',
            'id'            => (string)$this->id,
            'attributes'    => [
                'name' => $this->name,
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'job_title' => $this->job_title,
                'branch' => $this->branch,
                'email' => $this->email,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ],
           // 'relationships' => new EmployeeRelationshipResource($this),
            'links'         => [
                'self' => route('users.show', ['user' => $this->id]),
            ],
            
        ];
    }
}
