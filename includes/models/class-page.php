<?php
namespace Static_Maker;

if (!defined('ABSPATH')) {
    exit;
}

class Page
{

    protected static $table_name = 'staticmaker_pages';

    protected static $columns = array();
    protected $data = array();

    public static function table_name()
    {
        global $wpdb;
        return $wpdb->prefix . self::$table_name;
    }

    public static function create_table()
    {
        global $wpdb;

        $table_name = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
          id int(20) NOT NULL AUTO_INCREMENT,
          post_id int(20) UNIQUE,
          post_type varchar(255) NOT NULL,
          permalink varchar(255) DEFAULT '' NOT NULL,
          active tinyint(1) DEFAULT 1 NOT NULL,
          PRIMARY KEY (id)
        ) $charset_collate";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function get_page_count()
    {
        global $wpdb;
        $table_name = self::table_name();
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }

    public static function get_pages($user_option)
    {
        $defaults = array(
            'paged' => 1,
            'numberposts' => 25,
            'published' => true,
        );
        $option = array_merge($defaults, $user_option);

        global $wpdb;
        $table_name = self::table_name();

        $columns = '*';
        $sql = '';
        $args = array();

        // join
        if ($option['published']) {
            $columns = "*, {$table_name}.post_type as post_type";
            $sql .= " LEFT JOIN {$wpdb->prefix}posts AS posts ON {$table_name}.post_id = posts.id ";
        }

        // where
        if ($option['published']) {
            $columns = "*, {$table_name}.post_type as post_type";
            $sql .= " WHERE {$table_name}.post_id IS NULL OR posts.post_status = 'publish' ";
        }

        // limit, offset
        if ($option['numberposts'] !== -1) {
            $sql .= " LIMIT %d OFFSET %d ";

            $args[] = $option['numberposts'];
            $args[] = ($option['paged'] - 1) * $option['numberposts'];
        }

        $sql_head = "SELECT ${columns} FROM $table_name ";
        $sql = $sql_head . $sql;

        $prepareArgs = array_merge(array($sql), $args);
        if (!isset($prepareArgs[1])) {
            $prepareArgs[1] = $args;
        }

        $query = call_user_func_array(array($wpdb, 'prepare'), $prepareArgs);
        $pages = $wpdb->get_results($query, ARRAY_A);

        $instances = array();

        foreach ($pages as $page) {
            $lang_details = apply_filters('wpml_post_language_details', null, $page['post_id']);
            $lang_code = !is_wp_error($lang_details) ? $lang_details['language_code'] : '';
            $page['permalink'] = apply_filters('wpml_permalink', $page['permalink'], $lang_code);

            $ins = new self($page);
            $instances[] = $ins;
        }

        return $instances;
    }

    public static function get_page($id)
    {
        global $wpdb;
        $table_name = self::table_name();

        $query = "SELECT * FROM $table_name WHERE id = %d LIMIT 1";
        $rows = $wpdb->get_results($wpdb->prepare($query, $id), ARRAY_A);

        if (!isset($rows[0]) || !$rows[0]) {return null;}

        return new self($rows[0]);
    }

    public static function get_page_by_post_id($id)
    {
        global $wpdb;
        $table_name = self::table_name();

        $query = "SELECT * FROM $table_name WHERE post_id = %d LIMIT 1";
        $rows = $wpdb->get_results($wpdb->prepare($query, $id), ARRAY_A);

        if (!isset($rows[0]) || !$rows[0]) {return null;}

        return new self($rows[0]);
    }

    public static function get_page_by_link($link)
    {
        global $wpdb;
        $table_name = self::table_name();

        $query = "SELECT * FROM $table_name WHERE permalink = %s LIMIT 1";
        $rows = $wpdb->get_results($wpdb->prepare($query, $link), ARRAY_A);

        if (!isset($rows[0]) || !$rows[0]) {return null;}

        return new self($rows[0]);
    }

    public static function get_related_pages($post_id)
    {
        $posts_to_process = array();
        // $posts_to_process[] = $post_id;

        $post = Page::get_page_by_post_id($post_id);

        if ($archive = get_post_type_archive_link($post->post_type)) {
            $posts_to_process[] = array(
                'type' => 'url',
                'data' => $archive,
            );
        }

        if ($parents = get_post_ancestors($post->post_id)) {
            foreach ($parents as $parent) {
                $posts_to_process[] = array(
                    'type' => 'post_id',
                    'data' => $parent,
                );
            }
        }

        return $posts_to_process;
    }

    public function __construct($columns)
    {
        foreach ($columns as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __get($field_name)
    {
        if (!array_key_exists($field_name, $this->data)) {
            throw new \Exception('Undefined variable for ' . get_called_class());
        } else {
            return $this->data[$field_name];
        }
    }

    public function __set($field_name, $field_value)
    {
        return $this->data[$field_name] = $field_value;
    }

    public static function create($post_id, $post_type, $permalink, $active = 1)
    {
        $page = new static(array(
            'post_id' => $post_id,
            'post_type' => $post_type,
            'permalink' => $permalink,
            'active' => $active,
        ));
        $page->save();
        return $page;
    }

    public function save()
    {
        global $wpdb;
        $table_name = self::table_name();

        $is_manual_type = $this->data['post_type'] === 'static-maker-manual';
        $permalink = $this->data['permalink'];
        $post_id = $this->data['post_id'];

        if ($is_manual_type) {
            $exists_query = $wpdb->prepare("SELECT EXISTS(SELECT * FROM $table_name WHERE permalink = %s)", $permalink);
        } else {
            $exists_query = $wpdb->prepare("SELECT EXISTS(SELECT * FROM $table_name WHERE post_id = %s)", $post_id);
        }

        if ($wpdb->get_var($exists_query) === '1') { // have

            if ($is_manual_type) {
                return $wpdb->update($table_name, $this->data, array('permalink' => $permalink));
            } else {
                return $wpdb->update($table_name, $this->data, array('post_id' => $post_id));
            }

        } else {
            if ($is_manual_type) {
                return $wpdb->insert($table_name, $this->data);
            } else {
                return $wpdb->insert($table_name, $this->data);
            }
        }
    }

    public function delete()
    {
        global $wpdb;
        $table_name = self::table_name();
        return $wpdb->delete($table_name, array('id' => $this->data['id']));
    }

}
