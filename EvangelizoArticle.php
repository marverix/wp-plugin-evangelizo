<?php

class EvangelizoArticle {

	protected $type;
	protected $name;
	protected $title;
	protected $text;
	protected $source;
	protected $author;

	public function __construct ( $type ) {
		$this->set_type( $type );
	}

	public function set_type ( $type ) {
		$this->type = (string) $type;
	}

	public function set_name ( $name ) {
		$this->name = (string) $name;
	}

	public function set_title ( $title ) {
		$this->title = (string) $title;
	}

	public function set_text ( $text ) {
		## Clean text from [[Ab XYZ]]
		$text = preg_replace( '/\[\[[^\]]+\]\]/', '', $text );

		## Clean text from \r
		$text = preg_replace( '/\r/', '', $text );

		$this->text = $text;
	}

	public function set_source ( $source ) {
		$this->source = $source;
	}

	public function set_author ( $author ) {
		$this->author = $author;
	}

	public function get_html_class () {
		return 'evangelizo-' . $this->type;
	}

	public function get_html_name () {
		return '<h3>' . $this->name . '</h3>' . "\n";
	}

	public function get_html_title () {
		return '<p><strong>' . $this->title . '</strong></p>' . "\n";
	}

	protected function get_text_replacer () {
		if ( $this->type == 'psalm' ) {
			return '<br>';
		}

		return ' ';
	}

	public function get_html_text () {
		## Replace \n with <br>
		$text = preg_replace( '/\n/', $this->get_text_replacer(), $this->text );

		return '<p>' . $text . '</p>' . "\n";
	}

	public function get_html_footer () {
		$parts = array();

		if ( !empty( $this->source ) ) {
			$parts[] = $this->source;
		}

		if ( !empty( $this->author ) ) {
			$parts[] = $this->author;
		}

		if ( empty( $parts ) ) {
			return '';
		}

		return '<footer><em>' . implode( '<br>', $parts ) . '</em></footer>' . "\n";
	}

	public function __toString () {
		$ret = '<article class="' . $this->get_html_class() . '">';
		$ret .= $this->get_html_name();
		$ret .= $this->get_html_title();
		$ret .= $this->get_html_text();
		$ret .= $this->get_html_footer();
		$ret .= '</article>';
		return $ret;
	}

}
