<?php

namespace Partymeister\Slides\Http\Resources;

use Illuminate\Support\Str;
use Motor\Backend\Http\Resources\BaseResource;
use Partymeister\Competitions\Http\Resources\CompetitionResource;
use Partymeister\Competitions\Http\Resources\EntryResource;
use Partymeister\Competitions\Models\Competition;
use Partymeister\Competitions\Models\Entry;
use Partymeister\Competitions\Services\VoteService;

/**
 * @OA\Schema(
 *   schema="PlaylistResource",
 *   @OA\Property(
 *     property="id",
 *     type="integer",
 *     example="1"
 *   ),
 *   @OA\Property(
 *     property="name",
 *     type="string",
 *     example="Main rotation"
 *   ),
 *   @OA\Property(
 *     property="type",
 *     type="string",
 *     example="video"
 *   ),
 *   @OA\Property(
 *     property="is_competition",
 *     type="boolean",
 *     example="false"
 *   ),
 * )
 */
class PlaylistResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
		$data = [
			'id'             => (int) $this->id,
			'name'           => $this->name,
			'type'           => $this->type,
			'is_competition' => (boolean) $this->is_competition,
			'competition_id' => $this->competition_id,
			'playlist_role' => $this->getPlaylistRole(),
			'items'          => PlaylistItemResource::collection($this->items),
			'updated_at'     => $this->updated_at,
		];
	    if ($this->is_competition && $this->competition_id) {
		    $competition = Competition::where('id', $this->competition_id)
		                              ->first();
		    if ($competition) {
			    $data['competition'] = new CompetitionResource($competition);
			    $data['entries'] = [];
			    foreach (
				    $competition->entries()->where('status', 1)
				                ->orderBy('sort_position', 'ASC')->get() as $entry
			    ) {
				    $data['entries'][] = new EntryResource($entry);
			    }
		    }
	    }else if($this->playlistIsPrizegiving()) {
		    $results      = VoteService::getAllVotesByRank('ASC');
		    $specialVotes = VoteService::getAllSpecialVotesByRank();

		    foreach ($specialVotes as $entryKey => $entry) {
			    if ($entryKey > 6) {
				    unset($specialVotes[$entryKey]);
			    }
		    }
		    shuffle($specialVotes);

		    $comments = [];
		    foreach ($results as $competition) {
			    $comments[$competition['id']] = [];
			    foreach ($competition['entries'] as $entry) {
				    foreach ($entry['comments'] as $comment) {
					    if (strlen($comment) < 30) {
						    $comments[$competition['id']][] = $comment;
						    $comments[$competition['id']]   = array_unique($comments[$competition['id']]);
					    }
				    }
			    }
			    shuffle($comments[$competition['id']]);
			    $chunks = array_chunk($comments[$competition['id']], 8);
			    if (count($chunks) > 0) {
				    $comments[$competition['id']] = $chunks[0];
			    } else {
				    $comments[$competition['id']] = [];
			    }
			    $comments[$competition['id']] = implode('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
			                                            $comments[$competition['id']]);
		    }
		    $fullResults = [];
		    foreach($results as $key => $competition) {
			    foreach($competition['entries'] as $entryKey => $entryData) {
				    $entry = Entry::where('id', $entryData['id'])->first();
				    if($entry) {
					    $entryData['playable_files'] = $this->getPlayableFileInfo($entry);
				    }
				    $competition['entries'][$entryKey] = $entryData;
			    }
			    $results[$key] = $competition;
		    }
		    $data['results'] = array_values($results);
	    }
	    return $data;
    }

	protected function getPlaylistRole()
	{
		$role = "rotation";
		if($this->is_competition && $this->competition_id) {
			$role = "competition";
		}else if($this->playlistIsPrizegiving()) {
			$role = "prizegiving";
		}
		return $role;
	}

	protected function playlistIsPrizegiving() {
		return $this->is_prizegiving && $this->is_prizegiving !== "null";
	}

	protected function getPlayableFileInfo(Entry $entry) {
		$name = $entry->playable_file_name;
		$path = base_path('entries/' . Str::slug($entry->competition->name));
		$directory = '/entries/' . Str::slug($entry->competition->name);
		$entryDir = $entry->id;
		while (strlen($entryDir) < 4) {
			$entryDir = '0' . $entryDir;
		}

		$entryDir .= '/files';

		$location = $path . "/" . $entryDir . "/" . $name;
		$data = [
			"name" => basename($name),
			"path" => $location,
			"url" => $directory . "/" . $entryDir . "/" . $name
		];
		if(file_exists($location)) {
			$data['size'] = \filesize($location);
			$data['created'] = date('Y-m-d H:i:s', filectime($location));
			$data['mime_type'] = mime_content_type($location);
		}
		return $data;
	}
}
