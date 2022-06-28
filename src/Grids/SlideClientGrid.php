<?php

namespace Partymeister\Slides\Grids;

use Motor\Backend\Grid\Grid;
use Motor\Backend\Grid\Renderers\TranslateRenderer;
use Motor\Backend\Grid\Renderers\BladeRenderer;

/**
 * Class SlideClientGrid
 */
class SlideClientGrid extends Grid
{
    protected function setup()
    {
        $this->addColumn('name', trans('partymeister-slides::backend/slide_clients.name'), true);
        $this->addColumn('type', trans('partymeister-slides::backend/slide_clients.type'))
             ->renderer(TranslateRenderer::class, ['file' => 'partymeister-slides::backend/slide_clients.types']);
        $this->addColumn('ip_address', trans('partymeister-slides::backend/slide_clients.ip_address'), true);
        $this->addColumn('port', trans('partymeister-slides::backend/slide_clients.port'), true);

	    $this->addColumn('id', trans('partymeister-slides::backend/slide_clients.ws_channel'), true)
	         ->renderer(BladeRenderer::class, [ 'template' => 'partymeister-slides::grid.slide_clients.ws_name' ]);

        $this->addColumn('sort_position', trans('partymeister-slides::backend/slide_clients.sort_position'), true);
	    $this->addColumn('playlist.name', trans('partymeister-slides::backend/slide_clients.current_playlist'), true)
	         ->renderer(BladeRenderer::class, [ 'template' => 'partymeister-slides::grid.slide_clients.current_playlist' ]);
        $this->setDefaultSorting('sort_position', 'ASC');

        $this->addAction(trans('partymeister-slides::backend/slide_clients.open_client'), 'backend.slidemeister-web.show', ['class' => 'btn-primary'])
             ->onCondition('type', 'slidemeister-web', '=');

        $this->addEditAction(trans('motor-backend::backend/global.edit'), 'backend.navigations.edit')
             ->onCondition('parent_id', null, '!=');
        $this->addEditAction(trans('motor-backend::backend/global.edit'), 'backend.slide_clients.edit');
        $this->addDeleteAction(trans('motor-backend::backend/global.delete'), 'backend.slide_clients.destroy');
    }
}
