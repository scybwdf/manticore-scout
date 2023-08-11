<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'default' => env('SCOUT_ENGINE', 'manticoresearch'),
    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],
    'prefix' => env('APP_ENV', ''),
    'soft_delete' => false,
    'concurrency' => 100,
    'engine' => [
        'manticoresearch'=>[
            'driver' =>Scybwdf\ManticoreScout\Provider\ManticoreSearchProvider::class,
            'host' => env('MANTICORE_HOST','localhost'), // Manticore Search 主机
            'port' => env('MANTICORE_PORT',9308),        // Manticore Search 端口
            'setting'=>[],
            'auto_index_create'=>true //自动创建index
        ]
    ],
];
