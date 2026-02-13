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
 * Date selection form renderable.
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
 * Date selection form renderable class.
 *
 * @package    tool_newyeardates
 * @copyright  2026 Brian Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class date_selection_form implements renderable, templatable {

    /** @var stdClass The course object */
    protected $course;

    /** @var string Current start date formatted */
    protected $currentstartdate;

    /**
     * Constructor.
     *
     * @param stdClass $course Course object
     */
    public function __construct($course) {
        $this->course = $course;
        $this->currentstartdate = userdate($course->startdate, get_string('strftimedatefullshort', 'langconfig'));
    }

    /**
     * Export data for template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->courseid = $this->course->id;
        $data->currentstartdate = $this->currentstartdate;
        $data->defaultnewdate = date('Y-m-d', $this->course->startdate);
        $data->sesskey = sesskey();
        
        return $data;
    }
}
