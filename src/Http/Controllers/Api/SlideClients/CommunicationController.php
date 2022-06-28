<?php

namespace Partymeister\Slides\Http\Controllers\Api\SlideClients;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Motor\Backend\Http\Controllers\Controller;
use Partymeister\Slides\Events\PlaylistNextRequest;
use Partymeister\Slides\Events\PlaylistPreviousRequest;
use Partymeister\Slides\Events\PlaylistRequest;
use Partymeister\Slides\Events\PlaylistSeekRequest;
use Partymeister\Slides\Events\PlayNowRequest;
use Partymeister\Slides\Events\SiegmeisterRequest;
use Partymeister\Slides\Models\Playlist;
use Partymeister\Slides\Models\SlideClient;
use Partymeister\Slides\Services\XMLService;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class CommunicationController
 *
 * @package Partymeister\Slides\Http\Controllers\Api\SlideClients
 */
class CommunicationController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function playlist(Request $request)
    {
        $client = SlideClient::find(session('screens.active'));

        if (is_null($client)) {
            return response()->json(['message' => 'No slide client active'], 400);
        }

	    $playlistId = $request->get('playlist_id');
        switch ($client->type) {
            case 'screens':
                $result = XMLService::send('playlist', $request->all());
                if (! $result) {
                    return response()->json(['result' => $result], 400);
                } else {
	                $client->playlist_id = $playlistId;
	                $client->save();
                    return response()->json(['result' => $result]);
                }
                break;
            case 'slidemeister-web':
	            $playlist = Playlist::find($playlistId);
                if (is_null($playlist)) {
                    return response()->json(['message' => 'Playlist not found'], 400);
                }
                event(new PlaylistRequest($playlist, $request->get('callbacks')));

	            $client->playlist_id = $playlistId;
	            $client->save();
                return response()->json(['result' => 'Playlist event sent']);
                break;
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function playnow(Request $request)
    {
        $client = SlideClient::find(session('screens.active'));

        if (is_null($client)) {
            return response()->json(['message' => 'No slide client active'], 400);
        }

        switch ($client->type) {
            case 'screens':
                $result = XMLService::send('playnow', $request->all());
                if (! $result) {
                    return response()->json(['result' => $result], 400);
                } else {
                    return response()->json(['result' => $result]);
                }
                break;
            case 'slidemeister-web':
                if (Arr::get($request->all(), 'file_id') != null) {
                    $type = 'file';
                    $item = Arr::get($request->all(), 'file_id');
                } else {
                    $type = 'slide';
                    $item = Arr::get($request->all(), 'slide_id');
                }

                event(new PlayNowRequest($type, $item));

                return response()->json(['result' => 'PlayNow event sent']);
                break;
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function seek(Request $request)
    {
        $client = SlideClient::find(session('screens.active'));

        if (is_null($client)) {
            return response()->json(['message' => 'No slide client active'], 400);
        }

        switch ($client->type) {
            case 'screens':
                $result = XMLService::send('seek', $request->all());
                if (! $result) {
                    return response()->json(['result' => $result], 400);
                } else {
                    return response()->json(['result' => $result]);
                }
                break;
            case 'slidemeister-web':
                $playlist = Playlist::find($request->get('playlist_id'));
                if (is_null($playlist)) {
                    return response()->json(['message' => 'Playlist not found'], 400);
                }
                event(new PlaylistSeekRequest($playlist, 0));

                return response()->json(['result' => 'Seek event sent']);
                break;
        }
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function skip(Request $request)
    {
        $client = SlideClient::find(session('screens.active'));

        if (is_null($client)) {
            return response()->json(['message' => 'No slide client active'], 400);
        }

        switch ($client->type) {
            case 'screens':
                $result = XMLService::send($request->get('direction'), ['hard' => $request->get('hard')]);
                if (! $result) {
                    return response()->json(['result' => $result], 400);
                } else {
                    return response()->json(['result' => $result]);
                }
            // no break
            case 'slidemeister-web':
                switch ($request->get('direction')) {
                    case 'previous':
                        event(new PlaylistPreviousRequest($request->get('hard', false)));
                        break;
                    case 'next':
                        event(new PlaylistNextRequest($request->get('hard', false)));
                        break;
                }

                return response()->json(['result' => 'Skip event sent']);
                break;
        }
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function siegmeister(Request $request)
    {
        $client = SlideClient::find(session('screens.active'));

        if (is_null($client)) {
            return response()->json(['message' => 'No slide client active'], 400);
        }

        switch ($client->type) {
            case 'screens':
                // NO SUPPORT
                break;
            case 'slidemeister-web':
                event(new SiegmeisterRequest());

                return response()->json(['result' => 'Siegmeister event sent']);
                break;
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get_system_info(Request $request)
    {
        $result = XMLService::send('get_system_info');
        if (! $result) {
            return response()->json(['result' => $result], 400);
        } else {
            return response()->json(['result' => $result]);
        }
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function get_playlists(Request $request)
    {
        $client = SlideClient::find(session('screens.active'));

        if (is_null($client)) {
            return response()->json(['message' => 'No slide client active'], 400);
        }

        switch ($client->type) {
            case 'screens':
                $result = XMLService::send('get_playlists');
                if (! $result) {
                    return response()->json(['result' => $result], 400);
                } else {
                    return response()->json(['result' => $result]);
                }
                break;
            case 'slidemeister-web':
                $result = Cache::store('redis')
                               ->get(config('cache.prefix').':slidemeister-web.'.session('screens.active'));
                if (! $result) {
                    return response()->json(['result' => $result], 400);
                } else {
                    return response()->json(['result' => $result]);
                }
                break;
        }
    }
}
