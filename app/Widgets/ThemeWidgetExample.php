<?php

namespace App\Widgets;

use Carbon_Fields\Field;
use Carbon_Fields\Widget;

class ThemeWidgetExample extends Widget {
    // Register widget function. Must have the same name as the class
    function __construct() {
        $this->setup( 'theme_widget_example', 'Theme Widget - Example', 'Displays a block with title/text', array(
            Field::make( 'text', 'title', 'Title' )->set_default_value( 'Hello World!') ,
            Field::make( 'textarea', 'content', 'Content' )->set_default_value( 'Lorem Ipsum dolor sit amet' )
        ) );
        $this->print_wrappers = false;
    }

    // Called when rendering the widget in the front-end
    function front_end( $args, $instance ) {
        echo $args['before_title'] . $instance['title'] . $args['after_title'];
        echo '<p>' . $instance['content'] . '</p>';
    }
}
