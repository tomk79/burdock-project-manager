<?php
/**
 * main test
 */
class clearingTest extends PHPUnit_Framework_TestCase{
	private $fs;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
	}


	/**
	 * 後処理
	 */
	public function testClearing(){
		$this->assertSame( $this->fs->rm( __DIR__.'/testdata/bd_data_main/repositories/test_pj_fine---master/.git/' ), true );
        return;
	}

}
