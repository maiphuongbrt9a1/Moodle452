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
 * Plugin capabilities for the block_children_information plugin.
 *
 * @package   block_children_information
 * @copyright 2025, Võ Mai Phương <vomaiphuonghhvt@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_children_information extends block_base
{

    /**
     * Initialises the block.
     *
     * @return void
     */
    public function init()
    {
        $this->title = get_string('children_information', 'block_children_information');
    }

    /**
     * Gets the block contents.
     *
     * @return string The block HTML.
     */
    public function get_content()
    {
        global $OUTPUT, $DB, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        // Add logic here to define your template data or any other content.
        $sql = "SELECT children.childrenid,
                        children.parentid,
                        user.firstname,
                        user.lastname,
                        user.email,
                        user.phone1,
                        user.firstnamephonetic, 
                        user.lastnamephonetic, 
                        user.middlename, 
                        user.alternatename
                FROM {children_and_parent_information} children
                JOIN {user} user on user.id = children.childrenid
                WHERE children.parentid = :parentid";
        $params = ['parentid' => $USER->id];
        $children_from_db = $DB->get_records_sql($sql, $params);

        $data = new stdClass();
        if (empty($children_from_db)) {
            $data->haschildren = false;
            $data->nochildrenmessage = get_string('no_children', 'block_children_information');
            $data->linkformurl = new moodle_url('/local/children_management/index.php', []);
        } else {
            $data->haschildren = true;
            $children_for_templates = [];

            foreach ($children_from_db as $child) {
                $children_for_templates[] = [
                    'name' => fullname($child),
                    'profileurl' => new moodle_url('/user/profile.php', ['id' => $child->childrenid])
                ];
            }
            $data->children = $children_for_templates;
            $data->children_management_page_link = new moodle_url('/local/children_management/index.php', []);

        }

        $this->content->text = $OUTPUT->render_from_template('block_children_information/content', $data);
        return $this->content;
    }

    /**
     * Defines in which pages this block can be added.
     *
     * @return array of the pages where the block can be added.
     */
    public function applicable_formats()
    {
        return [
            'admin' => false,
            'site-index' => false,
            'course-view' => false,
            'mod' => false,
            'my' => true,
        ];
    }
}