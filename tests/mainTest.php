<?php
/**
 * main test
 */
class mainTest extends PHPUnit_Framework_TestCase{
	private $fs;
	private $helper_commander;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
		require_once(__DIR__.'/helper/commander.php');
		$this->helper_commander = new test_helper_commander();
		$this->helper_commander->execute('testdata/bd_data_main/repositories/test_pj_fine---master/', 'composer install');
		$this->helper_commander->execute('testdata/bd_data_main/repositories/test_pj_fine---master/', 'git init');
	}


	/**
	 * インスタンス化してみるテスト
	 */
	public function testCreateNewInstance(){
        $burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( __DIR__.'/testdata/bd_data_main' );
		$this->assertSame( is_object($burdockProjectManager), true );

		$pj = $burdockProjectManager->project('test_pj_fine');
		$this->assertSame( is_object($pj), true );

		$status = $pj->branch('preview', 'master')->status();
		// var_dump($status);
		$this->assertSame( is_object($status), true );
		$this->assertSame( is_object($status->api), true );
		$this->assertSame( $status->api->available, true );
		$this->assertSame( is_string($status->api->version), true );
		$this->assertSame( $status->api->is_sitemap_loaded, true );
		$this->assertSame( is_object($status->px2dthelper), true );
		$this->assertSame( $status->px2dthelper->available, true );
		$this->assertSame( is_string($status->px2dthelper->version), true );
		$this->assertSame( $status->px2dthelper->is_sitemap_loaded, true );
		$this->assertSame( $status->pathExists, true );
		$this->assertSame( $status->pathContainsFileCount, 8 );
		$this->assertSame( $status->composerJsonExists, true );
		$this->assertSame( $status->entryScriptExists, true );
		$this->assertSame( $status->homeDirExists, true );
		$this->assertSame( $status->confFileExists, true );
		$this->assertSame( $status->px2DTConfFileExists, true );
		$this->assertSame( $status->vendorDirExists, true );
		$this->assertSame( $status->isPxStandby, true );
		$this->assertSame( $status->gitDirExists, true );
		$this->assertSame( $status->guiEngineName, "broccoli-html-editor-php" );
        return;
	}

}
