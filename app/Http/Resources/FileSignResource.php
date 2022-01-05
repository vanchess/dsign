<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FileSignResource extends JsonResource
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
            'type'          => 'file-sign',
            'id'            => (string)$this->id,
            'attributes'    => [
                'user_id' => $this->user_id,
                'file_id' => $this->file_id,
                'verified_on_server_at' => $this->verified_on_server_at,
                'verified_on_server_error_srt' => $this->verified_on_server_error_srt,
                'verified_on_server_success' => $this->verified_on_server_success,
                'base64' => $this->base64,
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