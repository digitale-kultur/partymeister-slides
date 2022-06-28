<?php

Route::get('slidemeister-web/{slide_client}', '\Partymeister\Slides\Http\Controllers\SlidemeisterWebController@index')
     ->middleware(['bindings'])
     ->name('backend.slidemeister-web.show');

Route::group([
    'as'         => 'component.',
    'prefix'     => 'component',
    'namespace'  => 'Partymeister\Slides\Http\Controllers\Backend\Component',
    'middleware' => [
        'web',
    ],
], function () {
    // You only need this part if you already have a component group for the given namespace
    Route::get('playlist-viewers', 'ComponentPlaylistViewersController@create')
         ->name('playlist-viewers.create');
    Route::post('playlist-viewers', 'ComponentPlaylistViewersController@store')
         ->name('playlist-viewers.store');
    Route::get('playlist-viewers/{component_playlist_viewer}', 'ComponentPlaylistViewersController@edit')
         ->name('playlist-viewers.edit');
    Route::patch('playlist-viewers/{component_playlist_viewer}', 'ComponentPlaylistViewersController@update')
         ->name('playlist-viewers.update');
});

Route::group([
    'as'         => 'backend.',
    'prefix'     => 'backend',
    'namespace'  => 'Partymeister\Slides\Http\Controllers\Backend',
    'middleware' => [
        'web',
        'web_auth',
        'navigation',
    ],
], function () {
    Route::group(['middleware' => ['permission']], function () {
        Route::resource('slides', 'SlidesController')
             ->except(['create', 'show']);
        Route::get('slides/{slide}/duplicate', 'SlidesController@duplicate')
             ->name('slides.duplicate');

        Route::get('slides/create/{slide_template}', 'SlidesController@create')
             ->name('slides.create');

        Route::resource('slide_templates', 'SlideTemplatesController')
             ->except('show');
        Route::get('slide_templates/{slide_template}/duplicate', 'SlideTemplatesController@duplicate')
             ->name('slide_templates.duplicate');

        Route::resource('playlists', 'PlaylistsController');
        Route::resource('transitions', 'TransitionsController');
        Route::resource('slide_clients', 'SlideClientsController');
        Route::get('slide_clients/{slide_client}/activate', 'SlideClientsController@activate')
             ->name('slide_clients.activate');

        Route::resource('files', 'FilesController');
    });
});

Route::group([
	             'as' => 'backend.',
	             'prefix' => 'backend',
	             'namespace' => 'Partymeister\Slides\Http\Controllers\Backend',
	             'middleware' => [
		             'web',
	             ],
             ], function () {
	Route::get('playlist/{playlist}/json', 'JsonPlaylistController@show');
	Route::get('playlist/screen/{slideClient}/json', 'JsonPlaylistController@screen');
});

Route::get('backend/slide_templates/{slide_template}.html', 'Partymeister\Slides\Http\Controllers\Backend\SlideTemplatesController@show')
     ->middleware(['bindings'])
     ->name('backend.slide_templates.show');
Route::get('backend/slides/{slide}.html', 'Partymeister\Slides\Http\Controllers\Backend\SlidesController@show')
     ->middleware([
         'bindings',
         'cache.headers:etag',
     ])
     ->name('backend.slides.show');

// FIXME: put these in controllers so we can use the Route caching
//Route::get('test-prizegiving', function() {
//    $xml = \Partymeister\Slides\Services\XMLService::send('playlist', array('playlist_id' => 148));
//    return response($xml, 200)
//        ->header('Content-Type', 'text/xml');    //echo $xml;
//});
//
//Route::get('xmlservice/playlist', function() {
//    //$result = XMLMeister::send('playlist', array('playlist_id' => arr::get($_GET, 'play'), 'callbacks' => arr::get($_GET, 'callbacks')));
//    $xml = \Partymeister\Slides\Services\XMLService::send('playlist', ['playlist_id' => \Partymeister\Slides\Models\Playlist::find(194)->id, 'callbacks' => 0], false, true);
//    return response($xml, 200)
//        ->header('Content-Type', 'text/xml');    //echo $xml;
//});
//
//Route::get('xmlservice/next', function() {
//    $xml = \Partymeister\Slides\Services\XMLService::send('next', ['hard' => true], false, true);
//    return response($xml, 200)
//        ->header('Content-Type', 'text/xml');    //echo $xml;
//});
//
//Route::get('xmlservice/previous', function() {
//    $xml = \Partymeister\Slides\Services\XMLService::send('previous', ['hard' => true], false, true);
//    return response($xml, 200)
//        ->header('Content-Type', 'text/xml');    //echo $xml;
//});
//
//Route::get('xmlservice/getplaylists', function() {
//    $xml = \Partymeister\Slides\Services\XMLService::send('get_playlists', ['hard' => true], false, true);
//    return response($xml, 200)
//        ->header('Content-Type', 'text/xml');    //echo $xml;
//});
