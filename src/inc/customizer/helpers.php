<?php
/**
 * @package ttf-one
 */

if ( ! function_exists( 'ttf_one_sanitize_text' ) ) :
/**
 * Sanitize a string to allow only tags in the allowedtags array.
 *
 * @since  1.0
 *
 * @param  string    $string    The unsanitized string.
 * @return string               The sanitized string.
 */
function ttf_one_sanitize_text( $string ) {
	global $allowedtags;
	return wp_kses( $string , $allowedtags );
}
endif;

if ( ! function_exists( 'sanitize_hex_color' ) ) :
/**
 * Sanitizes a hex color.
 *
 * This is a copy of the core function for use when the customizer is not being shown.
 *
 * @since  1.0.0
 *
 * @param  string         $color    The proposed color.
 * @return string|null              The sanitized color.
 */
function sanitize_hex_color( $color ) {
	if ( '' === $color )
		return '';

	// 3 or 6 hex digits, or the empty string.
	if ( preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) )
		return $color;

	return null;
}
endif;

if ( ! function_exists( 'sanitize_hex_color_no_hash' ) ) :
/**
 * Sanitizes a hex color without a hash. Use sanitize_hex_color() when possible.
 *
 * This is a copy of the core function for use when the customizer is not being shown.
 *
 * @since  1.0.0
 *
 * @param  string         $color    The proposed color.
 * @return string|null              The sanitized color.
 */
function sanitize_hex_color_no_hash( $color ) {
	$color = ltrim( $color, '#' );

	if ( '' === $color )
		return '';

	return sanitize_hex_color( '#' . $color ) ? $color : null;
}
endif;

if ( ! function_exists( 'maybe_hash_hex_color' ) ) :
/**
 * Ensures that any hex color is properly hashed.
 *
 * This is a copy of the core function for use when the customizer is not being shown.
 *
 * @since  1.0.0
 *
 * @param  string         $color    The proposed color.
 * @return string|null              The sanitized color.
 */
function maybe_hash_hex_color( $color ) {
	if ( $unhashed = sanitize_hex_color_no_hash( $color ) )
		return '#' . $unhashed;

	return $color;
}
endif;

if ( ! function_exists( 'ttf_one_sanitize_choice' ) ) :
/**
 * Sanitize a value from a list of allowed values.
 *
 * The first value in the 'allowed_choices' array will be the default if the given
 * value doesn't match anything in the array.
 *
 * @param mixed $value
 * @param mixed $setting
 *
 * @return mixed
 */
function ttf_one_sanitize_choice( $value, $setting ) {
	if ( is_object( $setting ) ) {
		$setting = $setting->id;
	}

	$allowed_choices = array( 0 );

	switch ( $setting ) {
		case 'site-layout' :
			$allowed_choices = array( 'full-width', 'boxed' );
			break;
		case 'background-size' :
			$allowed_choices = array( 'auto', 'cover', 'contain' );
			break;
		case 'background-repeat' :
			$allowed_choices = array( 'no-repeat', 'repeat', 'repeat-x', 'repeat-y' );
			break;
		case 'background-position' :
			$allowed_choices = array( 'left', 'center', 'right' );
			break;
		case 'background-attachment' :
			$allowed_choices = array( 'fixed', 'scroll' );
			break;
		case 'font-site-title' || 'font-header' || 'font-body' :
			$fonts = ttf_one_get_google_fonts();
			$allowed_choices = array_keys( $fonts );
			break;
		case 'header-layout' || 'footer-layout' :
			$allowed_choices = array( 'layout-1', 'layout-2', 'layout-3', 'layout-4' );
			break;
	}

	if ( ! in_array( $value, $allowed_choices ) ) {
		$value = $allowed_choices[0];
	}

	return $value;
}
endif;

if ( ! function_exists( 'ttf_one_display_background' ) ) :
/**
 * Write the CSS to implement the background options.
 *
 * @since  1.0.0.
 *
 * @param  string    $css    The current CSS.
 * @return string            The modified CSS.
 */
function ttf_one_display_background( $css ) {
	$background_color = maybe_hash_hex_color( get_theme_mod( 'background-color', '#ffffff' ) );

	$background_image = get_theme_mod( 'background-image', false );
	if ( false !== $background_image ) {
		// Get and escape the other properties
		$background_size       = ttf_one_sanitize_choice( get_theme_mod( 'background-size', 'auto' ), 'background-size' );
		$background_repeat     = ttf_one_sanitize_choice( get_theme_mod( 'background-repeat', 'no-repeat' ), 'background-repeat' );
		$background_position   = ttf_one_sanitize_choice( get_theme_mod( 'background-position', 'center' ), 'background-position' );
		$background_attachment = ttf_one_sanitize_choice( get_theme_mod( 'background-attachment', 'fixed' ), 'background-attachment' );

		// Escape the image URL properly
		$background_image = addcslashes( esc_url_raw( $background_image ), '"' );

		// All variables are escaped at this point
		$css .= 'body{background:' . $background_color . ' url(' . $background_image . ') ' . $background_repeat . ' ' . $background_position . ' ' . $background_attachment . ';background-size:' . $background_size . ';}';
	} else {
		$css .= 'body{background-color:' . $background_color . ';}';
	}

	return $css;
}
endif;

add_filter( 'ttf_one_css', 'ttf_one_display_background' );

if ( ! function_exists( 'ttf_one_display_colors' ) ) :
/**
 * Write the CSS to implement the color options.
 *
 * @since  1.0.0.
 *
 * @param  string    $css    The current CSS.
 * @return string            The modified CSS.
 */
function ttf_one_display_colors( $css ) {
	$color_primary   = maybe_hash_hex_color( get_theme_mod( 'color-primary', '#ffffff' ) );
	$color_secondary = maybe_hash_hex_color( get_theme_mod( 'color-secondary', '#ffffff' ) );
	$color_text      = maybe_hash_hex_color( get_theme_mod( 'color-text', '#ffffff' ) );
	$color_accent    = maybe_hash_hex_color( get_theme_mod( 'color-accent', '#ffffff' ) );

	// All variables are escaped at this point
	$css .= 'a{color:' . $color_primary . ';}button{color:' . $color_primary . ';}p{color:' . $color_text . ';}';

	return $css;
}
endif;

add_filter( 'ttf_one_css', 'ttf_one_display_colors' );

if ( ! function_exists( 'ttf_one_display_favicons' ) ) :
/**
 * Write the favicons to the head to implement the options.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttf_one_display_favicons() {
	$logo_favicon = get_theme_mod( 'logo-favicon', false );
	if ( false !== $logo_favicon ) : ?>
		<link rel="icon" href="<?php echo esc_url( $logo_favicon ); ?>" />
	<?php endif;

	$logo_apple_touch = get_theme_mod( 'logo-apple-touch', false );
	if ( false !== $logo_apple_touch ) : ?>
		<link rel="apple-touch-icon" href="<?php echo esc_url( $logo_apple_touch ); ?>" />
	<?php endif;
}
endif;

add_action( 'wp_head', 'ttf_one_display_favicons' );

if ( ! function_exists( 'ttf_one_display_header_background' ) ) :
/**
 * Write the CSS to implement the header background option.
 *
 * @since  1.0.0.
 *
 * @param  string    $css    The current CSS.
 * @return string            The modified CSS.
 */
function ttf_one_display_header_background( $css ) {
	$background_color = maybe_hash_hex_color( get_theme_mod( 'header-background-color', '#ffffff' ) );
	$css .= '.site-header{background-color:' . $background_color . ';}';

	return $css;
}
endif;

add_filter( 'ttf_one_css', 'ttf_one_display_header_background' );

if ( ! function_exists( 'ttf_one_display_footer_background' ) ) :
/**
 * Write the CSS to implement the footer background option.
 *
 * @since  1.0.0.
 *
 * @param  string    $css    The current CSS.
 * @return string            The modified CSS.
 */
function ttf_one_display_footer_background( $css ) {
	$background_color = maybe_hash_hex_color( get_theme_mod( 'footer-background-color', '#ffffff' ) );
	$css .= '.site-footer{background-color:' . $background_color . ';}';

	return $css;
}
endif;

add_filter( 'ttf_one_css', 'ttf_one_display_footer_background' );

function ttf_one_print_layout_classes( $classes ) {
	$classes[] = get_theme_mod( 'footer-layout', 'footer-layout-1' );
	$classes[] = get_theme_mod( 'header-layout', 'header-layout-1' );
	return $classes;
}

add_filter( 'body_class', 'ttf_one_print_layout_classes' );

if ( ! function_exists( 'ttf_one_css_fonts' ) ) :
/**
 * Build the CSS rules for the custom fonts
 *
 * @since  1.0.0.
 *
 * @param  string    $css    The current CSS.
 * @return string            The modified CSS.
 */
function ttf_one_css_fonts( $css ) {
	$font_option_keys = array( 'font-body', 'font-site-title', 'font-header'  );
	$fonts = array();

	foreach ( $font_option_keys as $key ) {
		$fonts[$key] = get_theme_mod( $key, 'Open Sans' );
	}

	foreach ( $fonts as $key => $name ) {
		switch ( $key ) {
			case 'font-body' :
				$css .= ".font-body,body{font-family:" . esc_html( $name ) . ";}";
				break;
			case 'font-site-title' :
				$css .= ".font-site-title,.site-title{font-family:" . esc_html( $name ) . ";}";
				break;
			case 'font-header' :
				$css .= ".font-header,h1,h2,h3,h4,h5,h6{font-family:" . esc_html( $name ) . ";}";
				break;
		}
	}

	return $css;
}
endif;

if ( ! function_exists( 'ttf_one_get_google_font_request' ) ) :
/**
 * Build the HTTP request URL for Google fonts
 *
 * @since 1.0.0
 *
 * @param array $fonts The names of the fonts to include in the request.
 *
 * @return string
 */
function ttf_one_get_google_font_request( $fonts = array() ) {
	// Grab the theme options if no fonts are specified
	if ( empty( $fonts ) ) {
		$font_option_keys = array( 'font-site-title', 'font-header', 'font-body' );
		foreach ( $font_option_keys as $key ) {
			$fonts[] = get_theme_mod( $key, 'Open Sans' );
		}
	}

	// De-dupe the fonts
	$fonts = array_unique( $fonts );

	$allowed_fonts = ttf_one_get_google_fonts();
	$family = array();

	// Validate each font and convert to URL format
	foreach ( (array) $fonts as $font ) {
		$font = trim( $font );
		if ( in_array( $font, array_keys( $allowed_fonts ) ) ) {
			$family[] = str_replace( ' ', '+', $font );
		}
	}

	$request = '';

	// Convert from array to string
	if ( ! empty( $family ) ) {
		$request = '//fonts.googleapis.com/css?family=' . implode( '|', $family );
	}

	return esc_url( $request );
}
endif;

if ( ! function_exists( 'ttf_one_get_google_fonts' ) ) :
/**
 * Return an array of all available Google Fonts.
 *
 * @since  1.0.
 *
 * @return array    All Google Fonts.
 */
function ttf_one_get_google_fonts() {
	return array(
		'ABeeZee'                  => 'ABeeZee',
		'Abel'                     => 'Abel',
		'Abril Fatface'            => 'Abril Fatface',
		'Aclonica'                 => 'Aclonica',
		'Acme'                     => 'Acme',
		'Actor'                    => 'Actor',
		'Adamina'                  => 'Adamina',
		'Advent Pro'               => 'Advent Pro',
		'Aguafina Script'          => 'Aguafina Script',
		'Akronim'                  => 'Akronim',
		'Aladin'                   => 'Aladin',
		'Aldrich'                  => 'Aldrich',
		'Alef'                     => 'Alef',
		'Alegreya'                 => 'Alegreya',
		'Alegreya SC'              => 'Alegreya SC',
		'Alegreya Sans'            => 'Alegreya Sans',
		'Alegreya Sans SC'         => 'Alegreya Sans SC',
		'Alex Brush'               => 'Alex Brush',
		'Alfa Slab One'            => 'Alfa Slab One',
		'Alice'                    => 'Alice',
		'Alike'                    => 'Alike',
		'Alike Angular'            => 'Alike Angular',
		'Allan'                    => 'Allan',
		'Allerta'                  => 'Allerta',
		'Allerta Stencil'          => 'Allerta Stencil',
		'Allura'                   => 'Allura',
		'Almendra'                 => 'Almendra',
		'Almendra Display'         => 'Almendra Display',
		'Almendra SC'              => 'Almendra SC',
		'Amarante'                 => 'Amarante',
		'Amaranth'                 => 'Amaranth',
		'Amatic SC'                => 'Amatic SC',
		'Amethysta'                => 'Amethysta',
		'Anaheim'                  => 'Anaheim',
		'Andada'                   => 'Andada',
		'Andika'                   => 'Andika',
		'Angkor'                   => 'Angkor',
		'Annie Use Your Telescope' => 'Annie Use Your Telescope',
		'Anonymous Pro'            => 'Anonymous Pro',
		'Antic'                    => 'Antic',
		'Antic Didone'             => 'Antic Didone',
		'Antic Slab'               => 'Antic Slab',
		'Anton'                    => 'Anton',
		'Arapey'                   => 'Arapey',
		'Arbutus'                  => 'Arbutus',
		'Arbutus Slab'             => 'Arbutus Slab',
		'Architects Daughter'      => 'Architects Daughter',
		'Archivo Black'            => 'Archivo Black',
		'Archivo Narrow'           => 'Archivo Narrow',
		'Arimo'                    => 'Arimo',
		'Arizonia'                 => 'Arizonia',
		'Armata'                   => 'Armata',
		'Artifika'                 => 'Artifika',
		'Arvo'                     => 'Arvo',
		'Asap'                     => 'Asap',
		'Asset'                    => 'Asset',
		'Astloch'                  => 'Astloch',
		'Asul'                     => 'Asul',
		'Atomic Age'               => 'Atomic Age',
		'Aubrey'                   => 'Aubrey',
		'Audiowide'                => 'Audiowide',
		'Autour One'               => 'Autour One',
		'Average'                  => 'Average',
		'Average Sans'             => 'Average Sans',
		'Averia Gruesa Libre'      => 'Averia Gruesa Libre',
		'Averia Libre'             => 'Averia Libre',
		'Averia Sans Libre'        => 'Averia Sans Libre',
		'Averia Serif Libre'       => 'Averia Serif Libre',
		'Bad Script'               => 'Bad Script',
		'Balthazar'                => 'Balthazar',
		'Bangers'                  => 'Bangers',
		'Basic'                    => 'Basic',
		'Battambang'               => 'Battambang',
		'Baumans'                  => 'Baumans',
		'Bayon'                    => 'Bayon',
		'Belgrano'                 => 'Belgrano',
		'Belleza'                  => 'Belleza',
		'BenchNine'                => 'BenchNine',
		'Bentham'                  => 'Bentham',
		'Berkshire Swash'          => 'Berkshire Swash',
		'Bevan'                    => 'Bevan',
		'Bigelow Rules'            => 'Bigelow Rules',
		'Bigshot One'              => 'Bigshot One',
		'Bilbo'                    => 'Bilbo',
		'Bilbo Swash Caps'         => 'Bilbo Swash Caps',
		'Bitter'                   => 'Bitter',
		'Black Ops One'            => 'Black Ops One',
		'Bokor'                    => 'Bokor',
		'Bonbon'                   => 'Bonbon',
		'Boogaloo'                 => 'Boogaloo',
		'Bowlby One'               => 'Bowlby One',
		'Bowlby One SC'            => 'Bowlby One SC',
		'Brawler'                  => 'Brawler',
		'Bree Serif'               => 'Bree Serif',
		'Bubblegum Sans'           => 'Bubblegum Sans',
		'Bubbler One'              => 'Bubbler One',
		'Buda'                     => 'Buda',
		'Buenard'                  => 'Buenard',
		'Butcherman'               => 'Butcherman',
		'Butterfly Kids'           => 'Butterfly Kids',
		'Cabin'                    => 'Cabin',
		'Cabin Condensed'          => 'Cabin Condensed',
		'Cabin Sketch'             => 'Cabin Sketch',
		'Caesar Dressing'          => 'Caesar Dressing',
		'Cagliostro'               => 'Cagliostro',
		'Calligraffitti'           => 'Calligraffitti',
		'Cambo'                    => 'Cambo',
		'Candal'                   => 'Candal',
		'Cantarell'                => 'Cantarell',
		'Cantata One'              => 'Cantata One',
		'Cantora One'              => 'Cantora One',
		'Capriola'                 => 'Capriola',
		'Cardo'                    => 'Cardo',
		'Carme'                    => 'Carme',
		'Carrois Gothic'           => 'Carrois Gothic',
		'Carrois Gothic SC'        => 'Carrois Gothic SC',
		'Carter One'               => 'Carter One',
		'Caudex'                   => 'Caudex',
		'Cedarville Cursive'       => 'Cedarville Cursive',
		'Ceviche One'              => 'Ceviche One',
		'Changa One'               => 'Changa One',
		'Chango'                   => 'Chango',
		'Chau Philomene One'       => 'Chau Philomene One',
		'Chela One'                => 'Chela One',
		'Chelsea Market'           => 'Chelsea Market',
		'Chenla'                   => 'Chenla',
		'Cherry Cream Soda'        => 'Cherry Cream Soda',
		'Cherry Swash'             => 'Cherry Swash',
		'Chewy'                    => 'Chewy',
		'Chicle'                   => 'Chicle',
		'Chivo'                    => 'Chivo',
		'Cinzel'                   => 'Cinzel',
		'Cinzel Decorative'        => 'Cinzel Decorative',
		'Clicker Script'           => 'Clicker Script',
		'Coda'                     => 'Coda',
		'Coda Caption'             => 'Coda Caption',
		'Codystar'                 => 'Codystar',
		'Combo'                    => 'Combo',
		'Comfortaa'                => 'Comfortaa',
		'Coming Soon'              => 'Coming Soon',
		'Concert One'              => 'Concert One',
		'Condiment'                => 'Condiment',
		'Content'                  => 'Content',
		'Contrail One'             => 'Contrail One',
		'Convergence'              => 'Convergence',
		'Cookie'                   => 'Cookie',
		'Copse'                    => 'Copse',
		'Corben'                   => 'Corben',
		'Courgette'                => 'Courgette',
		'Cousine'                  => 'Cousine',
		'Coustard'                 => 'Coustard',
		'Covered By Your Grace'    => 'Covered By Your Grace',
		'Crafty Girls'             => 'Crafty Girls',
		'Creepster'                => 'Creepster',
		'Crete Round'              => 'Crete Round',
		'Crimson Text'             => 'Crimson Text',
		'Croissant One'            => 'Croissant One',
		'Crushed'                  => 'Crushed',
		'Cuprum'                   => 'Cuprum',
		'Cutive'                   => 'Cutive',
		'Cutive Mono'              => 'Cutive Mono',
		'Damion'                   => 'Damion',
		'Dancing Script'           => 'Dancing Script',
		'Dangrek'                  => 'Dangrek',
		'Dawning of a New Day'     => 'Dawning of a New Day',
		'Days One'                 => 'Days One',
		'Delius'                   => 'Delius',
		'Delius Swash Caps'        => 'Delius Swash Caps',
		'Delius Unicase'           => 'Delius Unicase',
		'Della Respira'            => 'Della Respira',
		'Denk One'                 => 'Denk One',
		'Devonshire'               => 'Devonshire',
		'Didact Gothic'            => 'Didact Gothic',
		'Diplomata'                => 'Diplomata',
		'Diplomata SC'             => 'Diplomata SC',
		'Domine'                   => 'Domine',
		'Donegal One'              => 'Donegal One',
		'Doppio One'               => 'Doppio One',
		'Dorsa'                    => 'Dorsa',
		'Dosis'                    => 'Dosis',
		'Dr Sugiyama'              => 'Dr Sugiyama',
		'Droid Sans'               => 'Droid Sans',
		'Droid Sans Mono'          => 'Droid Sans Mono',
		'Droid Serif'              => 'Droid Serif',
		'Duru Sans'                => 'Duru Sans',
		'Dynalight'                => 'Dynalight',
		'EB Garamond'              => 'EB Garamond',
		'Eagle Lake'               => 'Eagle Lake',
		'Eater'                    => 'Eater',
		'Economica'                => 'Economica',
		'Electrolize'              => 'Electrolize',
		'Elsie'                    => 'Elsie',
		'Elsie Swash Caps'         => 'Elsie Swash Caps',
		'Emblema One'              => 'Emblema One',
		'Emilys Candy'             => 'Emilys Candy',
		'Engagement'               => 'Engagement',
		'Englebert'                => 'Englebert',
		'Enriqueta'                => 'Enriqueta',
		'Erica One'                => 'Erica One',
		'Esteban'                  => 'Esteban',
		'Euphoria Script'          => 'Euphoria Script',
		'Ewert'                    => 'Ewert',
		'Exo'                      => 'Exo',
		'Exo 2'                    => 'Exo 2',
		'Expletus Sans'            => 'Expletus Sans',
		'Fanwood Text'             => 'Fanwood Text',
		'Fascinate'                => 'Fascinate',
		'Fascinate Inline'         => 'Fascinate Inline',
		'Faster One'               => 'Faster One',
		'Fasthand'                 => 'Fasthand',
		'Fauna One'                => 'Fauna One',
		'Federant'                 => 'Federant',
		'Federo'                   => 'Federo',
		'Felipa'                   => 'Felipa',
		'Fenix'                    => 'Fenix',
		'Finger Paint'             => 'Finger Paint',
		'Fjalla One'               => 'Fjalla One',
		'Fjord One'                => 'Fjord One',
		'Flamenco'                 => 'Flamenco',
		'Flavors'                  => 'Flavors',
		'Fondamento'               => 'Fondamento',
		'Fontdiner Swanky'         => 'Fontdiner Swanky',
		'Forum'                    => 'Forum',
		'Francois One'             => 'Francois One',
		'Freckle Face'             => 'Freckle Face',
		'Fredericka the Great'     => 'Fredericka the Great',
		'Fredoka One'              => 'Fredoka One',
		'Freehand'                 => 'Freehand',
		'Fresca'                   => 'Fresca',
		'Frijole'                  => 'Frijole',
		'Fruktur'                  => 'Fruktur',
		'Fugaz One'                => 'Fugaz One',
		'GFS Didot'                => 'GFS Didot',
		'GFS Neohellenic'          => 'GFS Neohellenic',
		'Gabriela'                 => 'Gabriela',
		'Gafata'                   => 'Gafata',
		'Galdeano'                 => 'Galdeano',
		'Galindo'                  => 'Galindo',
		'Gentium Basic'            => 'Gentium Basic',
		'Gentium Book Basic'       => 'Gentium Book Basic',
		'Geo'                      => 'Geo',
		'Geostar'                  => 'Geostar',
		'Geostar Fill'             => 'Geostar Fill',
		'Germania One'             => 'Germania One',
		'Gilda Display'            => 'Gilda Display',
		'Give You Glory'           => 'Give You Glory',
		'Glass Antiqua'            => 'Glass Antiqua',
		'Glegoo'                   => 'Glegoo',
		'Gloria Hallelujah'        => 'Gloria Hallelujah',
		'Goblin One'               => 'Goblin One',
		'Gochi Hand'               => 'Gochi Hand',
		'Gorditas'                 => 'Gorditas',
		'Goudy Bookletter 1911'    => 'Goudy Bookletter 1911',
		'Graduate'                 => 'Graduate',
		'Grand Hotel'              => 'Grand Hotel',
		'Gravitas One'             => 'Gravitas One',
		'Great Vibes'              => 'Great Vibes',
		'Griffy'                   => 'Griffy',
		'Gruppo'                   => 'Gruppo',
		'Gudea'                    => 'Gudea',
		'Habibi'                   => 'Habibi',
		'Hammersmith One'          => 'Hammersmith One',
		'Hanalei'                  => 'Hanalei',
		'Hanalei Fill'             => 'Hanalei Fill',
		'Handlee'                  => 'Handlee',
		'Hanuman'                  => 'Hanuman',
		'Happy Monkey'             => 'Happy Monkey',
		'Headland One'             => 'Headland One',
		'Henny Penny'              => 'Henny Penny',
		'Herr Von Muellerhoff'     => 'Herr Von Muellerhoff',
		'Holtwood One SC'          => 'Holtwood One SC',
		'Homemade Apple'           => 'Homemade Apple',
		'Homenaje'                 => 'Homenaje',
		'IM Fell DW Pica'          => 'IM Fell DW Pica',
		'IM Fell DW Pica SC'       => 'IM Fell DW Pica SC',
		'IM Fell Double Pica'      => 'IM Fell Double Pica',
		'IM Fell Double Pica SC'   => 'IM Fell Double Pica SC',
		'IM Fell English'          => 'IM Fell English',
		'IM Fell English SC'       => 'IM Fell English SC',
		'IM Fell French Canon'     => 'IM Fell French Canon',
		'IM Fell French Canon SC'  => 'IM Fell French Canon SC',
		'IM Fell Great Primer'     => 'IM Fell Great Primer',
		'IM Fell Great Primer SC'  => 'IM Fell Great Primer SC',
		'Iceberg'                  => 'Iceberg',
		'Iceland'                  => 'Iceland',
		'Imprima'                  => 'Imprima',
		'Inconsolata'              => 'Inconsolata',
		'Inder'                    => 'Inder',
		'Indie Flower'             => 'Indie Flower',
		'Inika'                    => 'Inika',
		'Irish Grover'             => 'Irish Grover',
		'Istok Web'                => 'Istok Web',
		'Italiana'                 => 'Italiana',
		'Italianno'                => 'Italianno',
		'Jacques Francois'         => 'Jacques Francois',
		'Jacques Francois Shadow'  => 'Jacques Francois Shadow',
		'Jim Nightshade'           => 'Jim Nightshade',
		'Jockey One'               => 'Jockey One',
		'Jolly Lodger'             => 'Jolly Lodger',
		'Josefin Sans'             => 'Josefin Sans',
		'Josefin Slab'             => 'Josefin Slab',
		'Joti One'                 => 'Joti One',
		'Judson'                   => 'Judson',
		'Julee'                    => 'Julee',
		'Julius Sans One'          => 'Julius Sans One',
		'Junge'                    => 'Junge',
		'Jura'                     => 'Jura',
		'Just Another Hand'        => 'Just Another Hand',
		'Just Me Again Down Here'  => 'Just Me Again Down Here',
		'Kameron'                  => 'Kameron',
		'Kantumruy'                => 'Kantumruy',
		'Karla'                    => 'Karla',
		'Kaushan Script'           => 'Kaushan Script',
		'Kavoon'                   => 'Kavoon',
		'Kdam Thmor'               => 'Kdam Thmor',
		'Keania One'               => 'Keania One',
		'Kelly Slab'               => 'Kelly Slab',
		'Kenia'                    => 'Kenia',
		'Khmer'                    => 'Khmer',
		'Kite One'                 => 'Kite One',
		'Knewave'                  => 'Knewave',
		'Kotta One'                => 'Kotta One',
		'Koulen'                   => 'Koulen',
		'Kranky'                   => 'Kranky',
		'Kreon'                    => 'Kreon',
		'Kristi'                   => 'Kristi',
		'Krona One'                => 'Krona One',
		'La Belle Aurore'          => 'La Belle Aurore',
		'Lancelot'                 => 'Lancelot',
		'Lato'                     => 'Lato',
		'League Script'            => 'League Script',
		'Leckerli One'             => 'Leckerli One',
		'Ledger'                   => 'Ledger',
		'Lekton'                   => 'Lekton',
		'Lemon'                    => 'Lemon',
		'Libre Baskerville'        => 'Libre Baskerville',
		'Life Savers'              => 'Life Savers',
		'Lilita One'               => 'Lilita One',
		'Lily Script One'          => 'Lily Script One',
		'Limelight'                => 'Limelight',
		'Linden Hill'              => 'Linden Hill',
		'Lobster'                  => 'Lobster',
		'Lobster Two'              => 'Lobster Two',
		'Londrina Outline'         => 'Londrina Outline',
		'Londrina Shadow'          => 'Londrina Shadow',
		'Londrina Sketch'          => 'Londrina Sketch',
		'Londrina Solid'           => 'Londrina Solid',
		'Lora'                     => 'Lora',
		'Love Ya Like A Sister'    => 'Love Ya Like A Sister',
		'Loved by the King'        => 'Loved by the King',
		'Lovers Quarrel'           => 'Lovers Quarrel',
		'Luckiest Guy'             => 'Luckiest Guy',
		'Lusitana'                 => 'Lusitana',
		'Lustria'                  => 'Lustria',
		'Macondo'                  => 'Macondo',
		'Macondo Swash Caps'       => 'Macondo Swash Caps',
		'Magra'                    => 'Magra',
		'Maiden Orange'            => 'Maiden Orange',
		'Mako'                     => 'Mako',
		'Marcellus'                => 'Marcellus',
		'Marcellus SC'             => 'Marcellus SC',
		'Marck Script'             => 'Marck Script',
		'Margarine'                => 'Margarine',
		'Marko One'                => 'Marko One',
		'Marmelad'                 => 'Marmelad',
		'Marvel'                   => 'Marvel',
		'Mate'                     => 'Mate',
		'Mate SC'                  => 'Mate SC',
		'Maven Pro'                => 'Maven Pro',
		'McLaren'                  => 'McLaren',
		'Meddon'                   => 'Meddon',
		'MedievalSharp'            => 'MedievalSharp',
		'Medula One'               => 'Medula One',
		'Megrim'                   => 'Megrim',
		'Meie Script'              => 'Meie Script',
		'Merienda'                 => 'Merienda',
		'Merienda One'             => 'Merienda One',
		'Merriweather'             => 'Merriweather',
		'Merriweather Sans'        => 'Merriweather Sans',
		'Metal'                    => 'Metal',
		'Metal Mania'              => 'Metal Mania',
		'Metamorphous'             => 'Metamorphous',
		'Metrophobic'              => 'Metrophobic',
		'Michroma'                 => 'Michroma',
		'Milonga'                  => 'Milonga',
		'Miltonian'                => 'Miltonian',
		'Miltonian Tattoo'         => 'Miltonian Tattoo',
		'Miniver'                  => 'Miniver',
		'Miss Fajardose'           => 'Miss Fajardose',
		'Modern Antiqua'           => 'Modern Antiqua',
		'Molengo'                  => 'Molengo',
		'Molle'                    => 'Molle',
		'Monda'                    => 'Monda',
		'Monofett'                 => 'Monofett',
		'Monoton'                  => 'Monoton',
		'Monsieur La Doulaise'     => 'Monsieur La Doulaise',
		'Montaga'                  => 'Montaga',
		'Montez'                   => 'Montez',
		'Montserrat'               => 'Montserrat',
		'Montserrat Alternates'    => 'Montserrat Alternates',
		'Montserrat Subrayada'     => 'Montserrat Subrayada',
		'Moul'                     => 'Moul',
		'Moulpali'                 => 'Moulpali',
		'Mountains of Christmas'   => 'Mountains of Christmas',
		'Mouse Memoirs'            => 'Mouse Memoirs',
		'Mr Bedfort'               => 'Mr Bedfort',
		'Mr Dafoe'                 => 'Mr Dafoe',
		'Mr De Haviland'           => 'Mr De Haviland',
		'Mrs Saint Delafield'      => 'Mrs Saint Delafield',
		'Mrs Sheppards'            => 'Mrs Sheppards',
		'Muli'                     => 'Muli',
		'Mystery Quest'            => 'Mystery Quest',
		'Neucha'                   => 'Neucha',
		'Neuton'                   => 'Neuton',
		'New Rocker'               => 'New Rocker',
		'News Cycle'               => 'News Cycle',
		'Niconne'                  => 'Niconne',
		'Nixie One'                => 'Nixie One',
		'Nobile'                   => 'Nobile',
		'Nokora'                   => 'Nokora',
		'Norican'                  => 'Norican',
		'Nosifer'                  => 'Nosifer',
		'Nothing You Could Do'     => 'Nothing You Could Do',
		'Noticia Text'             => 'Noticia Text',
		'Noto Sans'                => 'Noto Sans',
		'Noto Serif'               => 'Noto Serif',
		'Nova Cut'                 => 'Nova Cut',
		'Nova Flat'                => 'Nova Flat',
		'Nova Mono'                => 'Nova Mono',
		'Nova Oval'                => 'Nova Oval',
		'Nova Round'               => 'Nova Round',
		'Nova Script'              => 'Nova Script',
		'Nova Slim'                => 'Nova Slim',
		'Nova Square'              => 'Nova Square',
		'Numans'                   => 'Numans',
		'Nunito'                   => 'Nunito',
		'Odor Mean Chey'           => 'Odor Mean Chey',
		'Offside'                  => 'Offside',
		'Old Standard TT'          => 'Old Standard TT',
		'Oldenburg'                => 'Oldenburg',
		'Oleo Script'              => 'Oleo Script',
		'Oleo Script Swash Caps'   => 'Oleo Script Swash Caps',
		'Open Sans'                => 'Open Sans',
		'Open Sans Condensed'      => 'Open Sans Condensed',
		'Oranienbaum'              => 'Oranienbaum',
		'Orbitron'                 => 'Orbitron',
		'Oregano'                  => 'Oregano',
		'Orienta'                  => 'Orienta',
		'Original Surfer'          => 'Original Surfer',
		'Oswald'                   => 'Oswald',
		'Over the Rainbow'         => 'Over the Rainbow',
		'Overlock'                 => 'Overlock',
		'Overlock SC'              => 'Overlock SC',
		'Ovo'                      => 'Ovo',
		'Oxygen'                   => 'Oxygen',
		'Oxygen Mono'              => 'Oxygen Mono',
		'PT Mono'                  => 'PT Mono',
		'PT Sans'                  => 'PT Sans',
		'PT Sans Caption'          => 'PT Sans Caption',
		'PT Sans Narrow'           => 'PT Sans Narrow',
		'PT Serif'                 => 'PT Serif',
		'PT Serif Caption'         => 'PT Serif Caption',
		'Pacifico'                 => 'Pacifico',
		'Paprika'                  => 'Paprika',
		'Parisienne'               => 'Parisienne',
		'Passero One'              => 'Passero One',
		'Passion One'              => 'Passion One',
		'Pathway Gothic One'       => 'Pathway Gothic One',
		'Patrick Hand'             => 'Patrick Hand',
		'Patrick Hand SC'          => 'Patrick Hand SC',
		'Patua One'                => 'Patua One',
		'Paytone One'              => 'Paytone One',
		'Peralta'                  => 'Peralta',
		'Permanent Marker'         => 'Permanent Marker',
		'Petit Formal Script'      => 'Petit Formal Script',
		'Petrona'                  => 'Petrona',
		'Philosopher'              => 'Philosopher',
		'Piedra'                   => 'Piedra',
		'Pinyon Script'            => 'Pinyon Script',
		'Pirata One'               => 'Pirata One',
		'Plaster'                  => 'Plaster',
		'Play'                     => 'Play',
		'Playball'                 => 'Playball',
		'Playfair Display'         => 'Playfair Display',
		'Playfair Display SC'      => 'Playfair Display SC',
		'Podkova'                  => 'Podkova',
		'Poiret One'               => 'Poiret One',
		'Poller One'               => 'Poller One',
		'Poly'                     => 'Poly',
		'Pompiere'                 => 'Pompiere',
		'Pontano Sans'             => 'Pontano Sans',
		'Port Lligat Sans'         => 'Port Lligat Sans',
		'Port Lligat Slab'         => 'Port Lligat Slab',
		'Prata'                    => 'Prata',
		'Preahvihear'              => 'Preahvihear',
		'Press Start 2P'           => 'Press Start 2P',
		'Princess Sofia'           => 'Princess Sofia',
		'Prociono'                 => 'Prociono',
		'Prosto One'               => 'Prosto One',
		'Puritan'                  => 'Puritan',
		'Purple Purse'             => 'Purple Purse',
		'Quando'                   => 'Quando',
		'Quantico'                 => 'Quantico',
		'Quattrocento'             => 'Quattrocento',
		'Quattrocento Sans'        => 'Quattrocento Sans',
		'Questrial'                => 'Questrial',
		'Quicksand'                => 'Quicksand',
		'Quintessential'           => 'Quintessential',
		'Qwigley'                  => 'Qwigley',
		'Racing Sans One'          => 'Racing Sans One',
		'Radley'                   => 'Radley',
		'Raleway'                  => 'Raleway',
		'Raleway Dots'             => 'Raleway Dots',
		'Rambla'                   => 'Rambla',
		'Rammetto One'             => 'Rammetto One',
		'Ranchers'                 => 'Ranchers',
		'Rancho'                   => 'Rancho',
		'Rationale'                => 'Rationale',
		'Redressed'                => 'Redressed',
		'Reenie Beanie'            => 'Reenie Beanie',
		'Revalia'                  => 'Revalia',
		'Ribeye'                   => 'Ribeye',
		'Ribeye Marrow'            => 'Ribeye Marrow',
		'Righteous'                => 'Righteous',
		'Risque'                   => 'Risque',
		'Roboto'                   => 'Roboto',
		'Roboto Condensed'         => 'Roboto Condensed',
		'Roboto Slab'              => 'Roboto Slab',
		'Rochester'                => 'Rochester',
		'Rock Salt'                => 'Rock Salt',
		'Rokkitt'                  => 'Rokkitt',
		'Romanesco'                => 'Romanesco',
		'Ropa Sans'                => 'Ropa Sans',
		'Rosario'                  => 'Rosario',
		'Rosarivo'                 => 'Rosarivo',
		'Rouge Script'             => 'Rouge Script',
		'Ruda'                     => 'Ruda',
		'Rufina'                   => 'Rufina',
		'Ruge Boogie'              => 'Ruge Boogie',
		'Ruluko'                   => 'Ruluko',
		'Rum Raisin'               => 'Rum Raisin',
		'Ruslan Display'           => 'Ruslan Display',
		'Russo One'                => 'Russo One',
		'Ruthie'                   => 'Ruthie',
		'Rye'                      => 'Rye',
		'Sacramento'               => 'Sacramento',
		'Sail'                     => 'Sail',
		'Salsa'                    => 'Salsa',
		'Sanchez'                  => 'Sanchez',
		'Sancreek'                 => 'Sancreek',
		'Sansita One'              => 'Sansita One',
		'Sarina'                   => 'Sarina',
		'Satisfy'                  => 'Satisfy',
		'Scada'                    => 'Scada',
		'Schoolbell'               => 'Schoolbell',
		'Seaweed Script'           => 'Seaweed Script',
		'Sevillana'                => 'Sevillana',
		'Seymour One'              => 'Seymour One',
		'Shadows Into Light'       => 'Shadows Into Light',
		'Shadows Into Light Two'   => 'Shadows Into Light Two',
		'Shanti'                   => 'Shanti',
		'Share'                    => 'Share',
		'Share Tech'               => 'Share Tech',
		'Share Tech Mono'          => 'Share Tech Mono',
		'Shojumaru'                => 'Shojumaru',
		'Short Stack'              => 'Short Stack',
		'Siemreap'                 => 'Siemreap',
		'Sigmar One'               => 'Sigmar One',
		'Signika'                  => 'Signika',
		'Signika Negative'         => 'Signika Negative',
		'Simonetta'                => 'Simonetta',
		'Sintony'                  => 'Sintony',
		'Sirin Stencil'            => 'Sirin Stencil',
		'Six Caps'                 => 'Six Caps',
		'Skranji'                  => 'Skranji',
		'Slackey'                  => 'Slackey',
		'Smokum'                   => 'Smokum',
		'Smythe'                   => 'Smythe',
		'Sniglet'                  => 'Sniglet',
		'Snippet'                  => 'Snippet',
		'Snowburst One'            => 'Snowburst One',
		'Sofadi One'               => 'Sofadi One',
		'Sofia'                    => 'Sofia',
		'Sonsie One'               => 'Sonsie One',
		'Sorts Mill Goudy'         => 'Sorts Mill Goudy',
		'Source Code Pro'          => 'Source Code Pro',
		'Source Sans Pro'          => 'Source Sans Pro',
		'Special Elite'            => 'Special Elite',
		'Spicy Rice'               => 'Spicy Rice',
		'Spinnaker'                => 'Spinnaker',
		'Spirax'                   => 'Spirax',
		'Squada One'               => 'Squada One',
		'Stalemate'                => 'Stalemate',
		'Stalinist One'            => 'Stalinist One',
		'Stardos Stencil'          => 'Stardos Stencil',
		'Stint Ultra Condensed'    => 'Stint Ultra Condensed',
		'Stint Ultra Expanded'     => 'Stint Ultra Expanded',
		'Stoke'                    => 'Stoke',
		'Strait'                   => 'Strait',
		'Sue Ellen Francisco'      => 'Sue Ellen Francisco',
		'Sunshiney'                => 'Sunshiney',
		'Supermercado One'         => 'Supermercado One',
		'Suwannaphum'              => 'Suwannaphum',
		'Swanky and Moo Moo'       => 'Swanky and Moo Moo',
		'Syncopate'                => 'Syncopate',
		'Tangerine'                => 'Tangerine',
		'Taprom'                   => 'Taprom',
		'Tauri'                    => 'Tauri',
		'Telex'                    => 'Telex',
		'Tenor Sans'               => 'Tenor Sans',
		'Text Me One'              => 'Text Me One',
		'The Girl Next Door'       => 'The Girl Next Door',
		'Tienne'                   => 'Tienne',
		'Tinos'                    => 'Tinos',
		'Titan One'                => 'Titan One',
		'Titillium Web'            => 'Titillium Web',
		'Trade Winds'              => 'Trade Winds',
		'Trocchi'                  => 'Trocchi',
		'Trochut'                  => 'Trochut',
		'Trykker'                  => 'Trykker',
		'Tulpen One'               => 'Tulpen One',
		'Ubuntu'                   => 'Ubuntu',
		'Ubuntu Condensed'         => 'Ubuntu Condensed',
		'Ubuntu Mono'              => 'Ubuntu Mono',
		'Ultra'                    => 'Ultra',
		'Uncial Antiqua'           => 'Uncial Antiqua',
		'Underdog'                 => 'Underdog',
		'Unica One'                => 'Unica One',
		'UnifrakturCook'           => 'UnifrakturCook',
		'UnifrakturMaguntia'       => 'UnifrakturMaguntia',
		'Unkempt'                  => 'Unkempt',
		'Unlock'                   => 'Unlock',
		'Unna'                     => 'Unna',
		'VT323'                    => 'VT323',
		'Vampiro One'              => 'Vampiro One',
		'Varela'                   => 'Varela',
		'Varela Round'             => 'Varela Round',
		'Vast Shadow'              => 'Vast Shadow',
		'Vibur'                    => 'Vibur',
		'Vidaloka'                 => 'Vidaloka',
		'Viga'                     => 'Viga',
		'Voces'                    => 'Voces',
		'Volkhov'                  => 'Volkhov',
		'Vollkorn'                 => 'Vollkorn',
		'Voltaire'                 => 'Voltaire',
		'Waiting for the Sunrise'  => 'Waiting for the Sunrise',
		'Wallpoet'                 => 'Wallpoet',
		'Walter Turncoat'          => 'Walter Turncoat',
		'Warnes'                   => 'Warnes',
		'Wellfleet'                => 'Wellfleet',
		'Wendy One'                => 'Wendy One',
		'Wire One'                 => 'Wire One',
		'Yanone Kaffeesatz'        => 'Yanone Kaffeesatz',
		'Yellowtail'               => 'Yellowtail',
		'Yeseva One'               => 'Yeseva One',
		'Yesteryear'               => 'Yesteryear',
		'Zeyada'                   => 'Zeyada',
	);
}
endif;