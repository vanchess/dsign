<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PeriodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            'type'          => 'period',
            'id'            => (string)$this->id,
            'attributes'    => [
                'name' => $this->to->format('m.y'),
                'year' => $this->to->format('Y'),
                'from' => $this->from,
                'to' => $this->to
            ],
           // 'relationships' => new EmployeeRelationshipResource($this),
            'links'         => [
                'self' => route('period.show', ['period' => $this->id]),
            ],
            
        ];
    }
}
