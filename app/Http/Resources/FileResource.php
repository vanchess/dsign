<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
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
            'type'          => 'file',
            'id'            => (string)$this->id,
            'attributes'    => [
                'name' => $this->name,
                'user_id' => $this->user_id,
                'description' => $this->description,
                'link' => route('fileDownload', ['id' => $this->id]),
                'linkPdf' => route('filePdfDownload', ['id' => $this->id]),
                'linkStamped' => route('fileStampedDownload', ['id' => $this->id]),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ],
           // 'relationships' => new EmployeeRelationshipResource($this),
            'links'         => [
                'self' => route('fileDownload', ['id' => $this->id]),
            ],
            
        ];
    }
}
