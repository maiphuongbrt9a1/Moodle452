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
 * Callback implementations for local_children_course_list_management
 *
 * @package    local_children_course_list_management
 * @copyright  2025 Võ Mai Phương <vomaiphuonghhvt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_children_course_list_management;

use moodle_url;
use core\output\pix_icon;
class helper
{
    /**
     * Generates a sortable table header link with an arrow icon indicating the current sort direction.
     * @param object $current_page The current page object containing the URL.
     * @param string $column_name The name of the column to sort.
     * @param string $display_column_name The display name of the column.
     * @param string $current_sort_column The currently sorted column.
     * @param string $current_direction The current sort direction ('asc' or 'desc').
     * @return string HTML output for the sortable header link with an arrow icon. 
     */
    public static function make_sort_table_header_helper(
        $current_page,
        $column_name,
        $display_column_name,
        $current_sort_column,
        $current_direction
    ) {
        global $OUTPUT;
        $new_direction = '';
        if ($current_sort_column === $column_name and $current_direction === 'asc') {
            $new_direction = 'desc';
        } else {
            $new_direction = 'asc';
        }

        $new_url = new moodle_url($current_page->url, ['sort' => $column_name, 'direction' => $new_direction]);

        $arrow_up = new pix_icon('t/sort_asc', $display_column_name, 'core', ['class' => 'icon-inline']);
        $arrow_down = new pix_icon('t/sort_desc', $display_column_name, 'core', ['class' => 'icon-inline']);
        $arrow = $arrow_up;

        if ($current_sort_column === $column_name) {
            // Mũi tên lên/xuống
            $arrow = ($current_direction === 'asc') ? $arrow_up : $arrow_down;
        }

        return $display_column_name . ' ' . $OUTPUT->action_icon(
            $new_url,
            $arrow
        );
    }

}
