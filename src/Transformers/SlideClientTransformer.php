<?php

namespace Partymeister\Slides\Transformers;

use League\Fractal;
use Partymeister\Slides\Models\SlideClient;

/**
 * Class SlideClientTransformer
 * @package Partymeister\Slides\Transformers
 */
class SlideClientTransformer extends Fractal\TransformerAbstract
{

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [];


    /**
     * Transform record to array
     *
     * @param  SlideClient  $record
     *
     * @return array
     */
    public function transform(SlideClient $record)
    {
        return [
            'id'            => (int) $record->id,
            'name'          => $record->name,
            'type'          => $record->type,
            'ip_address'    => $record->ip_address,
            'port'          => $record->port,
            'configuration' => $record->configuration,
            'websocket' => [
                'key' => config('broadcasting.connections.pusher.key'),
                'host' => config('broadcasting.connections.pusher.options.host'),
                'port' => config('broadcasting.connections.pusher.options.port'),
                'path' => config('broadcasting.connections.pusher.options.path'),
            ]
        ];
    }
}
