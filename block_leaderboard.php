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
 * Base file for leaderboard block.
 *
 * @package    blocks_leaderboard
 * @copyright  2019 Erik Fox
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

class block_leaderboard extends block_base {

    /**
     * Init.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('leaderboard', 'block_leaderboard');
    }
    // The PHP tag and the curly bracket for the class definition
    // will only be closed after there is another function added in the next section.

    /**
     * The plugin has a settings.php file.
     *
     * @return boolean True.
     */
    public function has_config() {
        return true;
    }

    /**
     * Get content.
     *
     * @return stdClass
     */
    public function get_content() {

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text   = '';
        $this->content->footer = '';

        $renderer = $this->page->get_renderer('block_leaderboard');
        $this->content->text = $renderer->leaderboard_block($this->page->course);

        /*
        // If the point values for assignments or quizzes get lost somehow, uncomment this code.
        $this->fix_assignments();
        $this->fix_quizzes();
        */

        return $this->content;
    }

    /**
     * Hide the header.
     *
     * @return boolean True
     */
    public function hide_header() {
        return true;
    }

    /**
     * Add special html attributes.
     *
     * @return stdClass
     */
    public function html_attributes() {
        $attributes = parent::html_attributes(); // Get default values.
        $attributes['class'] .= ' block_leaderboard'; // Append our class to class attribute.
        return $attributes;
    }
}
