<?php
/**
 * functions for Thembay Elementor
 *
 * @package    tbay-elementor
 * @author     Team Thembays <thembayteam@gmail.com >
 * @license    GNU General Public License, version 3
 * @copyright  2015-2016 Thembay Elementor
 */

if ( !function_exists('tbay_elementor_register_widgets_theme') ) {
    function tbay_elementor_register_widgets_theme() {
          
        $widgets = array(
                        'Tbay_Widget_Twitter', 
                        'Tbay_Widget_Facebook_Like_Box', 
                        'Tbay_Widget_Widget_Instagram', 
                        'Tbay_Widget_Banner_Image',
                        'Tbay_Widget_Custom_Menu',
                        'Tbay_Widget_List_Categories',
                        'Tbay_Widget_Popular_Post',
                        'Tbay_Widget_Popup_Newsletter',
                        'Tbay_Widget_Posts',
                        'Tbay_Widget_Recent_Comment',
                        'Tbay_Widget_Recent_Post',
                        'Tbay_Widget_Single_Image',
                        'Tbay_Widget_Socials',
                        'Tbay_Widget_Top_Rate',
                        'Tbay_Widget_Featured_Video'
                    );

        if( defined( 'YITH_WCBR' ) && YITH_WCBR ) {
          array_push($widgets,'Tbay_Widget_Yith_Brand_Images');
        }        

        if ( class_exists( 'WooCommerce' ) ) {
          array_push($widgets,'Tbay_Widget_Woo_Carousel');
        }

        $widgets = apply_filters( 'tbay_elementor_register_widgets_theme', $widgets);


        foreach ($widgets as $widget) {
            if(class_exists($widget)) {
                register_widget( $widget );
            }   
        }
                    
    }

    add_action( 'widgets_init', 'tbay_elementor_register_widgets_theme', 30 );
}

if( ! function_exists( 'tbay_elementor_register_post_types' ) ) {
    function tbay_elementor_register_post_types() {

        $types = array('footer', 'megamenu', 'header');

        $post_types = apply_filters( 'tbay_elementor_register_post_types', $types);
        if ( !empty($post_types) ) {
            foreach ($post_types as $post_type) {
                if ( file_exists( TBAY_ELEMENTOR_DIR . 'classes/post-types/'.$post_type.'.php' ) ) {
                    require TBAY_ELEMENTOR_DIR . 'classes/post-types/'.$post_type.'.php';
                }
            }
        }
    }
}

if( ! function_exists( 'tbay_elementor_widget_init' ) ) {
    function tbay_elementor_widget_init() {
    	$widgets = apply_filters( 'tbay_elementor_register_widgets', array() );
    	if ( !empty($widgets) ) {
    		foreach ($widgets as $widget) {
    			if ( file_exists( TBAY_ELEMENTOR_DIR . 'classes/widgets/'.$widget.'.php' ) ) {
    				require TBAY_ELEMENTOR_DIR . 'classes/widgets/'.$widget.'.php';
    			}
    		}
    	}
    }
}

if( ! function_exists( 'tbay_elementor_get_widget_locate' ) ) {
    function tbay_elementor_get_widget_locate( $name, $plugin_dir = TBAY_ELEMENTOR_DIR ) {
    	$template = '';
    	
    	// Child theme
    	if ( ! $template && ! empty( $name ) && file_exists( get_stylesheet_directory() . "/widgets/{$name}" ) ) {
    		$template = get_stylesheet_directory() . "/widgets/{$name}";
    	}

    	// Original theme
    	if ( ! $template && ! empty( $name ) && file_exists( get_template_directory() . "/widgets/{$name}" ) ) {
    		$template = get_template_directory() . "/widgets/{$name}";
    	}

    	// Plugin
    	if ( ! $template && ! empty( $name ) && file_exists( $plugin_dir . "/templates/widgets/{$name}" ) ) {
    		$template = $plugin_dir . "/templates/widgets/{$name}";
    	}

    	// Nothing found
    	if ( empty( $template ) ) {
    		throw new Exception( "Template /templates/widgets/{$name} in plugin dir {$plugin_dir} not found." );
    	}

    	return $template;
    }
}

if( ! function_exists( 'tbay_elementor_display_svg_image' ) ) {
    function tbay_elementor_display_svg_image( $url, $class = '', $wrap_as_img = true, $attachment_id = null ) {
        if ( ! empty( $url ) && is_string( $url ) ) {

            // we try to inline svgs
            if ( substr( $url, - 4 ) === '.svg' ) {

                //first let's see if we have an attachment and inline it in the safest way - with readfile
                //include is a little dangerous because if one has short_open_tags active, the svg header that starts with <? will be seen as PHP code
                if ( ! empty( $attachment_id ) && false !== @readfile( get_attached_file( $attachment_id ) ) ) {
                    //all good
                } elseif ( false !== ( $svg_code = get_transient( md5( $url ) ) ) ) {
                    //now try to get the svg code from cache
                    echo $svg_code;
                } else {

                    //if not let's get the file contents using WP_Filesystem
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );

                    WP_Filesystem();

                    global $wp_filesystem;
                    
                    $svg_code = $wp_filesystem->get_contents( $url );

                    if ( ! empty( $svg_code ) ) {
                        set_transient( md5( $url ), $svg_code, 12 * HOUR_IN_SECONDS );

                        echo $svg_code;
                    }
                }

            } elseif ( $wrap_as_img ) {

                if ( ! empty( $class ) ) {
                    $class = ' class="' . $class . '"';
                }

                echo '<img src="' . $url . '"' . $class . ' alt="" />';

            } else {
                echo $url;
            }
        }
    }
}


if( ! function_exists( 'tbay_elementor_get_file_contents' ) ) {
    function tbay_elementor_get_file_contents($url, $use_include_path, $context) {
    	return @file_get_contents($url, false, $context);
    }
}

if( ! function_exists( 'tbay_elementor_remove_image_srcset' ) ) {
    function tbay_elementor_remove_image_srcset( $media_item ) {
        add_filter( 'wp_calculate_image_srcset', '__return_false' );
    }
    add_action( 'init', 'tbay_elementor_remove_image_srcset', 10 );
}


if( ! function_exists( 'tbay_elementor_product_add_metaboxes' ) ) {
    add_action( 'add_meta_boxes', 'tbay_elementor_product_add_metaboxes', 50 );
    function tbay_elementor_product_add_metaboxes() {

        if( function_exists( 'tbay_size_guide_metabox_output' ) ) {
            //Add metaboxes size guide to product
            add_meta_box( 'woocommerce-product-size-guide-images', esc_html__( 'Product Size Guide (Only Variable product)', 'tbay-elementor' ), 'tbay_size_guide_metabox_output', 'product', 'side', 'low' );
        }       

        if( function_exists( 'tbay_swatch_attribute_template' ) ) {
            add_meta_box( 'woocommerce-product-swatch-attribute', esc_html__( 'Swatch attribute to display', 'tbay-elementor' ), 'tbay_swatch_attribute_template', 'product', 'side' );    
        }    

        if( function_exists( 'tbay_single_select_single_layout_template' ) ) {
            add_meta_box( 'woocommerce-product-single-layout', esc_html__( 'Select Single Product Layout', 'tbay-elementor' ), 'tbay_single_select_single_layout_template', 'product', 'side' );  
        }

    }
}


if( ! function_exists( 'tbay_calculate_image_srcset' ) ) {
    /**
     * A helper function to calculate the image sources to include in a 'srcset' attribute.
     *
     * @since 4.4.0
     *
     * @param array  $size_array    Array of width and height values in pixels (in that order).
     * @param string $image_src     The 'src' of the image.
     * @param array  $image_meta    The image meta data as returned by 'wp_get_attachment_metadata()'.
     * @param int    $attachment_id Optional. The image attachment ID to pass to the filter. Default 0.
     * @return string|bool          The 'srcset' attribute value. False on error or when only one source exists.
     */
    function tbay_calculate_image_srcset( $size_array, $image_src, $image_meta, $attachment_id = 0 ) {
        /**
         * Let plugins pre-filter the image meta to be able to fix inconsistencies in the stored data.
         *
         * @since 4.5.0
         *
         * @param array  $image_meta    The image meta data as returned by 'wp_get_attachment_metadata()'.
         * @param array  $size_array    Array of width and height values in pixels (in that order).
         * @param string $image_src     The 'src' of the image.
         * @param int    $attachment_id The image attachment ID or 0 if not supplied.
         */
        $image_meta = apply_filters( 'wp_calculate_image_srcset_meta', $image_meta, $size_array, $image_src, $attachment_id );

        if ( empty( $image_meta['sizes'] ) || ! isset( $image_meta['file'] ) || strlen( $image_meta['file'] ) < 4 ) {
            return false;
        }

        $image_sizes = $image_meta['sizes'];

        // Get the width and height of the image.
        $image_width  = (int) $size_array[0];
        $image_height = (int) $size_array[1];

        // Bail early if error/no width.
        if ( $image_width < 1 ) {
            return false;
        }

        $image_basename = wp_basename( $image_meta['file'] );

        /*
         * WordPress flattens animated GIFs into one frame when generating intermediate sizes.
         * To avoid hiding animation in user content, if src is a full size GIF, a srcset attribute is not generated.
         * If src is an intermediate size GIF, the full size is excluded from srcset to keep a flattened GIF from becoming animated.
         */
        if ( ! isset( $image_sizes['thumbnail']['mime-type'] ) || 'image/gif' !== $image_sizes['thumbnail']['mime-type'] ) {
            $image_sizes[] = array(
                'width'  => $image_meta['width'],
                'height' => $image_meta['height'],
                'file'   => $image_basename,
            );
        } elseif ( strpos( $image_src, $image_meta['file'] ) ) {
            return false;
        }

        // Retrieve the uploads sub-directory from the full size image.
        $dirname = _wp_get_attachment_relative_path( $image_meta['file'] );

        if ( $dirname ) {
            $dirname = trailingslashit( $dirname );
        }

        $upload_dir    = wp_get_upload_dir();
        $image_baseurl = trailingslashit( $upload_dir['baseurl'] ) . $dirname;

        /*
         * If currently on HTTPS, prefer HTTPS URLs when we know they're supported by the domain
         * (which is to say, when they share the domain name of the current request).
         */
        if ( is_ssl() && 'https' !== substr( $image_baseurl, 0, 5 ) && parse_url( $image_baseurl, PHP_URL_HOST ) === $_SERVER['HTTP_HOST'] ) {
            $image_baseurl = set_url_scheme( $image_baseurl, 'https' );
        }

        /*
         * Images that have been edited in WordPress after being uploaded will
         * contain a unique hash. Look for that hash and use it later to filter
         * out images that are leftovers from previous versions.
         */
        $image_edited = preg_match( '/-e[0-9]{13}/', wp_basename( $image_src ), $image_edit_hash );

        /**
         * Filters the maximum image width to be included in a 'srcset' attribute.
         *
         * @since 4.4.0
         *
         * @param int   $max_width  The maximum image width to be included in the 'srcset'. Default '1600'.
         * @param array $size_array Array of width and height values in pixels (in that order).
         */
        $max_srcset_image_width = apply_filters( 'max_srcset_image_width', 1600, $size_array );

        // Array to hold URL candidates.
        $sources = array();

        /**
         * To make sure the ID matches our image src, we will check to see if any sizes in our attachment
         * meta match our $image_src. If no matches are found we don't return a srcset to avoid serving
         * an incorrect image. See #35045.
         */
        $src_matched = false;

        /*
         * Loop through available images. Only use images that are resized
         * versions of the same edit.
         */
        foreach ( $image_sizes as $image ) {
            $is_src = false;

            // Check if image meta isn't corrupted.
            if ( ! is_array( $image ) ) {
                continue;
            }

            // If the file name is part of the `src`, we've confirmed a match.
            if ( ! $src_matched && false !== strpos( $image_src, $dirname . $image['file'] ) ) {
                $src_matched = $is_src = true;
            }

            // Filter out images that are from previous edits.
            if ( $image_edited && ! strpos( $image['file'], $image_edit_hash[0] ) ) {
                continue;
            }

            /*
             * Filters out images that are wider than '$max_srcset_image_width' unless
             * that file is in the 'src' attribute.
             */
            if ( $max_srcset_image_width && $image['width'] > $max_srcset_image_width && ! $is_src ) {
                continue;
            }

            // If the image dimensions are within 1px of the expected size, use it.
            if ( wp_image_matches_ratio( $image_width, $image_height, $image['width'], $image['height'] ) ) {
                // Add the URL, descriptor, and value to the sources array to be returned.
                $source = array(
                    'url'        => $image_baseurl . $image['file'],
                    'descriptor' => 'w',
                    'value'      => $image['width'],
                );

                // The 'src' image has to be the first in the 'srcset', because of a bug in iOS8. See #35030.
                if ( $is_src ) {
                    $sources = array( $image['width'] => $source ) + $sources;
                } else {
                    $sources[ $image['width'] ] = $source;
                }
            }
        }

        /**
         * Filters an image's 'srcset' sources.
         *
         * @since 4.4.0
         *
         * @param array  $sources {
         *     One or more arrays of source data to include in the 'srcset'.
         *
         *     @type array $width {
         *         @type string $url        The URL of an image source.
         *         @type string $descriptor The descriptor type used in the image candidate string,
         *                                  either 'w' or 'x'.
         *         @type int    $value      The source width if paired with a 'w' descriptor, or a
         *                                  pixel density value if paired with an 'x' descriptor.
         *     }
         * }
         * @param array  $size_array    Array of width and height values in pixels (in that order).
         * @param string $image_src     The 'src' of the image.
         * @param array  $image_meta    The image meta data as returned by 'wp_get_attachment_metadata()'.
         * @param int    $attachment_id Image attachment ID or 0.
         */
        $sources = apply_filters( 'wp_calculate_image_srcset', $sources, $size_array, $image_src, $image_meta, $attachment_id );

        // Only return a 'srcset' value if there is more than one source.
        if ( ! $src_matched || ! is_array( $sources ) || count( $sources ) < 2 ) {
            return false;
        }

        $srcset = '';

        foreach ( $sources as $source ) {
            $srcset .= str_replace( ' ', '%20', $source['url'] ) . ' ' . $source['value'] . $source['descriptor'] . ', ';
        }

        return rtrim( $srcset, ', ' );
    }
}
