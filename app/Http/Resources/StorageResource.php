<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StorageResource extends JsonResource
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
            'id' => $this->id,
            'file' => $this->file,
            'zip_password' => $this->zip_password,
            'category' => $this->notarizedDocument->category,
            'notarized_document' => $this->notarizedDocument,

         
        ];
    }
}
