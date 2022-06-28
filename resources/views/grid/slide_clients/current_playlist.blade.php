@if ($record->playlist)
{{$record->playlist->name}}<br/><br/>
<a href="/backend/playlist/screen/{{$record->id}}/json" target="_blank">Current Playlist JSON</a>
@endif
