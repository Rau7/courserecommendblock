<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'block_courserecommend';
$plugin->version = 2024121999;
$plugin->requires = 2022112800; // Moodle 4.1
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.0.1';
$plugin->dependencies = array(
    'local_courserecommend' => 2024121999
);
