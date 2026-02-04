<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Settings for tool_newyeardates
 *
 * @package    tool_newyeardates
 * @copyright  2026 Brian Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('tool_newyeardates', get_string('pluginname', 'tool_newyeardates'));
    
    // Month options
    $months = array(
        1 => get_string('january', 'tool_newyeardates'),
        2 => get_string('february', 'tool_newyeardates'),
        3 => get_string('march', 'tool_newyeardates'),
        4 => get_string('april', 'tool_newyeardates'),
        5 => get_string('may', 'tool_newyeardates'),
        6 => get_string('june', 'tool_newyeardates'),
        7 => get_string('july', 'tool_newyeardates'),
        8 => get_string('august', 'tool_newyeardates'),
        9 => get_string('september', 'tool_newyeardates'),
        10 => get_string('october', 'tool_newyeardates'),
        11 => get_string('november', 'tool_newyeardates'),
        12 => get_string('december', 'tool_newyeardates')
    );
    
    $settings->add(new admin_setting_configselect(
        'tool_newyeardates/term1month',
        get_string('term1month', 'tool_newyeardates'),
        get_string('term1month_desc', 'tool_newyeardates'),
        8, // Default: August
        $months
    ));
    
    $settings->add(new admin_setting_configselect(
        'tool_newyeardates/term2month',
        get_string('term2month', 'tool_newyeardates'),
        get_string('term2month_desc', 'tool_newyeardates'),
        1, // Default: January
        $months
    ));
    
    $ADMIN->add('tools', $settings);
}
