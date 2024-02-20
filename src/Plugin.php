<?php declare( strict_types = 1 );

namespace PiotrPress\Composer\GitHub;

use Composer\Plugin\PluginInterface;
use Composer\Composer;
use Composer\IO\IOInterface;

class Plugin implements PluginInterface {
    public function activate( Composer $composer, IOInterface $io ) : void {
        $pass = $composer->getConfig()->get( 'http-basic' )[ 'github.com' ][ 'password' ] ?? '';
        Stream::setUrl( "https://x-oauth-basic:$pass@api.github.com/repos/%s/actions/artifacts" );
        Stream::register( 'github.artifacts' );
    }

    public function deactivate( Composer $composer, IOInterface $io ) : void {}
    public function uninstall( Composer $composer, IOInterface $io ) : void {}
}