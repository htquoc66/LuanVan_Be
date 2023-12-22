<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotarizedDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $customersA = $this->customers->where('pivot.description', 'customerA')->values();
        $customersB = $this->customers->where('pivot.description', 'customerB')->values();
        $manager = $this->users->where('pivot.description', 'manager')->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
            ];
        })->values();
        $notary = $this->users->where('pivot.description', 'notary')->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
            ];
        })->values();
        $accountant = $this->users->where('pivot.description', 'accountant')->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
            ];
        })->values();

        // Sử dụng method costs() để lấy ra các costs với điều kiện cost_type_id tương ứng
        $cost_1 = $this->costs->where('cost_type_id', 1)->values();
        $cost_2 = $this->costs->where('cost_type_id', 2)->values();
        $cost_3 = $this->costs->where('cost_type_id', 3)->values();

        return [
            'id' => $this->id,
            'category' => $this->category,
            'name' => $this->name,
            'file' => $this->file,
            'status' => $this->status,
            'date' => $this->date,
            'total_cost' => $this->total_cost,
            'customersA' => $customersA,
            'customersB' => $customersB,
            'manager' => $manager,
            'notary' => $notary,
            'accountant' => $accountant,
            'lawTexts' => $this->lawTexts,
            'status' => $this->status,
            'date' => $this->date,
            'reason' => $this->reason,

            'cost_1' => $cost_1,
            'cost_2' => $cost_2,
            'cost_3' => $cost_3,
        ];
    }
}
