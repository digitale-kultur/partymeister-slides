<?php

namespace Partymeister\Slides\Http\Controllers\Backend;

use Illuminate\Http\JsonResponse;
use Motor\Backend\Http\Controllers\Controller;
use Partymeister\Slides\Events\PlaylistRequest;
use Partymeister\Slides\Models\Playlist;
use Partymeister\Slides\Models\SlideClient;

/**
 * Class SlideClientsController
 * @package Partymeister\Slides\Http\Controllers\Backend
 */
class JsonPlaylistController extends Controller
{

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param  Playlist  $playlist
	 * @return JsonResponse
	 */
	public function show(Playlist $playlist)
	{
		$request = new PlaylistRequest($playlist, true);
		return response()->json($request);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param  SlideClient  $slideClient
	 * @return JsonResponse
	 */
	public function screen(SlideClient $slideClient)
	{
		$playlist = Playlist::find($slideClient->playlist_id);
		$request = new \stdClass();
		if($playlist) {
			$request = new PlaylistRequest($playlist, true);
		}
		return response()->json($request);
	}

}
