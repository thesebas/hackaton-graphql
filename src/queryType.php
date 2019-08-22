<?php
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

$artistType = null;

$albumType = new ObjectType([
    'name' => 'Album',
    'description' => 'This type represents Album recorded by some artist',
    'fields' => function () use (&$artistType) {
        return [
            'name' => [
                'type' => Type::string(),
                'description' => 'name of the Album',
            ],
            'artist' => [
                'type' => $artistType,
                'description' => 'creator of the Album',
                'resolve' => function ($root) {
                    $artistName = is_string($root['artist']) ? $root['artist'] : $root['artist']['name'];
                    return \thesebas\graphql\getArtist($artistName);
                },
            ],
            'url' => [
                'type' => Type::string(),
                'description' => 'url of album on last.fm',
                'resolve' => function ($root) {
                    return $root['url'];
                },
            ],
        ];
    },
]);

$artistType = new ObjectType([
    'name' => 'Artist',
    'description' => 'This type represents Artist',
    'fields' => function () use (&$artistType, &$albumType) {
        return [
            'name' => [
                'type' => Type::string(),
                'description'=>'name of the Artist',
                'resolve' => function ($root) {
                    return $root['name'];
                },
            ],
            'mbid' => [
                'type' => Type::string(),
                'description' => 'UUID of artist in MusicBrainz',
                'resolve' => function ($root) {
                    if (!empty($root['mbid'])) {
                        return $root['mbid'];
                    }
                    $artist = \thesebas\graphql\getArtist($root['name']);
                    return $artist['mbid'] ?? null;
                },
            ],
            'albums' => [
                'type' => Type::listOf($albumType),
                'description' => 'Top albums of this artist',
                'args' => [
                    'limit' => [
                        'type' => Type::int(),
                    ],
                ],
                'resolve' => function ($root, $args) {
                    return \thesebas\graphql\getArtistTopAlbums($root['name'], $args['limit'] ?? null);
                },
            ],
            'url' => [
                'type' => Type::string(),
                'description' => 'url of this album on last.fm',
                'resolve' => function ($root) {
                    return $root['url'];
                },
            ],
            'similar' => [
                'type' => Type::listOf($artistType),
                'description' => 'Other related artists',
                'resolve' => function ($root) {
                    // \thesebas\graphql\tools\dump($root);
                    if (!empty($root['similar']['artist'])) {
                        return $root['similar']['artist'];
                    }
                    $artist = \thesebas\graphql\getArtist($root['name']);
                    return $artist['similar']['artist'];
                },
            ],
        ];
    },
]);

return new ObjectType([
    'name' => 'Query',
    'description' => 'This is main query',
    'fields' => [
        'artistsearch' => [
            'description' => 'Search artists by name',
            'type' => Type::listOf($artistType),
            'args' => [
                'pattern' => Type::nonNull(Type::string()),
                'limit' => Type::int(),
            ],
            'resolve' => function ($root, $args) {
                list($res) = thesebas\graphql\findArtists($args['pattern'], $args['limit'] ?? null);
                return $res;
            },
        ],
        'artist' => [
            'type' => $artistType,
            'args' => [
                'name' => Type::nonNull(Type::string()),
            ],
            'resolve' => function ($root, $args) {
                return thesebas\graphql\getArtist($args['name']);
            },
        ],
    ],
]);
