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
 * Renderer for tool_newyeardates.
 *
 * @package    tool_newyeardates
 * @copyright  2026 Brian Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_newyeardates\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;

/**
 * Renderer class for tool_newyeardates.
 *
 * @package    tool_newyeardates
 * @copyright  2026 Brian Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Render the date selection form.
     *
     * @param date_selection_form $form
     * @return string HTML
     */
    protected function render_date_selection_form(date_selection_form $form) {
        $data = $form->export_for_template($this);
        return $this->render_from_template('tool_newyeardates/date_selection_form', $data);
    }

    /**
     * Render the changes preview.
     *
     * @param changes_preview $preview
     * @return string HTML
     */
    protected function render_changes_preview(changes_preview $preview) {
        $data = $preview->export_for_template($this);
        return $this->render_from_template('tool_newyeardates/changes_preview', $data);
    }
}
