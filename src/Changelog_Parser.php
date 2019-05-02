<?php
namespace Harry_Bewes\WordPress;

use Exception;

/**
 * Given a WordPress plugin readme.txt file, parses it and extracts the
 * changelog.
 */
class Changelog_Parser {
	/**
	 * Major headings are lines which are delimited with `==`.
	 */
	const HEADING = '==';

	/**
	 * Minor heading are lines which are delimited with `=`.
	 */
	const SUB_HEADING = '=';

	/** @var array */
	private $changelog = [];

	/** @var bool */
	private $entered_changelog = false;

	/** @var bool */
	private $in_changelog = false;

	/** @var string */
	private $line_content = '';

	/** @var int */
	private $line_index = '';

	/** @var array */
	private $readme = [];

	/** @var string */
	private $readme_path = '';

	/** @var string */
	private $version = '';

	/**
	 * Given a valid path to a plugin readme file, attempts to parse
	 * the changelog entries within it.
	 *
	 * @param string $readme_path
	 *
	 * @throws Exception
	 */
	public function __construct( string $readme_path ) {
		$this->readme_path = $readme_path;
		$this->load();

		if ( empty( $this->readme ) ) {
			throw new Exception( 'Could not open readme file for reading.' );
		}

		$this->parse();
	}

	private function load() {
		if ( ! file_exists( $this->readme_path ) ) {
			return;
		}

		$readme_file = fopen( $this->readme_path, 'r' );

		if ( ! $readme_file ) {
			return;
		}

		while ( $line = fgets( $readme_file ) ) {
			$this->readme[] = trim( $line );
		}

		fclose( $readme_file );
	}

	private function parse() {
		foreach ( $this->readme as $this->line_index => $this->line_content ) {
			// Detect when we enter and exit the changelog section of the readme text
			$this->detect_entered_changelog();
			$this->detect_exited_changelog();

			// Detect individual version blocks and record the information
			$this->detect_start_of_version_block();
			$this->detect_single_change();
		}
	}

	private function detect_entered_changelog() {
		if ( $this->entered_changelog ) {
			return;
		}

		// Look for the `== Changelog ==` heading
		if ( $this->detect_heading( self::HEADING, 'Changelog' ) ) {
			$this->entered_changelog = true;
			$this->in_changelog = true;
		}
	}

	private function detect_exited_changelog() {
		if ( ! $this->entered_changelog ) {
			return;
		}

		// Look for a new heading such as `== Special Notes ==`
		if (
			$this->detect_heading( self::HEADING )
			&& ! $this->detect_heading( self::HEADING, 'Changelog' )
		) {
			$this->in_changelog = false;
		}
	}

	private function detect_start_of_version_block() {
		if ( ! $this->in_changelog ) {
			return;
		}

		if ( ! $this->detect_heading( self::SUB_HEADING, '[0-9]+\.[0-9\.]+' ) ) {
			return;
		}

		preg_match( '/[0-9]+\.[0-9\.]+/', $this->line_content, $version );
		preg_match( '/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $this->line_content, $date );

		if ( count( $version ) !== 1 ) {
			return;
		}

		$version = trim( $version[ 0 ] );
		$date    = $date[ 0 ] ?? '';

		$this->version = trim( $version );
		$this->changelog[ $this->version ] = (object) [
			'date'    => trim( $date ),
			'entries' => [],
		];
	}

	private function detect_single_change() {
		if ( ! $this->in_changelog ) {
			return;
		}

		if ( empty( $this->version ) || ! isset( $this->changelog[ $this->version ] ) ) {
			return;
		}

		if ( 0 !== strpos( $this->line_content, '* ' ) || strlen( $this->line_content ) < 3 ) {
			return;
		}

		$entry = trim( substr( $this->line_content, 2 ) );

		if ( empty( $entry ) ) {
			return;
		}

		$this->changelog[ $this->version ]->entries[] = $entry;
	}

	private function detect_heading( $heading_marker = '=', $contains = '' ) {
		preg_match( "/^{$heading_marker}\s?(.*)\s?{$heading_marker}$/i", $this->line_content, $matches );

		if ( count( $matches ) !== 2 ) {
			return false;
		}

		if ( empty( $contains ) ) {
			return true;
		}

		return (bool) preg_match( "/{$contains}/i", $matches[ 1 ] );
	}

	public function get_changelog() {
		return $this->changelog;
	}
}