<?php
/*
Plugin Name: Category Wise Posts with Thumbnails
Plugin URI: https://github.com/MuhammadUsman0304/Category-Wise-Posts-with-Thumbnails
Description: Small and fast plugin to display posts category wise in the sidebar, a list of linked titles and thumbnails of  posts category wise
Version: 1.0.0
Author: Muhammad Usman
Tags: post widget, posts with thumbnails, category wise posts
Author URI: https://www.linkedin.com/in/muhammad-usman-b3439218b/
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

*/

defined('ABSPATH') || die("hey you can't call me :) ");
class Category_Wise_Posts_with_Thumbnails extends WP_Widget
{

    // Set up the widget name and description
    public function __construct()
    {
        $widget_options = array(
            'classname' => 'Category-Wise-Posts-with-Thumbnails',
            'description' => 'A custom widget to display recent posts category wise in the sidebar'
        );
        parent::__construct('Category_Wise_Posts_with_Thumbnails', 'Category Wise Posts with Thumbnails Widget', $widget_options);
        add_action('wp_enqueue_scripts', array($this, 'my_enqueue_scripts'));
    }
    public function my_enqueue_scripts()
    {
        wp_enqueue_style('my-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');
        wp_enqueue_style('bs-style', plugin_dir_url(__FILE__) . 'assets/css/bootstrap.min.css"', [], false, false);
        wp_enqueue_script('bs-js', 'assets/js/bootstrap.bundle.min.js', array('jquery'), 1.0, true);
    }

    // Front-end display of widget
    public function widget($args, $instance)
    {
        if (isset($instance['title'])) {
            $title = apply_filters('widget_title', $instance['title']);
        } else {
            $title = '';
        }
        $num_posts = isset($instance['num_posts']) ? absint($instance['num_posts']) : 5;
        $show_date = isset($instance['show_date']) ? (bool) $instance['show_date'] : false;
        $show_thumbnail = isset($instance['show_thumbnail']) ? (bool) $instance['show_thumbnail'] : false;
        $thumbnail_size = isset($instance['thumbnail_size']) ? $instance['thumbnail_size'] : 'thumbnail';
        $thumbnail_align = isset($instance['thumbnail_align']) ? $instance['thumbnail_align'] : 'left';
        $category = isset($instance['category']) ? absint($instance['category']) : '';

        // echo $args['before_widget'];

        if ($title) {
            echo $args['before_title'] . $title . $args['after_title'];
        }


        $recent_posts =   array(
            'posts_per_page' => $num_posts,
            'post_status' => 'publish',
            'post_type' => 'post',
            'cat' => $category
        );


        $query = new WP_Query($recent_posts);

        if (isset($recent_posts['before_widget'])) {
            echo $recent_posts['before_widget'];
        }
        if (!empty($title) && isset($recent_posts['before_title'])) {
            echo $recent_posts['before_title'] . $title . $recent_posts['after_title'];
        }

        if ($query->have_posts()) {
            // echo '<ul class="posts-ul">';
            while ($query->have_posts()) {
                $query->the_post();
                echo "<div class='row my-3'>";
                echo '<div class="col-md-4">';
                if (has_post_thumbnail()) {
                    the_post_thumbnail($thumbnail_size);
                }
                echo '</div>
                <div class="col-md-6">
                ';

                echo '<a class="text-decoration-none" href="' . get_permalink() . '"><h2 class="post-content post-title">' . get_the_title() . '</h2></a>';
                echo '<p class="post-content">' . get_the_date() . '</p>';
                echo '<p class="post-content"><a class="text-decoration-none" href="#">' . get_the_author() . '</a> </p>';

                echo '
                </div>
               ';
                echo "</div>";
            }
            echo '</ul>';
        } else {
            echo '<p>No posts found</p>';
        }
        wp_reset_postdata();
        if (isset($recent_posts['after_widget'])) {
            echo esc_html($recent_posts['after_widget']);
        }
    }

    public function form($instance)
    {
        $title = isset($instance['title']) ? $instance['title'] : '';
        $num_posts = isset($instance['num_posts']) ? absint($instance['num_posts']) : 5;
        $thumbnail_size = isset($instance['thumbnail_size']) ? sanitize_text_field($instance['thumbnail_size']) : 'thumbnail';
        $category = isset($instance['category']) ? absint($instance['category']) : 0;
        $categories = get_categories();
        $category_name = '';

        if ($category) {
            $category_obj = get_category($category);
            if ($category_obj) {
                $category_name = $category_obj->name;
            }
        }

?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input type="text" class="widefat" id="my-widget-title <?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($title); ?>">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('num_posts'); ?>"><?php _e('Number of posts to display:'); ?></label>
            <input type="number" class="tiny-text" id="<?php echo $this->get_field_id('num_posts'); ?>" name="<?php echo $this->get_field_name('num_posts'); ?>" value="<?php echo esc_attr($num_posts); ?>" min="1" step="1">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('thumbnail_size'); ?>"><?php _e('Thumbnail size:'); ?></label>
            <select id=" <?php echo $this->get_field_id('thumbnail_size'); ?>" name="<?php echo $this->get_field_name('thumbnail_size'); ?>">
                <?php
                $sizes = get_intermediate_image_sizes();
                foreach ($sizes as $size) {
                    echo '<option value="' . esc_attr($size) . '"' . selected($size, $thumbnail_size, false) . '>' . esc_html($size) . '</option>';
                }
                ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('category') ?>">Category:</label>
            <select class="widefat myuscls" id="myus_category <?php echo esc_html($this->get_field_id('category')) ?>" name="<?php echo  esc_html($this->get_field_name('category')) ?>">
                <option value="0">All categories</option>

                <?php
                foreach ($categories as $cat) {
                    echo '<option value="' . esc_html($cat->cat_ID) . '" ' . selected($category, $cat->cat_ID, false) . '>' . esc_html($cat->cat_name) . '</option>';
                }
                ?>
            </select>
        </p>

<?php

    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['num_posts'] = isset($new_instance['num_posts']) ? absint($new_instance['num_posts']) : 5;
        $instance['thumbnail_size'] = isset($new_instance['thumbnail_size']) ? sanitize_text_field($new_instance['thumbnail_size']) : 'thumbnail';
        $instance['category'] = (int) $new_instance['category'];
        return $instance;
    }
}

function us_register_custom_recent_posts_widget()
{
    register_widget('Category_Wise_Posts_with_Thumbnails');
}
add_action('widgets_init', 'us_register_custom_recent_posts_widget');
