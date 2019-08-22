<?php

namespace thesebas\graphql;

define('API_BASE', 'http://ws.audioscrobbler.com/2.0/');
define('API_KEY', 'xxx');

function makeKey($params)
{
    return md5(\serialize($params));
    $r = [];
    foreach ($params as $k => $v) {
        $r[] = "{$k}-{$v}";
    }
    return escapeshellcmd(join("--", $r));
}

function request($method, $params)
{
    $query = \http_build_query([
        'api_key' => API_KEY,
        'format' => 'json',
        'method' => $method,
    ] + $params);
    $key = makeKey($params + ['method' => $method]);
    $path = "cache/{$key}";
    if (\file_exists($path)) {
        $resp = \file_get_contents($path);
    } else {
        $resp = \file_get_contents(API_BASE . '?' . $query);
        tools\dump($query);
        \file_put_contents($path, $resp);
    }

    return \json_decode($resp, true);
}

function findArtists($pattern, $limit = null)
{
    $res = request('artist.search', ['artist' => $pattern] + (null === $limit ? ['limit' => 10] : ['limit' => $limit]));
    return [$res['results']['artistmatches']['artist'], $res['results']['opensearch:totalResults']];
}

function getArtist($name)
{
    $res = request('artist.getinfo', ['artist' => $name]);
    return $res['artist'];
}

function getArtistAlbum($artist, $album)
{
    $res = request('album.getinfo', ['artist' => $artist, 'album' => $album]);
    return $res['album'];
}

function getArtistTopAlbums($artist, $limit = null)
{
    $res = request('artist.gettopalbums', ['artist' => $artist] + (null === $limit ? [] : ['limit' => $limit]));
    return $res['topalbums']['album'];
}

function findAlbum($pattern)
{
    $res = request('album.search', ['album' => $pattern, 'limit' => 5]);
    return [$res['results']['albummatches']['album'], $res['results']['opensearch:totalResults']];

}
