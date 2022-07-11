<?php

namespace Partymeister\Slides\Http\Resources;

use Motor\Backend\Http\Resources\BaseResource;
use Motor\Backend\Http\Resources\CategoryResource;
use Motor\Backend\Http\Resources\MediaResource;

/**
 * @OA\Schema(
 *   schema="SlideResource",
 *   @OA\Property(
 *     property="id",
 *     type="integer",
 *     example="1"
 *   ),
 *   @OA\Property(
 *     property="name",
 *     type="string",
 *     example="My first slide"
 *   ),
 *   @OA\Property(
 *     property="slide_template",
 *     type="object",
 *     ref="#/components/schemas/SlideTemplateResource"
 *   ),
 *   @OA\Property(
 *     property="slide_type",
 *     type="string",
 *     example="announce"
 *   ),
 *   @OA\Property(
 *     property="category",
 *     type="object",
 *     ref="#/components/schemas/CategoryResource"
 *   ),
 *   @OA\Property(
 *     property="definitions",
 *     type="json",
 *     example="{}"
 *   ),
 *   @OA\Property(
 *     property="cached_html_preview",
 *     type="string"
 *   ),
 *   @OA\Property(
 *     property="cached_html_final",
 *     type="string"
 *   ),
 *   @OA\Property(
 *     property="file_final",
 *     type="object",
 *     ref="#/components/schemas/MediaResource"
 *   ),
 *   @OA\Property(
 *     property="file_preview",
 *     type="object",
 *     ref="#/components/schemas/MediaResource"
 *   ),
 * )
 */
class SlideResource extends BaseResource
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
            'id'                  => (int) $this->id,
            'name'                => $this->name,
            'slide_template'      => new SlideTemplateResource($this->slide_template),
            'slide_type'          => $this->slide_type,
            'category'            => new CategoryResource($this->category),
            'definitions'         => json_decode($this->definitions),
            'cached_html_preview' => $this->cached_html_preview,
            'cached_html_final'   => $this->cached_html_final,
            'file_final'          => new MediaResource($this->getFirstMedia('final')),
            'file_preview'        => new MediaResource($this->getFirstMedia('preview'))
        ];
    }
}
