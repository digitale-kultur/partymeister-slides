<?php

namespace Partymeister\Slides\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *   schema="SlideClientResource",
 *   @OA\Property(
 *     property="id",
 *     type="integer",
 *     example="1"
 *   )
 * )
 */

class SlideClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
