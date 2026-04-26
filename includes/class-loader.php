<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Carno_Livechat_Loader {

    protected $actions = [];
    protected $filters = [];

    public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->actions[] = compact( 'hook', 'component', 'callback', 'priority', 'accepted_args' );
    }

    public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->filters[] = compact( 'hook', 'component', 'callback', 'priority', 'accepted_args' );
    }

    public function run() {
        foreach ( $this->filters as $filter ) {
            add_filter(
                $filter['hook'],
                [ $filter['component'], $filter['callback'] ],
                $filter['priority'],
                $filter['accepted_args']
            );
        }

        foreach ( $this->actions as $action ) {
            add_action(
                $action['hook'],
                [ $action['component'], $action['callback'] ],
                $action['priority'],
                $action['accepted_args']
            );
        }
    }
}
