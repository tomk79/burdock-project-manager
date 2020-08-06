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

		$pj = $burdockProjectManager->pj('test_pj_001');
		$this->assertSame( is_object($pj), true );
        return;
	}

}
