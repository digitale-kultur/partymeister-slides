<?php

namespace Partymeister\Slides\Http\Resources;

use Motor\Backend\Http\Resources\BaseResource;
use Motor\Backend\Http\Resources\CategoryResource;
use Motor\Backend\Http\Resources\MediaResource;
use Partymeister\Competitions\Http\Resources\EntryResource;
use Partymeister\Competitions\Models\Competition;
use Partymeister\Competitions\Models\Entry;

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
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
		$slide = [
			'id'                  => (int) $this->id,
			'name'                => $this->name,
			'slide_template'      => new SlideTemplateResource($this->slide_template),
			'slide_type'          => $this->slide_type,
			'category_id'         => $this->category_id,
			'category_name'       => (!is_null($this->category) ? $this->category->name : ''),
			'category'            => new CategoryResource($this->category),
			'definitions'         => json_decode($this->definitions),
			'cached_html_preview' => $this->cached_html_preview,
			'cached_html_final'   => $this->cached_html_final,
			'file_final'          => new MediaResource($this->getFirstMedia('final')),
			'file_preview'        => new MediaResource($this->getFirstMedia('preview')),
			'file'                => new MediaResource($this->getFirstMedia('preview')),
			'additionals'         => $this->getAdditionalSlideData()
		];

	    return $slide;
    }

    private function getAdditionalSlideData()
    {
	    $data = [];
	    if($this->slide_type === "compo") {
				$definitions = json_decode($this->definitions);
			    if($this->category->competition_id) {
				    $competition = Competition::where('id', $this->category->competition_id)->first();
			    }else{
				    // fallback to association by name if compoid is not set
				    $competition = Competition::where('name', $this->category->name)->first();
			    }
				if($competition) {
					foreach($definitions->elements as $key => $element) {
						if($element->properties->placeholder == "<<sort_position_prefixed>>") {
							$sortPosition = (int) ltrim( $element->properties->content, "0");
							/** @var Entry $entry */
							$entry = Entry::where('competition_id', (int) $competition->id)->where('sort_position', $sortPosition)->first();
							if($entry) {
								$data['entry'] = new EntryResource($entry);
							}
						}
					}
				}
	    }
	    return $data;
    }



}
