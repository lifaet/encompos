<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'category_name' => $this->category->name ?? null, // added
            'brand_id' => $this->brand_id,
            'brand_name' => $this->brand->name ?? null,       // added
            'unit_id' => $this->unit_id,
            'price' => $this->price,
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'purchase_price' => $this->purchase_price,
            'quantity' => $this->quantity,
            'expire_date' => $this->expire_date,
            'status' => $this->status,
            'discounted_price' => $this->discounted_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
