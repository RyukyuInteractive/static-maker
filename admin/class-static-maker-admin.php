<?php
namespace Static_Maker;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://developer.wordpress.org/
 * @since      1.0.0
 *
 * @package    Static_Maker
 * @subpackage Static_Maker/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Static_Maker
 * @subpackage Static_Maker/admin
 * @author     ameyamashiro <ameyamashiro@example.com>
 */
class Static_Maker_Admin
{
    private $root;

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $root       Root class
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($root, $plugin_name, $version)
    {

        $this->root = $root;
        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Static_Maker_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Static_Maker_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/static-maker-admin.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts($hook)
    {
        $messages = [
            'process_completed' => __('Process Completed', PLUGIN_NAME),
            'failed_to_register' => __('Failed to register', PLUGIN_NAME),
        ];

        switch ($hook) {
            case 'toplevel_page_static-maker':
                wp_register_script('static-maker_admin-display-page-list', plugins_url('', dirname(__FILE__)) . '/admin/js/sm-admin-display-page-list.js');
                wp_enqueue_script('static-maker_admin-display-page-list');
                wp_localize_script('static-maker_admin-display-page-list', 'smData', [
                    'enqueue_single_by_id' => [
                        'url' => wp_nonce_url(admin_url('admin-ajax.php'), 'enqueue_single_by_id'),
                        'messages' => $messages,
                    ],
                    'remove_page_from_list' => [
                        'url' => wp_nonce_url(admin_url('admin-ajax.php'), 'remove_page_from_list'),
                        'messages' => $messages,
                    ],
                    'change_page_status' => [
                        'url' => wp_nonce_url(admin_url('admin-ajax.php'), 'change_page_status'),
                        'messages' => $messages,
                    ],
                    'enqueue_all_pages' => [
                        'url' => wp_nonce_url(admin_url('admin-ajax.php'), 'enqueue_all_pages'),
                        'messages' => $messages,
                    ],
                ]);
                return;
            case 'static-maker_page_static-maker_queues':
                wp_register_script('static-maker_admin-display-queue-list', plugins_url('', dirname(__FILE__)) . '/admin/js/sm-admin-display-queue-list.js');
                wp_enqueue_script('static-maker_admin-display-queue-list');
                wp_localize_script('static-maker_admin-display-queue-list', 'smData', [
                    'process_queue_all' => [
                        'url' => wp_nonce_url(admin_url('admin-ajax.php'), 'process_queue_all'),
                        'messages' => $messages,
                    ],
                ]);
                return;
            case 'static-maker_page_static-maker_page_add':
                wp_register_script('static-maker_admin-display-add', plugins_url('', dirname(__FILE__)) . '/admin/js/sm-admin-display-add.js');
                wp_enqueue_script('static-maker_admin-display-add');
                wp_localize_script('static-maker_admin-display-add', 'smData', [
                    'add_pages_by_post_type' => [
                        'url' => wp_nonce_url(admin_url('admin-ajax.php'), 'add_pages_by_post_type'),
                        'messages' => [
                            'failed_to_register' => __('Failed to register', PLUGIN_NAME),
                        ],
                    ],
                    'add_page_by_url' => [
                        'url' => wp_nonce_url(admin_url('admin-ajax.php'), 'add_page_by_url'),
                        'messages' => $messages,
                    ],
                ]);
                return;
            case 'static-maker_page_static-maker_settings':
                $options = get_option($this->plugin_name);
                $rsync_initial = array(
                    array(
                        'host' => '',
                        'user' => '',
                        'ssh_key' => '',
                        'dir' => '',
                        'rsync_options' => '',
                        'before_command' => '',
                        'auth_method' => '',
                    ),
                );
                $rsync_options = isset($options['rsync']) ? $options['rsync'] : $rsync_initial;
                $rsync_vars = [];
                foreach ($rsync_options as $i => $rsync) {
                    array_push($rsync_vars, [
                        '{{HOST}}' => $rsync['host'],
                        '{{USER}}' => $rsync['user'],
                        '{{SSH_KEY}}' => \Static_Maker\CryptoUtil::decrypt($rsync['ssh_key'], true),
                        '{{DIR}}' => $rsync['user'],
                        '{{RSYNC_OPTIONS}}' => $rsync['rsync_options'],
                        '{{BEFORE_COMMAND}}' => $rsync['before_command'],
                        '{{SSH_AUTH}}' => $rsync['auth_method'] === 'ssh' ? 'checked' : '',
                        '{{PASS_AUTH}}' => $rsync['auth_method'] === 'pass' ? 'checked' : '',
                    ]);
                }
                wp_register_script('static-maker_admin-display-setup', plugins_url('', dirname(__FILE__)) . '/admin/js/sm-admin-display-setup.js');
                wp_enqueue_script('static-maker_admin-display-setup');
                wp_localize_script('static-maker_admin-display-setup', 'smData', [
                    'rsync' => $rsync_vars,
                ]);
                return;
        }
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */

    public function add_plugin_admin_menu()
    {

        /*
         * Add a settings page for this plugin to the Settings menu.
         *
         * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
         *
         *        Administration Menus: http://codex.wordpress.org/Administration_Menus
         *
         */
        $admin_cap = 'manage_options';
        $editor_cap = 'edit_others_posts';
        $slug = $this->plugin_name;

        add_menu_page('Static Maker', 'Static Maker', $admin_cap, $slug, false, 'dashicons-welcome-widgets-menus', '80.050');

        add_submenu_page($this->plugin_name, __('Managed page list', PLUGIN_NAME), __('Pages', PLUGIN_NAME), $admin_cap, $slug, array($this, 'display_plugin_page_list_page'));
        add_submenu_page($this->plugin_name, __('All Queue List', PLUGIN_NAME), __('Queues', PLUGIN_NAME), $editor_cap, $slug . '_queues', array($this, 'display_plugin_queue_list_page'));
        add_submenu_page($this->plugin_name, __('Add page', PLUGIN_NAME), __('Add', PLUGIN_NAME), $admin_cap, $slug . '_page_add', array($this, 'display_plugin_add_page'));
        add_submenu_page($this->plugin_name, __('Settings', PLUGIN_NAME), __('Settings', PLUGIN_NAME), $admin_cap, $slug . '_settings', array($this, 'display_plugin_setup_page'));

        do_action('static_maker_menu_configure', $slug);
    }

    /**
     * Add settings action link to the plugins page.
     *
     * @since    1.0.0
     */

    public function add_action_links($links)
    {
        /*
         *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
         */
        $settings_link = array(
            '<a href="' . admin_url('admin.php?page=' . $this->plugin_name . '_settings') . '">' . __('Settings', $this->plugin_name) . '</a>',
        );
        return array_merge($settings_link, $links);

    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */

    public function display_plugin_setup_page()
    {
        include_once 'partials/static-maker-admin-display-setup.php';
    }

    public function display_plugin_page_list_page()
    {
        include_once 'partials/static-maker-admin-display-page-list.php';
    }

    public function display_plugin_queue_list_page()
    {
        include_once 'partials/static-maker-admin-display-queue-list.php';
    }

    public function display_plugin_add_page()
    {
        include_once 'partials/static-maker-admin-display-add.php';
    }

    public function options_update()
    {
        register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
    }

    /**
     *
     * admin/class-wp-cbf-admin.php
     *
     **/
    public function validate($input)
    {

        // All checkboxes inputs
        $valid = array();

        $valid['host'] = (isset($input['host']) && !empty($input['host'])) ? $input['host'] : '';
        $valid['basic_enable'] = (isset($input['basic_enable']) && !empty($input['basic_enable'])) ? 1 : 0;
        $valid['basic_auth_user'] = (isset($input['basic_auth_user']) && !empty($input['basic_auth_user'])) ? $input['basic_auth_user'] : '';
        $valid['basic_auth_pass'] = (isset($input['basic_auth_pass']) && !empty($input['basic_auth_pass'])) ? $input['basic_auth_pass'] : '';
        $valid['output_path'] = (isset($input['output_path']) && !empty($input['output_path'])) ? $input['output_path'] : '';
        $valid['queue_limit'] = (isset($input['queue_limit']) && !empty($input['queue_limit'])) ? $input['queue_limit'] : '';
        $valid['accepted_post_types'] = (isset($input['accepted_post_types']) && !empty($input['accepted_post_types'])) ? $input['accepted_post_types'] : '';
        $valid['copy_directories'] = (isset($input['copy_directories']) && !empty($input['copy_directories'])) ? $input['copy_directories'] : '';

        if (isset($input['rsync'])) {
            foreach ($input['rsync'] as $i => $rsync) {
                if (empty($rsync['host']) && empty($rsync['user']) && empty($rsync['ssh_key']) && empty($rsync['dir'])) {
                    continue;
                }

                $d = array();

                // encrypt ssh key
                $key = '';
                if (isset($rsync['ssh_key']) && !empty($rsync['ssh_key'])) {
                    $key = CryptoUtil::encrypt($rsync['ssh_key'], true);
                }

                $d['host'] = $rsync['host'];
                $d['user'] = $rsync['user'];
                $d['auth_method'] = isset($rsync['auth_method']) ? $rsync['auth_method'] : 'ssh';
                $d['ssh_key'] = $key;
                $d['dir'] = $rsync['dir'];
                $d['rsync_options'] = $rsync['rsync_options'];
                $d['before_command'] = $rsync['before_command'];

                $valid['rsync'][$i] = $d;
            }
        }

        $valid['replaces'] = isset($input['replaces']) ? $input['replaces'] : null;

        return $valid;
    }

}
