<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'type' => $this->CustomerType->name,
            'name' => $this->name,
            'idCard_number' => $this->idCard_number,
            'idCard_issued_date' => $this->idCard_issued_date,
            'idCard_issued_place' => $this->idCard_issued_place,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'avatar' => $this->avatar,
        ];    
    }
}
