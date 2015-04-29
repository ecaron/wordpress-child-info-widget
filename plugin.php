<?php
/*
Plugin Name: Child Age & Info Widget
Plugin URI: 
Description: Show your kid's age & birth info in a widget
Version: 1.0
Author: ecaron
Author URI: http://ericcaron.com
License: GPL2
*/

/**
 * Register the widget
 */
add_action('widgets_init', create_function('', 'return register_widget("Child_Age_Widget");'));

/**
 * Class Child_Age_Widget
 */
class Child_Age_Widget extends WP_Widget
{
  /** Basic Widget Settings */
  const WIDGET_NAME = "Child Age & Info Widget";
  const WIDGET_DESCRIPTION = "Show your kid's age & birth info in a widget";

  var $textdomain;
  var $fields;

  /**
   * Construct the widget
   */
  function __construct()
  {
    //We're going to use $this->textdomain as both the translation domain and the widget class name and ID
    $this->textdomain = strtolower(get_class($this));

    //Figure out your textdomain for translations via this handy debug print
    //var_dump($this->textdomain);

    //Add fields
    $this->add_field('title', 'Enter title (name)', '', 'text');
    $this->add_field('child_birthdate', 'Birthdate', 'Birthdate and time', 'text');
    $this->add_field('child_birth_height', 'Birth height', 'Birth height', 'text');
    $this->add_field('child_birth_weight', 'Birth weight', 'Birth weight', 'text');

    //Translations
    load_plugin_textdomain($this->textdomain, false, basename(dirname(__FILE__)) . '/languages' );

    //Init the widget
    parent::__construct($this->textdomain, __(self::WIDGET_NAME, $this->textdomain), array( 'description' => __(self::WIDGET_DESCRIPTION, $this->textdomain), 'classname' => $this->textdomain));
  }

  /**
   * Widget frontend
   *
   * @param array $args
   * @param array $instance
   */
  public function widget($args, $instance)
  {
    $title = apply_filters('widget_title', $instance['title']);

    /* Before and after widget arguments are usually modified by themes */
    echo $args['before_widget'];

    if (!empty($title))
      echo $args['before_title'] . $title . $args['after_title'];

    /* Widget output here */
    $this->widget_output($args, $instance);

    /* After widget */
    echo $args['after_widget'];
  }
  
  /**
   * This function will execute the widget frontend logic.
   * Everything you want in the widget should be output here.
   */
  private function widget_output($args, $instance)
  {
    extract($instance);

    $birthDate = new DateTime($child_birthdate);
    $currentDate = new DateTime(null);
    $interval = date_diff($birthDate, $currentDate);
    $format = [];
    $doPlural = function($nb,$str){return $nb>1?$str.'s':$str;}; // adds plurals
    if($interval->y !== 0) {
        $format[] = "%y ".$doPlural($interval->y, "year");
    }
    if($interval->m !== 0) {
        $format[] = "%m ".$doPlural($interval->m, "month");
    }
    if($interval->d !== 0) {
        $format[] = "%d ".$doPlural($interval->d, "day");
    }
    if($interval->y < 1 && $interval->h !== 0) {
        $format[] = "%h ".$doPlural($interval->h, "hour");
    }
    $actualFormat = array_pop($format);
    if (count($format)) {
      $actualFormat = implode(', ', $format)." and ".$actualFormat;
    }
    $dateDiff = $interval->format($actualFormat);

    /**
     * This is where you write your custom code.
     */
    ?>
      <blockquote>
        Born on <?php echo $birthDate->format('l F jS Y \a\t g:ia'); ?>. <span class="time-ago" style="font-weight:bold">That's <?php echo $dateDiff; ?> ago!</span><br>
        Height at birth: <?php echo $child_birth_height; ?><br>
        Weight at birth: <?php echo $child_birth_weight; ?>
      </blockquote>
    <?php
  }

  /**
   * Widget backend
   *
   * @param array $instance
   * @return string|void
   */
  public function form( $instance )
  {
    /* Generate admin for fields */
    foreach($this->fields as $field_name => $field_data)
    {
      if($field_data['type'] === 'text'):
        ?>
        <p>
          <label for="<?php echo $this->get_field_id($field_name); ?>"><?php _e($field_data['description'], $this->textdomain ); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id($field_name); ?>" name="<?php echo $this->get_field_name($field_name); ?>" type="text" value="<?php echo esc_attr(isset($instance[$field_name]) ? $instance[$field_name] : $field_data['default_value']); ?>" />
        </p>
      <?php
      //elseif($field_data['type'] == 'textarea'):
      //You can implement more field types like this.
      else:
        echo __('Error - Field type not supported', $this->textdomain) . ': ' . $field_data['type'];
      endif;
    }
  }

  /**
   * Adds a text field to the widget
   *
   * @param $field_name
   * @param string $field_description
   * @param string $field_default_value
   * @param string $field_type
   */
  private function add_field($field_name, $field_description = '', $field_default_value = '', $field_type = 'text')
  {
    if(!is_array($this->fields))
      $this->fields = array();

    $this->fields[$field_name] = array('name' => $field_name, 'description' => $field_description, 'default_value' => $field_default_value, 'type' => $field_type);
  }

  /**
   * Updating widget by replacing the old instance with new
   *
   * @param array $new_instance
   * @param array $old_instance
   * @return array
   */
  public function update($new_instance, $old_instance)
  {
    return $new_instance;
  }
}

