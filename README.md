WordPress Zero
==============

Classes to abstract out some common functionality for WordPress and generally make developing custom themes and CMS-style sites easier.

Requirements
------------

This has only been tested with PHP 5.2+ and will probably break with anything older.

Usage
-----

### WidgetZero ###

Class to abstract out some common functionality for WordPress widgets.

Example:

	class MyWidget extends WidgetZero {
		function MyWidget() {
			$widget_ops = array('classname' => 'mywidget_class', 'description' => __( "My Widget does something") );
			$this->WP_Widget('mywidget_id', __('My Widget'), $widget_ops);
		
			// set up fields
			$this->set_fields(array(
				array(
					'name'=>'title',
					'default'=>'Butterfly Giraffes Widget'
				),
				array(
					'name'=>'number',
					'label'=>'Number of giraffes',
					'size'=>3,
					'default'=>'5'
				),
				array(
					'name'=>'chrysalis_length', // label will automatically be rendered as "Chrysalis Length:"
					'size' => '3',
					'default' => '0',
					'note' => 'Leave blank for no chrysalis'
				),
				array(
					'name'=>'awesome',
					'label'=>'Make it awesome?',
					'type'=>'toggle'
				),
				array(
					'name'=>'giraffe_type',
					'type'=>'select',
					'optionlist'=>array(
						'tall' => 'Tall giraffes',
						'short' => 'Short giraffes',
						'mixed' => 'All kinds of giraffes'
					)
				)
			)
			);
		}
		function widget($args, $instance) {
			echo $args['before_widget'];
		
			$title = $this->get_field_value('title', $instance);
			if ( $title ) echo $args['before_title'] . $title . $args['after_title'];
		
			printf("<p>We have %s giraffes!</p>", $this->get_field_value('number'));
			printf("<p>In a chrysalis %s units in length!</p>", $this->get_field_value('chrysalis_length'));
			switch($this->get_field_value('giraffe_type')){
				case 'tall':
					echo "<p>Those giraffes are pretty tall.</p>";
				case 'short':
					echo "<p>Those giraffes are kind of short.</p>";
				case 'mixed':
					echo "<p>Wow, all kinds of giraffes!</p>";
			}
			if ($this->get_field_value('awesome')){
				echo "<p>Awesome!</p>";
			}
			
			echo $args['after_widget'];
		}
	}
	
If you are putting your widget file somewhere other than the plugins directory (for example building a widget in to your theme) you will also need to register your widget somewhere:
	
	add_action('widgets_init', create_function('', "register_widget('MyWidget');"));



### MetaboxZero ###

Make custom meta boxes on the edit page easier.

Example:

	$post_box_fields = array(
		array(
			'name' => '_page_title',
			'label' => 'Browser Window Title',
			'size' => 90
		),
		array(
			'name' => '_meta_description',
			'size' => 90
		),
	);

	$post_box_info = array(
		'id' => 'my_post_meta',
		'title' => 'Post Info',
		'page' => 'post',
		'position' => 'normal',
		'priority' => 'high'
	);

	new MetaboxZero($post_box_info, $post_box_fields);


--------------------------------

Since November 1, 2010
Gabriel Gilder
