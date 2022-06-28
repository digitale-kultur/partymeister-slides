<?php

namespace Partymeister\Slides\Services;

use Illuminate\Support\Arr;
use Motor\Backend\Models\Category;
use Motor\Backend\Services\BaseService;
use Motor\Core\Filter\Renderers\SelectRenderer;
use Motor\Media\Models\File;
use Motor\Media\Models\FileAssociation;
use Partymeister\Competitions\Helpers\CallbackHelper;
use Partymeister\Competitions\Models\Entry;
use Partymeister\Slides\Helpers\ScreenshotHelper;
use Partymeister\Slides\Models\Playlist;
use Partymeister\Slides\Models\PlaylistItem;
use Partymeister\Slides\Models\Slide;
use Partymeister\Slides\Models\Transition;

/**
 * Class PlaylistService
 */
class PlaylistService extends BaseService
{
    /**
     * @var string
     */
    protected $model = Playlist::class;

    /**
     * @param $data
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig
     */
    public static function generatePrizegivingPlaylist($data)
    {
        ini_set('max_execution_time', 1200);
        // 1. find out if we have an existing playlist and delete it
        $playlists = Playlist::where('name', 'Prizegiving: Actual prizegiving with winners')
                             ->get();
        foreach ($playlists as $playlist) {
            foreach ($playlist->items as $item) {
                if ($item->slide != null) {
                    $item->slide->delete();
                }
            }
            $playlist->delete();
        }

        // 2. create a slide category for this competition in case it does not exist yet
        $category = Category::where('scope', 'slides')
                            ->where('name', 'Prizegiving (actual)')
                            ->first();
        if (is_null($category)) {
            $rootNode = Category::where('scope', 'slides')
                                ->where('_lft', 1)
                                ->first();
            if (is_null($rootNode)) {
                exit('Root node for slide category tree does not exist');
            }
            $c = new Category();
            $c->scope = 'slides';
            $c->name = 'Prizegiving (actual)';
            $rootNode->appendNode($c);
            $category = Category::where('scope', 'slides')
                                ->where('name', 'Prizegiving (actual)')
                                ->first();
        }

        // 4. create playlist
        $playlist = Playlist::where('name', 'Prizegiving: Actual prizegiving with winners')
                            ->first();
        if (is_null($playlist)) {
            $playlist = new Playlist();
        }
        $playlist->name = 'Prizegiving: Actual prizegiving with winners';
	    $playlist->is_prizegiving = true;
        $playlist->type = 'video';
        $playlist->save();

        // 3. save slides
        $count = 0;
        $slideIds = [];

        if (config('partymeister-slides.screenshots')) {
            $browser = new ScreenshotHelper();
        }

        foreach (Arr::get($data, 'slide', []) as $slideName => $definitions) {
            $count++;
            $type = Arr::get($data, 'type.'.$slideName);
            $name = Arr::get($data, 'name.'.$slideName);
            $meta = Arr::get($data, 'meta.'.$slideName, '{}');
            $slideType = config('partymeister-competitions-slides.'.$type.'.slide_type', 'default');
            $midiNote = config('partymeister-competitions-slides.'.$type.'.midi_note', 0);
            $transitionIdentifier = config('partymeister-competitions-slides.'.$type.'.transition', 0);
            $transitionDuration = config('partymeister-competitions-slides.'.$type.'.transition_duration', 2000);
            $duration = config('partymeister-competitions-slides.'.$type.'.duration', 20);
            $isAdvancedManually = config('partymeister-competitions-slides.'.$type.'.is_advanced_manually', true);

            $transition = Transition::where('identifier', $transitionIdentifier)
                                    ->first();

            $callback = null;

            switch ($type) {
                case 'comingup':
                    $callback = CallbackHelper::prizegivingStarts();
                    break;
            }

            $s = new Slide();
            $s->category_id = $category->id;
            $s->name = $name;
            $s->slide_type = $slideType;
            $s->definitions = stripslashes($definitions);
            $s->cached_html_preview = Arr::get($data, 'cached_html_preview.'.$slideName, '');
            $s->cached_html_final = Arr::get($data, 'cached_html_final.'.$slideName, '');

            $s->save();

//            $s->addMedia(public_path().'/images/generating-preview.png')
//                ->preservingOriginal()
//                ->withCustomProperties([ 'generating' => true ])
//                ->toMediaCollection('preview', 'media');

            $i = new PlaylistItem();
            $i->playlist_id = $playlist->id;
            $i->type = 'image';
            $i->slide_type = $s->slide_type;
            $i->slide_id = $s->id;
            $i->is_advanced_manually = $isAdvancedManually;
            $i->midi_note = $midiNote;
            $i->metadata = $meta;
            if (! is_null($transition)) {
                $i->transition_id = $transition->id;
            }
            $i->transition_duration = $transitionDuration;
            $i->duration = $duration;
            if (! is_null($callback)) {
                $i->callback_hash = $callback->hash;
                $i->callback_delay = 20;
            }

            $i->sort_position = $count;
            $i->save();

            // 7. generate slides
            if (isset($browser)) {
                $browser->screenshot(config('app.url_internal').route('backend.slides.show', [$s->id], false).'?preview=true', storage_path().'/preview_'.$slideName.'.png');
                $browser->screenshot(config('app.url_internal').route('backend.slides.show', [$s->id], false), storage_path().'/final_'.$slideName.'.png');
            }

            $s->clearMediaCollection('preview');
            $s->clearMediaCollection('final');
            $s->addMedia(storage_path().'/preview_'.$slideName.'.png')
              ->toMediaCollection('preview', 'media');
            $s->addMedia(storage_path().'/final_'.$slideName.'.png')
              ->toMediaCollection('final', 'media');

            $slideIds[] = $s->id;
        }
        //$slideChunks = array_chunk($slideIds, 10);
        //foreach ($slideChunks as $chunk) {
        //    event(new SlideCollectionSaved(collect($chunk), 'slides'));
        //}
    }

    /**
     * @param $competition
     * @param $data
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig
     */
    public static function generateCompetitionPlaylist($competition, $data)
    {
        ini_set('max_execution_time', 1200);

        // 1. find out if we have an existing playlist and delete it
        $playlists = Playlist::where('name', 'Competition: '.$competition->name)
                             ->get();
        foreach ($playlists as $playlist) {
            foreach ($playlist->items as $item) {
                if ($item->slide != null) {
                    $item->slide->delete();
                }
            }
            $playlist->delete();
        }

        // 2. create a slide category for this competition in case it does not exist yet
        $competitionCategory = Category::where('scope', 'slides')
                                       ->where('name', 'Competitions')
                                       ->first();
        if (is_null($competitionCategory)) {
            $rootNode = Category::where('scope', 'slides')
                                ->where('_lft', 1)
                                ->first();
            if (is_null($rootNode)) {
                exit('Root node for slide category tree does not exist');
            }
            $c = new Category();
            $c->scope = 'slides';
            $c->name = 'Competitions';
            $rootNode->appendNode($c);
        }
        $category = Category::where('scope', 'slides')
                            ->where('name', $competition->name)
                            ->first();
        if (is_null($category)) {
            $rootNode = Category::where('scope', 'slides')
                                ->where('name', 'Competitions')
                                ->first();
            $category = new Category();
            $category->scope = 'slides';
            $category->name = $competition->name;
            $rootNode->appendNode($category);
            $category->refresh();
        }

        // 4. create playlist
        $playlist = new Playlist();
        $playlist->name = 'Competition: '.$competition->name;
        $playlist->type = 'video';
        $playlist->is_competition = true;
	    $playlist->competition_id = $competition->id;
        $playlist->save();

        // 3. save slides
        $count = 0;

        if (config('partymeister-slides.screenshots')) {
            $browser = new ScreenshotHelper();
        }

        foreach (Arr::get($data, 'slide', []) as $slideName => $definitions) {
            $count++;
            $type = Arr::get($data, 'type.'.$slideName);
            $name = Arr::get($data, 'name.'.$slideName);
            $id = Arr::get($data, 'id.'.$slideName, null);
            $slideType = config('partymeister-competitions-slides.'.$type.'.slide_type', 'default');
            $midiNote = config('partymeister-competitions-slides.'.$type.'.midi_note', 0);
            $transitionIdentifier = config('partymeister-competitions-slides.'.$type.'.transition', 5);
            $transitionDuration = config('partymeister-competitions-slides.'.$type.'.transition_duration', 2000);
            $duration = config('partymeister-competitions-slides.'.$type.'.duration', 20);
            $isAdvancedManually = config('partymeister-competitions-slides.'.$type.'.is_advanced_manually', true);

            $transition = Transition::where('identifier', $transitionIdentifier)
                                    ->first();

            $transitionSlidemeister = Transition::where('client_type', 'slidemeister-web')
                                                ->where('identifier', 255)
                                                ->first();

            $callback = null;

            switch ($type) {
                case 'comingup':
                    $callback = CallbackHelper::competitionStarts($competition);
                    break;
                case 'entry':
                    if (! is_null($id)) {
                        $entry = Entry::find($id);
                        if (! is_null($entry)) {
                            $callback = CallbackHelper::livevoting($entry);
                        }
                    }
                    break;
                case 'end':
                    $callback = CallbackHelper::competitionEnds($competition);
                    break;
            }

            switch ($type) {
                case 'comingup':
                case 'now':
                case 'end':
                case 'entry':
                case 'participants':
                    $s = new Slide();
                    $s->category_id = $category->id;
                    $s->name = $name;
                    $s->slide_type = $slideType;
                    $s->definitions = stripslashes($definitions);
                    $s->cached_html_preview = Arr::get($data, 'cached_html_preview.'.$slideName, '');
                    $s->cached_html_final = Arr::get($data, 'cached_html_final.'.$slideName, '');

                    $s->save();

//                    $s->addMedia(public_path() . '/images/generating-preview.png')
//                      ->preservingOriginal()
//                      ->withCustomProperties([ 'generating' => true ])
//                      ->toMediaCollection('preview', 'media');

                    $i = new PlaylistItem();
                    $i->playlist_id = $playlist->id;
                    $i->type = 'image';
                    $i->slide_type = $s->slide_type;
                    $i->slide_id = $s->id;
                    $i->is_advanced_manually = $isAdvancedManually;
                    $i->midi_note = $midiNote;
                    if (! is_null($transition)) {
                        $i->transition_id = $transition->id;
                    }
                    if (! is_null($transitionSlidemeister)) {
                        $i->transition_slidemeister_id = $transitionSlidemeister->id;
                    }
                    $i->transition_duration = $transitionDuration;
                    $i->duration = $duration;
                    if (! is_null($callback)) {
                        $i->callback_hash = $callback->hash;
                        $i->callback_delay = 20;
                    }

                    $i->sort_position = $count;
                    $i->save();

                    // 7. generate slides
                    if (isset($browser)) {
                        $browser->screenshot(config('app.url_internal').route('backend.slides.show', [$s->id], false).'?preview=true', storage_path().'/preview_'.$slideName.'.png');
                        $browser->screenshot(config('app.url_internal').route('backend.slides.show', [$s->id], false), storage_path().'/final_'.$slideName.'.png');
                    }

                    $s->clearMediaCollection('preview');
                    $s->clearMediaCollection('final');

                    if (is_file(storage_path().'/preview_'.$slideName.'.png')) {
                        $s->addMedia(storage_path().'/preview_'.$slideName.'.png')
                          ->toMediaCollection('preview', 'media');
                    }
                    if (is_file(storage_path().'/final_'.$slideName.'.png')) {
                        $s->addMedia(storage_path().'/final_'.$slideName.'.png')
                          ->toMediaCollection('final', 'media');
                    }
                    break;
                case 'video_1':
                case 'video_2':
                case 'video_3':
                    $d = json_decode($definitions, true);

                    //// Get video duration
                    //$file = File::find($d['file_id']);
                    //if (!is_null($file))
                    //{
                    //	$ffmpeg = FFProbe::create([
                    //		'ffmpeg.binaries' => config('medialibrary.ffmpeg_binaries'),
                    //		'ffprobe.binaries' => config('medialibrary.ffprobe_binaries'),
                    //	]);
                    //	$duration = $ffmpeg->format($file->getFirstMedia('file')
                    //		->getPath())// extracts file informations
                    //	->get('duration');             // returns the duration property
                    //}

                    // Load file and check mime type
                    $file = File::find($d['file_id']);
                    if (is_null($file)) {
                        break;
                    }

                    if ($file->media()
                             ->first() != null && ($file->media()
                                                        ->first()->mime_type == 'video/x-m4v' || $file->media()
                                                                                                      ->first()->mime_type == 'video/mp4')) {
                        $type = 'video';
                    } else {
                        $type = 'image';
                    }

                    $i = new PlaylistItem();
                    $i->playlist_id = $playlist->id;
                    $i->type = $type;
                    $i->is_advanced_manually = $isAdvancedManually;
                    $i->midi_note = $midiNote;
                    if (! is_null($transition)) {
                        $i->transition_id = $transition->id;
                    }
                    if (! is_null($transitionSlidemeister)) {
                        $i->transition_slidemeister_id = $transitionSlidemeister->id;
                    }
                    $i->transition_duration = $transitionDuration;
                    $i->duration = $duration;
                    $i->sort_position = $count;
                    $i->save();

                    // Create file association
                    $fa = new FileAssociation();
                    $fa->file_id = $d['file_id'];
                    $fa->model_type = get_class($i);
                    $fa->model_id = $i->id;
                    $fa->identifier = 'playlist_item';
                    $fa->save();
                    break;
            }
        }
    }

    public function filters()
    {
        $this->filter->add(new SelectRenderer('type'))
                     ->setOptionPrefix(trans('partymeister-slides::backend/playlists.type'))
                     ->setEmptyOption('-- '.trans('partymeister-slides::backend/playlists.type').' --')
                     ->setOptions(trans('partymeister-slides::backend/playlists.types'));
    }

    public function afterCreate()
    {
        $this->savePlaylistItems();
    }

    protected function savePlaylistItems()
    {
        $items = json_decode($this->request->get('playlist_items'));

        // Delete all playlist items for this playlist
        foreach ($this->record->items()
                              ->get() as $item) {
            $item->file_association()
                 ->delete();
            $item->delete();
        }

        // Create new playlist items
        foreach ($items as $key => $item) {
            $i = new PlaylistItem();
            $i->playlist_id = $this->record->id;
            $i->type = (isset($item->type) ? $item->type : $this->getType($item));

            if (isset($item->transition_identifier)) {
                $transition = Transition::where('identifier', $item->transition_identifier)
                                        ->first();
            } else {
                $transition = Transition::where('identifier', 255)
                                        ->first();
            }
            if (isset($item->transition_slidemeister_identifier)) {
                $transitionSlidemeister = Transition::where('identifier', $item->transition_slidemeister_identifier)
                                                    ->first();
            } else {
                $transitionSlidemeister = Transition::where('identifier', 255)
                                                    ->first();
            }

            if (isset($item->overwrite_slide_type) && $item->overwrite_slide_type != '') {
                $i->slide_type = $item->overwrite_slide_type;
            } else {
                if (property_exists($item, 'slide_type')) {
                    $i->slide_type = $item->slide_type;
                }
            }

            $i->duration = $item->duration;
            $i->transition_id = (is_null($transition) ? null : $transition->id);
            $i->transition_slidemeister_id = (is_null($transitionSlidemeister) ? null : $transitionSlidemeister->id);
            $i->transition_duration = $item->transition_duration;
            $i->is_advanced_manually = $item->is_advanced_manually;
            $i->midi_note = $item->midi_note;
            $i->callback_hash = $item->callback_hash;
            $i->callback_delay = $item->callback_delay;
            $i->metadata = (isset($item->metadata) ? $item->metadata : '{}');
            $i->sort_position = $key;

            if (isset($item->slide) && property_exists($item, 'slide_type') && $item->slide_type !== '') {
                if (isset($item->slide)) {
                    $i->slide_id = $item->slide->id;
                } else {
                    $i->slide_id = $item->id;
                }
            }

            if (isset($item->definitions) && property_exists($item, 'slide_type') && $item->slide_type !== '') {
                if (isset($item->slide)) {
                    $i->slide_id = $item->slide->id;
                } else {
                    $i->slide_id = $item->id;
                }
            }

            // Fixme: implement this
            $i->is_muted = false;

            $i->save();


            if (!$i->slide && (isset($item->file) || isset($item->file_association))) {
                // Create file association
                $fa = new FileAssociation();
                if (property_exists($item, 'file_association')) {
                    $fa->file_id = $item->file_association->id;
                } else {
                    $fa->file_id = $item->id;
                }
                $fa->model_type = get_class($i);
                $fa->model_id = $i->id;
                $fa->identifier = 'playlist_item';
                $fa->save();
            }
        }
    }

    /**
     * @param $data
     */

    /**
     * @param $item
     * @return string
     */
    protected function getType($item)
    {
        if (isset($item->file) && is_array($item->file) || (isset($item->file_preview) && is_array($item->file_preview))) {
            return 'image';
        }
        if (isset($item->file_preview)) {
            $item->file = $item->file_preview;
        }
        if (in_array($item->file->mime_type, [
            'image/png',
            'image/jpg',
            'image/jpeg',
        ])) {
            return 'image';
        }

        if (in_array($item->file->mime_type, ['video/x-m4v', 'video/mp4'])) {
            return 'video';
        }

        return '';
    }

    public function beforeUpdate()
    {
        $this->record->updated_at = date('Y-m-d H:i:s');
    }

    public function afterUpdate()
    {
        $this->savePlaylistItems();
    }
}
