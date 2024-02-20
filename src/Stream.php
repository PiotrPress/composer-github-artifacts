<?php declare( strict_types = 1 );

namespace PiotrPress\Composer\GitHub;

use PiotrPress\Streamer;
use GuzzleHttp\Client;

class Stream extends Streamer {
    static protected string $url = '';

    static public function setUrl( string $url ) : void {
        self::$url = $url;
    }

    static public function register( string $protocol, int $flags = 0 ) : bool {
        if ( \in_array( $protocol, \stream_get_wrappers() ) ) self::unregister( $protocol );
        return parent::register( $protocol, $flags );
    }

    public function stream_open( string $path, string $mode, int $options, ?string &$opened_path ) : bool {       
        $client = new Client();
        
        if ( ( $response = $client->get( \parse_url( self::$url, \PHP_URL_SCHEME ) . '://' . \parse_url( self::$url, \PHP_URL_HOST ) .
            \sprintf( \parse_url( self::$url, \PHP_URL_PATH ), \substr( $path, \strlen( \parse_url( $path, \PHP_URL_SCHEME ) . '://' ), -\strlen( \basename( $path ) . '/' ) ) ), [ 
            'auth' => $auth = [ \parse_url( self::$url, \PHP_URL_USER ), \parse_url( self::$url, \PHP_URL_PASS ) ],
            'headers' => $headers = [ 'Accept' => 'application/vnd.github+json', 'User-Agent' => 'Composer' ],
            'query' => [ 'per_page' => 1, 'name' => \basename( $path ) ]
        ] ) )->getStatusCode() === 200 and $url = \json_decode( $response->getBody()->getContents(), true )[ 'artifacts' ][ 0 ][ 'archive_download_url' ] ?? '' ) {
            $data = $client->get( $url, [ 'auth' => $auth, 'headers' => $headers ] )->getBody()->getContents() ?? null;
            \file_put_contents( $file = \sys_get_temp_dir() . '/packages.json.zip', $data );
            self::$data[ $path ] = \file_get_contents( "zip://$file#packages.json" );
            \unlink( $file );
        } else self::$data[ $path ] = '';

        return parent::stream_open( $path, $mode, $options, $opened_path );
    }
}