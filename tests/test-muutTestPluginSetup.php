<?php

/**
 * Test to make sure Muut plugin setup is funcitonal.
 */
class Muut_Test_Plugin_Setup extends WP_UnitTestCase {

	const CLASSNAME = 'Muut';

	public function setUp() {
		parent::setUp();
	}

	public function takeDown() {
		parent::takeDown();
	}

	/**
	 * Test that the Muut class exists.
	 */
	function test_class_exists() {
		$classname = 'Muut';

		$this->assertTrue( class_exists( $classname ) );
	}

	/**
	 * Test that the muut() function returns a "Muut" instance.
	 */
	function test_muut_instance() {
		$instance = muut();

		$this->assertInstanceOf( self::CLASSNAME, $instance );

		return $instance;
	}

	/**
	 * Test the singleton-ness of the main Muut class.
	 *
	 * @depends test_muut_instance
	 */
	function test_muut_singleton() {
		$instance_1 = muut();
		$instance_2 = muut();

		$instance_1->testprop = 'A';
		$instance_2->testprop = 'B';

		// Make sure the values equal each other (as they are referencing the SAME (and only) instance.
		$this->assertEquals( $instance_1->testprop, $instance_2->testprop );

		// Make sure that instance 1's testprop changed to 'B'.
		$this->assertEquals( $instance_1->testprop, 'B' );
	}
}