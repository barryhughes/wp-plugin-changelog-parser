<?php
namespace Harry_Bewes\WordPress\Tests;

use Exception;
use Harry_Bewes\WordPress\Changelog_Parser as Parser;
use PHPUnit\Framework\TestCase as Test_Case;

class Changelog_Parser extends Test_Case {
	private $changelog;

	public function setUp(): void {
		$this->changelog = ( new Parser( __DIR__ . '/data/readme.sample.txt' ) )->get_changelog();
	}

	/**
	 * @test
	 */
	public function throws_exception_if_readme_path_is_invalid() {
		$this->expectException( Exception::CLASS );
		new Parser( __DIR__ . '/data/readme.sample.NOEXISTS.txt' );
	}

	/**
	 * @test
	 */
	public function can_successfully_extract_changelog() {
		$this->assertNotEmpty( $this->changelog );
	}

	/**
	 * @test
	 */
	public function changelog_contains_expected_number_of_entries() {
		$this->assertCount( 3, $this->changelog );
	}

	/**
	 * @test
	 */
	public function changelog_is_structured_as_associative_array() {
		$this->assertIsArray( $this->changelog );
		$this->assertNotEquals( $this->changelog, array_values( $this->changelog ) );
	}

	/**
	 * @test
	 */
	public function version_block_structure_has_entries_array() {
		$version_block = $this->changelog['2.5.0'];
		$this->assertObjectHasAttribute( 'entries', $version_block );
		$this->assertIsArray( $version_block->entries );
	}

	/**
	 * @test
	 */
	public function date_is_captured_if_provided() {
		$version_block = $this->changelog['2.5.1'];
		$this->assertObjectHasAttribute( 'date', $version_block );
		$this->assertEquals( '2019-04-20', $version_block->date );
	}

	/**
	 * @test
	 */
	public function date_is_empty_string_if_not_provided() {
		$version_block = $this->changelog['2.4.5'];
		$this->assertObjectHasAttribute( 'date', $version_block );
		$this->assertEquals( '', $version_block->date );
	}
}