<div class="d-none float-right playlist-{{$record->id}}-preview playlist-preview">
    <img class="img-thumbnail" style="max-width: 200px;">
</div>
@if (session('screens.active', null) == null)
    {{trans('partymeister-slides::backend/slide_clients.no_active_client')}}
@else
    <ul class="list-unstyled">
        <li>
            <a href="#" class="slide-clients-play"
               data-playlist="{{$record->id}}" data-action="cache" data-callbacks="0">{{trans('partymeister-slides::backend/slide_clients.cache')}}</a>
        </li>
        <li>
            <a href="#" class="slide-clients-play"
               data-playlist="{{$record->id}}" data-action="seek" data-callbacks="1">{{trans('partymeister-slides::backend/slide_clients.play_with_callbacks')}} {{trans('partymeister-slides::backend/slide_clients.restart_playlist')}}</a>
        </li>
        <li>
            <a href="#" class="slide-clients-play"
               data-playlist="{{$record->id}}" data-action="none" data-callbacks="1">{{trans('partymeister-slides::backend/slide_clients.play_with_callbacks')}}</a>
        </li>
        <li>
            <a href="#" class="slide-clients-play"
               data-playlist="{{$record->id}}" data-action="seek" data-callbacks="0">{{trans('partymeister-slides::backend/slide_clients.play_without_callbacks')}} {{trans('partymeister-slides::backend/slide_clients.restart_playlist')}}</a>
        </li>
        <li>
            <a href="#" class="slide-clients-play"
               data-playlist="{{$record->id}}" data-action="none" data-callbacks="0">{{trans('partymeister-slides::backend/slide_clients.play_without_callbacks')}}</a>
        </li>
    </ul>
@endif
<div class="playlist-{{$record->id}}-cached playlist-json">
    <a href="/backend/playlist/{{$record->id}}/json" target="_blank" class="slide-clients-json">{{trans('partymeister-slides::backend/slide_clients.json_playlist')}}</a>
</div>
<div class="d-none playlist-{{$record->id}}-cached playlist-cached">
    <strong>{{trans('partymeister-slides::backend/slide_clients.cached')}}</strong>
</div>
<div class="d-none playlist-{{$record->id}}-outdated playlist-outdated" data-timestamp="{{strtotime($record->updated_at)}}">
    <strong>{{trans('partymeister-slides::backend/slide_clients.outdated')}}</strong>
</div>
<div class="d-none playlist-{{$record->id}}-playing playlist-playing">
    <strong>{{trans('partymeister-slides::backend/slide_clients.playing')}}</strong>
</div>
