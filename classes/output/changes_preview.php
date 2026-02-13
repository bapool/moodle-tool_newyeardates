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
 * Changes preview renderable.
 *
 * @package    tool_newyeardates
 * @copyright  2026 Brian Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_newyeardates\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Changes preview renderable class.
 *
 * @package    tool_newyeardates
 * @copyright  2026 Brian Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class changes_preview implements renderable, templatable {

    /** @var array Activities with date changes */
    protected $activities;

    /** @var int Course ID */
    protected $courseid;

    /** @var string New start date string */
    protected $newstartdatestr;

    /**
     * Constructor.
     *
     * @param array $activities Array of activities with changes
     * @param int $courseid Course ID
     * @param string $newstartdatestr New start date string
     */
    public function __construct($activities, $courseid, $newstartdatestr) {
        $this->activities = $activities;
        $this->courseid = $courseid;
        $this->newstartdatestr = $newstartdatestr;
    }

    /**
     * Export data for template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->courseid = $this->courseid;
        $data->newstartdate = $this->newstartdatestr;
        $data->sesskey = sesskey();
        $data->activities = array();

        // Sort activities by old date.
        usort($this->activities, function($a, $b) {
            return $a['olddate'] - $b['olddate'];
        });

        // Format activities for display.
        foreach ($this->activities as $activity) {
            $activitydata = new stdClass();
            $activitydata->name = $activity['name'];
            $activitydata->field = $activity['field'];
            $activitydata->displayname = $activity['name'] . ' (' . $activity['field'] . ')';
            
            // Format dates safely.
            if ($activity['olddate'] > 946684800) {
                $activitydata->olddate = userdate($activity['olddate'], 
                    get_string('strftimedatefullshort', 'langconfig'));
            } else {
                $activitydata->olddate = get_string('notset', 'tool_newyeardates');
            }
            
            if ($activity['newdate'] > 946684800) {
                $activitydata->newdate = userdate($activity['newdate'], 
                    get_string('strftimedatefullshort', 'langconfig'));
            } else {
                $activitydata->newdate = get_string('notset', 'tool_newyeardates');
            }
            
            $data->activities[] = $activitydata;
        }

        $data->hasactivities = !empty($data->activities);
        $data->cancelurl = new \moodle_url('/course/view.php', array('id' => $this->courseid));

        return $data;
    }
}
