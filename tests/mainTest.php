<?php
/**
 * main test
 */
class mainTest extends PHPUnit_Framework_TestCase{
	private $fs;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
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
		$this->assertSame( is_object($status->px2dthelper), true );
		$this->assertSame( $status->pathExists, true );
        return;
	}

}
