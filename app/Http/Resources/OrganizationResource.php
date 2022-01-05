<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
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
            'type'          => 'organization',
            'id'            => (string)$this->id,
            'attributes'    => [
                'name' => $this->name,
                'short_name' => $this->short_name,
                'description' => $this->description,
                'address' => $this->address,
                'inn' => $this->inn,
                'ogrn' => $this->ogrn,
                'kpp' => $this->kpp,
                'email' => $this->email,
                'phone' => $this->phone,
                'chief' => $this->chief,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ],
           // 'relationships' => new EmployeeRelationshipResource($this),
            'links'         => [
                'self' => route('organization.show', ['organization' => $this->id]),
            ],
            
        ];
    }
}