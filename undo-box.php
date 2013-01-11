<?php
/*
  Plugin Name: Undo Box
  Plugin URI: http://trenvo.com
  Description: Simple one-click restore post when writing
  Version: 1.0
  Author: Mike Martel
  Author URI: http://trenvo.com
 */

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Version number
 *
 * @since 0.1
 */
define('UNDOBOX_VERSION', '1.0');

/**
 * PATHs and URLs
 *
 * @since 0.1
 */
define ( 'UNDOBOX_DIR', plugin_dir_path ( __FILE__ ) );
define ( 'UNDOBOX_URL', plugin_dir_url ( __FILE__ ) );
define ( 'UNDOBOX_INC_URL', UNDOBOX_URL . '_inc/' );

if (!class_exists('WP_UndoBox')) :

    class WP_UndoBox    {

        /**
         * Creates an instance of the WP_UndoBox class
         *
         * @return WP_UndoBox object
         * @since 0.1
         * @static
        */
        public static function &init() {
            static $instance = false;

            if (!$instance) {
                $instance = new WP_UndoBox;
            }

            return $instance;
        }

        /**
         * Constructor
         *
         * @since 0.1
         */
        public function __construct() {
            if ( ! WP_POST_REVISIONS )
                return;

            global $wp_meta_boxes;

            $page = get_current_screen()->id;
            add_meta_box('undo-box', __('Undo'), array ( &$this, 'do_metabox' ), $page, 'side', 'core' );

            $undo_box = $wp_meta_boxes[$page]['side']['core']['undo-box'];
            unset ( $wp_meta_boxes[$page]['side']['core']['undo-box'] );

            // With array_splice we lose undo_box key in the metabox array. Is this a problem?
            array_splice($wp_meta_boxes[$page]['side']['core'], 1, 0, array ( $undo_box ) );

            add_action('admin_enqueue_scripts', array ( &$this, 'enqueue_script' ) );
        }

            /**
             * PHP4
             *
             * @since 0.1
             */
            public function WP_UndoBox() {
                $this->__construct();
            }

        public function do_metabox() {
            global $post;
            if ( !$revisions = wp_get_post_revisions( $post->ID, array ( 'numberposts' => 1 ) ) ) :
                // @TODO Is there a better string for this?
                _e ("No items found.");
                return;
            endif;
            $revision = current ( $revisions );
            if ( !current_user_can( 'read_post', $revision->ID ) ) :
                // @TODO Is there a better string for this?
                _e ("Sorry, you are not allowed to edit this post.");
                return;
            endif;
            ?>

            <p><?php echo wp_post_revision_title( $revision ). ' ' . __('by') . ' ' . get_the_author_meta( 'display_name', $revision->post_author ); ?></p>

            <p style="float:left">
                <a href="javascript:void(0)" class="show-all"><?php _e("Show All"); ?></a>
                <a href="<?php echo add_query_arg ( array ( "action" => "diff", "right" => $post->ID, "left" => $revision->ID ), admin_url('revision.php') ); ?>" class="button-secondary">Compare</a>
            </p>
            <p style="float:right">
                <?php echo '<a href="' . wp_nonce_url( add_query_arg( array( 'revision' => $revision->ID, 'action' => 'restore' ), admin_url('revision.php') ), "restore-post_$post->ID|$revision->ID" ) . '#undo-box" class="button-primary">' . __( 'Restore' ) . '</a>'; ?>
            </p>

            <div class="clear"></div>

            <?php
        }

        public function enqueue_script() {
            wp_enqueue_script ( 'undo-box', UNDOBOX_INC_URL . 'undo-box.js', array ( 'jquery' ), UNDOBOX_VERSION, true );
        }
    }

    add_action('add_meta_boxes', array('WP_UndoBox', 'init') );
endif;