<?php
/**
 * @package meet-team
 */
/*
 Plugin Name: Meet the Community Team
Description: Creates a widget for viewing communities experts
Version: 1.0
Author: Harry Oh, Eddie Moya, Dan Crimmins
*/

/**
 * 
 *
 * @author Harry Oh, Eddie Moya, Dan Crimmins
 * @version 1.0
 */
class Meet_Team_Widget extends WP_Widget {

	/**
	 * The name of this widget - shown in WP Admin widget section.
	 */
	var $widget_name = 'Meet the Community Team Widget';
	 
	/**
	 * This widget's ID
	 */
	var $id_base = 'meet_team_widget';

	/**
	 * Description of this widget
	 */
	public $description = 'A widget for interfacing with communities experts';

	/**
	 * Widget's CSS class name.
	 */
	public $classname = 'meet-team-widget';

	/**
	 *
	 *
	 * @author Eddie Moya
	 * @return void
	 */
	public function Meet_Team_Widget() {
		$widget_ops = array(
				'description' => $this->description,
				'classname' => $this->classname
		);
		
		add_action('admin_print_scripts-widgets.php', array($this, 'enqueue'));

		parent::WP_Widget($this->id_base, $this->widget_name, $widget_ops);
	}

	public function enqueue() {
		wp_register_script('meet-team', plugins_url('meet-team/meet-team.js'), array('jquery'));
		wp_enqueue_script('meet-team');
	}
	
	/**
	 * Self-registering widget method.
	 *
	 * This can be called statically.
	 *
	 * @author Eddie Moya
	 * @return void
	 */
	public function register_widget() {
		add_action('widgets_init', create_function( '', 'register_widget("' . __CLASS__ . '");' ));
	}

	/**
	 * Prints out experts in accordance to the conditions specified in this widget's admin. There are only 3 
	 * possible conditions in which experts are displayed: 
	 * 
	 * (1) When a page has a category, and display_method is 'automatic'
	 * (2) When a page has no category, but display_method is 'automatic'
	 * (3) Regardless of whether a page has a category, display_method is 'manual'       
	 * 
	 * Finally, the number of experts displayed is specified by $instance['number-of-experts']
	 * 
	 * @author Harry Oh, Eddie Moya, Dan Crimmins
	 * 
	 * @param array $instance - [REQUIRED]
	 * @param string $display_method - [OPTIONAL] 'automatic' or 'manual'
	 */
	private function display_experts($instance, $display_method = 'automatic') {

		$current_category = get_query_var('cat');

		// When in a page that has a category
		if ('automatic' === $display_method && '' !== $current_category) {
		
			// Only save the specified number of users
			$users_to_display = array_slice(
					get_users_by_taxonomy('category', $current_category),
					0,
					$instance['number-of-experts']
			);

		// When there is no category
		} else if ('automatic' === $display_method && '' == $current_category) {
			
			$all_categories = get_terms('category');
			$total_categories = count($all_categories);
			$users_to_display = array();
			
			// Randomly select users from ALL categories. Random selection algorithm used: 
			// Step 1: Select a random category from $all_categories
			// Step 2: Add all of the users from that category (that have not already been added) onto $users_to_display
			// Step 3: If number of users added to $users_to_display is still less than the number of experts that was 
			//    	   specified to be shown, then go to Step 1
			// Step 4: Complete
			while (count($users_to_display) < $instance['number-of-experts']) {
				$random_index = rand(0, $total_categories - 1);
				$retrieved_users_from_random_category = get_users_by_taxonomy('category', $all_categories[$random_index]);
				
				// Keep adding users until 
				for ($i = 0; $i < count($retrieved_users_from_random_category) && count($users_to_display) < $instance['number-of-experts']; $i += 1) {
					if (!in_array($retrieved_users_from_random_category[$i], $users_to_display)) {
						$users_to_display[] = $retrieved_users_from_random_category[$i];
					}
				}
			}

		// When internal user has manually selected experts to display
		} else if ('manual' === $display_method) {
		
			// Retrieve users selected in the widget admin section
			for ($i = 0; $i < $instance['number-of-experts']; $i += 1) {
				$selected_user_id = $instance['user-' . ($i + 1)]; // starts from $user-1
				$users_to_display[] = get_userdata($selected_user_id);
			}

		}

		// Print out the users that were retrieved based from one of the three conditions accounted for by the above 
		// if/elseif/elseif statement. 
		foreach ($users_to_display as $user_to_display) {
			$user_meta = get_user_meta($user_to_display->ID);
			$user_city = $user_meta['user_city'][0] ? $user_meta['user_city'][0] : 'Hoffman Estates';
			$user_state = $user_meta['user_state'][0] ? $user_meta['user_state'][0] : 'IL';
			$most_recent_post = new WP_Query(
					array(
							'author' => $user_to_display->ID,
							'showposts' => 1,
							'caller_get_posts' => 1,
							'ignore_sticky_posts' => 1
					)
			);
			$most_recent_post_date = date("M d, Y", strtotime($most_recent_post->post->post_date));

			echo '<p>';
			echo '<a href="#"><img src="wp-content/plugins/meet-team/chew.jpeg" style="border: 1px solid black; width: 60px; height: 68px; float: left;" /></a>';
			echo '<a href="#" style="font-size: 10px">' . $user_to_display->display_name . '</a><span style="font-size: 9px"> ' . $user_city . ', ' . $user_state . '</span><br />';
			echo '<span style="font-size: 9px">Last posted on ' . $most_recent_post_date . '</span>';
			show_user_badges($user_to_display->ID);
			echo '</p>';
		}
	}
	
	/**
	 * The front end of the widget. Invokes display_experts to do the heavy-lifting. 
	 *
	 * Do not call directly, this is called internally to render the widget.
	 *
	 * @author Harry Oh, Eddie Moya, Dan Crimmins
	 *
	 * @param array $args       [Required] Automatically passed by WordPress - Settings defined when registering the sidebar of a theme
	 * @param array $instance   [Required] Automatically passed by WordPress - Current saved data for the widget options.
	 * @return void
	 */
	public function widget( $args, $instance ){
		extract($args);
		extract($instance);

		echo $before_widget;

		echo $before_title . $title . $after_title;
		echo $subtitle;

		// Automatic or manual? 
		if ($instance['auto'] == '') {
			$this->display_experts($instance, 'manual'); 
		} else {
			$this->display_experts($instance);
		}

		echo '<a href="#">See More</a>';
		
		echo $after_widget;
	}

	/**
	 * Input validation. 
	 *
	 * @author Harry Oh, Eddie Moya
	 * @uses esc_attr() http://codex.wordpress.org/Function_Reference/esc_attr
	 *
	 * @param array $new_instance   [Required] Automatically passed by WordPress
	 * @param array $old_instance   [Required] Automatically passed by WordPress
	 * @return array|bool Final result of newly input data. False if update is rejected.
	 */
	public function update($new_instance, $old_instance){

		/* Lets inherit the existing settings */
		$instance = $old_instance;

		/* Sanitize input fields */
		foreach($new_instance as $key => $value){
			$instance[$key] = esc_attr($value);
		}

		foreach($instance as $key => $value){
			if($value == 'on' && !isset($new_instance[$key])){
				unset($instance[$key]);
			}
		}

		return $instance;
	}

	/**
	 * Generates the form for this widget, in the WordPress admin area.
	 * Default fields are 
	 *
	 * The use of the helper functions form_field() and form_fields() is not
	 * neccessary, and may sometimes be inhibitive or restrictive.
	 *
	 * @author Harry Oh, Eddie Moya, Dan Crimmins
	 *
	 * @uses wp_parse_args() http://codex.wordpress.org/Function_Reference/wp_parse_args
	 * @uses self::form_field()
	 * @uses self::form_fields()
	 *
	 * @param array $instance [Required] Automatically passed by WordPress
	 * @return void
	 */
	public function form($instance) {

		/* Setup default values for form fields - associtive array, keys are the field_id's */
		$defaults = array(
				'title' => 'Meet the Community Team',
				'subtitle' => "Whatever your question or issue, we're here to help",
				'number-of-experts' => '3',
				'category-1' => 'all',
				'category-2' => 'all',
				'category-3' => 'all'
		);

		$instance = wp_parse_args((array) $instance, $defaults);

		/* Basic options: title, select number of experts, and automatic selection toggle */
		$fields = array(

				// Title of this widget
				array(
						'field_id' => 'title',
						'type' => 'text',
						'label' => 'Widget Title'
				),

				// Subtitle of this widget
				array(
						'field_id' => 'subtitle',
						'type' => 'text',
						'label' => 'Widget Subtitle'
				),

				// Select number of experts
				array(
						'field_id' => 'number-of-experts',
						'type' => 'select-slim',
						'label' => 'Number of Experts to Display: ',
						'options' => array(
								'1' => 1,
								'2' => 2,
								'3' => 3,
								'4' => 4,
								'5' => 5,
								'6' => 6,
								'7' => 7,
								'8' => 8,
								'9' => 9,
								'10' => 10
						)
				),

				// Choose automatic expert selection
				array(
						'field_id' => 'auto',
						'type' => 'checkbox',
						'label' => 'Automatically select by category'
				)
		);

		$this->form_fields($fields, $instance);

		// 
		if ($instance['auto'] == '') {

			$category_terms = get_terms('category');
			$length = count($category_terms);
			$categories = array();

			for ($i = 0; $i < $length; $i += 1) {
				$categories[$category_terms[$i]->term_id] = ucfirst($category_terms[$i]->slug);
			}

			echo '<hr />';
			echo '<br />';
			for ($i = 1; $i <= $instance['number-of-experts']; $i += 1) {
				if ($instance['category-' . $i] == 'all') {
					$user_list = get_users();
				} else {
					$user_list = get_users_by_taxonomy('category', array($instance['category-' . $i]));
				}

				$categories = array('all' => 'All Categories') + $categories;
				$this->form_field('category-' . $i, 'select', 'Expert #' . $i, $instance, $categories);
				$this->user_list_form_field($user_list, $instance, $i);
			}
		}
	}

	/**
	 * 
	 * 
	 * @author Harry Oh
	 * 
	 * @param array $user_list
	 * @param array $instance
	 * @param int $i
	 * @return void
	 */
	private function user_list_form_field($user_list, $instance, $i) {
		?>
		<p>
			<select id="<?php echo $this->get_field_id('user-' . $i); ?>"
				name="<?php echo $this->get_field_name('user-' . $i); ?>"
				class="widefat">
				<option value="not-selected"
				<?php selected("not-selected", $instance['user-' . $i]); ?>>
					<?php echo 'Select Expert #'. $i; ?>
				</option>
				<?php foreach ($user_list as $user) :  ?>
				<option value="<?php echo $user->ID; ?>"
				<?php selected($user->ID, $instance['user-' . $i]); ?>>
					<?php echo $user->display_name; ?>
				</option>
				<?php endforeach; ?>
			</select>
		</p>
		<br />
		<?php
	}

	/**
	 * Helper function - does not need to be part of widgets, this is custom, but
	 * is helpful in generating multiple input fields for the admin form at once.
	 *
	 * This is a wrapper for the singular form_field() function.
	 *
	 * @author Eddie Moya
	 *
	 * @uses self::form_fields()
	 *
	 * @param array $fields     [Required] Nested array of field settings
	 * @param array $instance   [Required] Current instance of widget option values.
	 * @return void
	 */
	private function form_fields($fields, $instance){
		foreach($fields as &$field){
			extract($field);

			$this->form_field($field_id, $type, $label, $instance, $options);
		}
	}

	/**
	 * Helper function - does not need to be part of widgets, this is custom, but
	 * is helpful in generating single input fields for the admin form at once.
	 *
	 * @author Eddie Moya
	 *
	 * @uses get_field_id() (No Codex Documentation)
	 * @uses get_field_name() http://codex.wordpress.org/Function_Reference/get_field_name
	 *
	 * @param string $field_id  [Required] This will be the CSS id for the input, but also will be used internally by wordpress to identify it. Use these in the form() function to set detaults.
	 * @param string $type      [Required] The type of input to generate (text, textarea, select, checkbox]
	 * @param string $label     [Required] Text to show next to input as its label.
	 * @param array $instance   [Required] Current instance of widget option values.
	 * @param array $options    [Optional] Associative array of values and labels for html Option elements.
	 *
	 * @return void
	 */
	private function form_field($field_id, $type, $label, $instance, $options = array()){

		?>
<p>
	<?php

	switch ($type){

            case 'text': ?>

	<label for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo $label; ?>:
	</label> <input id="<?php echo $this->get_field_id( $field_id ); ?>" style="<?php echo $style; ?>" class="widefat" name="<?php echo $this->get_field_name( $field_id ); ?>" value="<?php echo $instance[$field_id]; ?>" />
	<?php break;

            case 'select': ?>

	<select onchange="bananas(this)" id="<?php echo $this->get_field_id( $field_id ); ?>"
		class="widefat meet-team-instant-change" name="<?php echo $this->get_field_name($field_id); ?>">
		<?php
                            foreach ( $options as $value => $label ) :  ?>

		<option value="<?php echo $value; ?>"
		<?php selected($value, $instance[$field_id]) ?>>
			<?php echo $label ?>
		</option>
		<?php

		endforeach;
		?>
	</select>

	<?php break;

				case 'select-slim': ?>
	<label for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo $label; ?>
	</label> <select onchange="bananas(this)" class="meet-team-instant-change" id="<?php echo $this->get_field_id( $field_id ); ?>"
		name="<?php echo $this->get_field_name($field_id); ?>">
		<?php
                            foreach ( $options as $value => $display ) :  ?>

		<option value="<?php echo $value; ?>"
		<?php selected($value, $instance[$field_id]) ?>>
			<?php echo $display ?>
		</option>
		<?php

		endforeach;
		?>
	</select>

	<?php break;

case 'textarea':

	$rows = (isset($options['rows'])) ? $options['rows'] : '16';
	$cols = (isset($options['cols'])) ? $options['cols'] : '20';

	?>
	<label for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo $label; ?>:
	</label>
	<textarea class="widefat" rows="<?php echo $rows; ?>"
		cols="<?php echo $cols; ?>"
		id="<?php echo $this->get_field_id($field_id); ?>"
		name="<?php echo $this->get_field_name($field_id); ?>">
		<?php echo $instance[$field_id]; ?>
	</textarea>
	<?php break;

case 'radio' :
	/**
	 * Need to figure out how to automatically group radio button settings with this structure.
	 */
	?>

	<?php break;

            case 'checkbox' : ?>
	<input type="checkbox" class="checkbox meet-team-instant-change" onclick="bananas(this)"
		id="<?php echo $this->get_field_id($field_id); ?>"
		name="<?php echo $this->get_field_name($field_id); ?>"
		<?php checked( (!empty($instance[$field_id]))); ?> /> <label
		for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo $label; ?>
	</label>
	<?php
	}

	?></p>
<?php
	}
}

Meet_Team_Widget::register_widget();