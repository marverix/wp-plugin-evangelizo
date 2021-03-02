<?php
/**
 * This file contains Evangelizo_Article class
 *
 * @package evangelizo
 */

/**
 * Evangelizo_Article class, which generates <article> element
 */
class Evangelizo_Article {

	/**
	 * Type
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Title
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Text
	 *
	 * @var string
	 */
	protected $text;

	/**
	 * Source
	 *
	 * @var string
	 */
	protected $source;

	/**
	 * Author
	 *
	 * @var string
	 */
	protected $author;

	/**
	 * Constructor
	 *
	 * @param string $type Type.
	 */
	public function __construct( $type ) {
		$this->set_type( $type );
	}

	/**
	 * Set Type
	 *
	 * @param string $type Type.
	 * @return void
	 */
	public function set_type( $type ) {
		$this->type = (string) $type;
	}

	/**
	 * Set Name
	 *
	 * @param string $name Name.
	 * @return void
	 */
	public function set_name( $name ) {
		$this->name = (string) $name;
	}

	/**
	 * Set Title
	 *
	 * @param string $title Title.
	 * @return void
	 */
	public function set_title( $title ) {
		$this->title = (string) $title;
	}

	/**
	 * Set Text and clean it
	 *
	 * @param string $text Text.
	 * @return void
	 */
	public function set_text( $text ) {
		// Clean text from [[Ab XYZ]] .
		$text = preg_replace( '/\[\[[^\]]+\]\]/', '', $text );

		// Clean text from \r .
		$text = preg_replace( '/\r/', '', $text );

		$this->text = $text;
	}

	/**
	 * Set Source
	 *
	 * @param string $source Source.
	 * @return void
	 */
	public function set_source( $source ) {
		$this->source = $source;
	}

	/**
	 * Set Author
	 *
	 * @param string $author Author.
	 * @return void
	 */
	public function set_author( $author ) {
		$this->author = $author;
	}

	/**
	 * Get for HTML the class param value
	 *
	 * @return string
	 */
	public function get_html_class() {
		return 'evangelizo-' . $this->type;
	}

	/**
	 * Get the HTML string with the name
	 *
	 * @return string
	 */
	public function get_html_name() {
		return '<h3>' . $this->name . '</h3>' . "\n";
	}

	/**
	 * Get the HTML string with the title
	 *
	 * @return string
	 */
	public function get_html_title() {
		return '<p><strong>' . $this->title . '</strong></p>' . "\n";
	}

	/**
	 * Get the replacer used in get_html_text
	 *
	 * @return string
	 */
	protected function get_text_replacer() {
		if ( $this->type === 'psalm' ) {
			return '<br>';
		}

		return ' ';
	}

	/**
	 * Get the HTML string with the text
	 *
	 * @return string
	 */
	public function get_html_text() {
		// Replace \n with <br> .
		$text = preg_replace( '/\n/', $this->get_text_replacer(), $this->text );

		return '<p>' . $text . '</p>' . "\n";
	}

	/**
	 * Get the HTML string with the footer
	 *
	 * @return string
	 */
	public function get_html_footer() {
		$parts = array();

		if ( ! empty( $this->source ) ) {
			$parts[] = $this->source;
		}

		if ( ! empty( $this->author ) ) {
			$parts[] = $this->author;
		}

		if ( empty( $parts ) ) {
			return '';
		}

		return '<footer><em>' . implode( '<br>', $parts ) . '</em></footer>' . "\n";
	}

	/**
	 * To String
	 *
	 * @return string
	 */
	public function __toString() {
		$ret  = '<article class="' . $this->get_html_class() . '">';
		$ret .= $this->get_html_name();
		$ret .= $this->get_html_title();
		$ret .= $this->get_html_text();
		$ret .= $this->get_html_footer();
		$ret .= '</article>';
		return $ret;
	}

}
