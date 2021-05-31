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
 * Manage instances of app types.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_applaunch;

use core\notification;
use mod_applaunch\event\app_type_created;
use mod_applaunch\event\app_type_deleted;
use mod_applaunch\event\app_type_disabled;
use mod_applaunch\event\app_type_enabled;
use mod_applaunch\event\app_type_updated;
use mod_applaunch\local\form\app_type_form;
use mod_applaunch\local\table\app_type_list;

defined('MOODLE_INTERNAL') || die();

class app_type_controller {
    /**
     * View action.
     */
    const ACTION_VIEW = 'view';

    /**
     * Add action.
     */
    const ACTION_ADD = 'add';

    /**
     * Edit action.
     */
    const ACTION_EDIT = 'edit';

    /**
     * Delete action.
     */
    const ACTION_DELETE = 'delete';

    /**
     * Hide action.
     */
    const ACTION_HIDE = 'hide';

    /**
     * Show action.
     */
    const ACTION_SHOW = 'show';


    /**
     * Locally cached $OUTPUT object.
     * @var \bootstrap_renderer
     */
    protected $output;

    /**
     * region_manager constructor.
     */
    public function __construct() {
        global $OUTPUT;

        $this->output = $OUTPUT;
    }

    /**
     * Execute required action.
     *
     * @param string $action Action to execute.
     */
    public function execute($action) {

        $this->set_external_page();

        switch($action) {
            case self::ACTION_ADD:
            case self::ACTION_EDIT:
                $this->edit($action, optional_param('id', null, PARAM_INT));
                break;

            case self::ACTION_DELETE:
                $this->delete(required_param('id', PARAM_INT));
                break;

            case self::ACTION_HIDE:
                $this->hide(required_param('id', PARAM_INT));
                break;

            case self::ACTION_SHOW:
                $this->show(required_param('id', PARAM_INT));
                break;

            case self::ACTION_VIEW:
            default:
                $this->view();
                break;
        }
    }

    /**
     * Set external page for the manager.
     */
    protected function set_external_page() {
        admin_externalpage_setup('mod_applaunch/app_type');
    }

    /**
     * Return record instance.
     *
     * @param int $id
     * @param \stdClass|null $data
     *
     * @return app_type
     */
    protected function get_instance($id = 0, \stdClass $data = null) {
        return new app_type($id, $data);
    }

    /**
     * Print out all records in a table.
     */
    protected function display_all_records() {
        $records = app_type::get_records([], 'id');

        $table = new app_type_list();
        $table->display($records);
    }

    /**
     * Returns a text for create new record button.
     * @return string
     */
    protected function get_create_button_text() : string {
        return get_string('setting:addapptype', 'applaunch');
    }

    /**
     * Returns form for the record.
     *
     * @param app_type|null $instance
     *
     * @return app_type_form
     */
    protected function get_form($instance) : app_type_form {
        global $PAGE;

        return new app_type_form($PAGE->url->out(false), ['persistent' => $instance]);
    }

    /**
     * View page heading string.
     * @return string
     */
    protected function get_view_heading() : string {
        return get_string('setting:manage_app_types', 'applaunch');
    }

    /**
     * New record heading string.
     * @return string
     */
    protected function get_new_heading() : string {
        return get_string('setting:newapptype', 'applaunch');
    }

    /**
     * Edit record heading string.
     * @return string
     */
    protected function get_edit_heading() : string {
        return get_string('setting:editapptype', 'applaunch');
    }

    /**
     * Returns base URL for the manager.
     * @return string
     */
    public static function get_base_url() : string {
        return '/mod/applaunch/app_type.php';
    }

    /**
     * Execute edit action.
     *
     * @param string $action Could be edit or create.
     * @param null|int $id Id of the region or null if creating a new one.
     */
    protected function edit($action, $id = null) {
        global $PAGE;

        $PAGE->set_url(new \moodle_url(static::get_base_url(), ['action' => $action, 'id' => $id]));
        $instance = null;

        if ($id) {
            $instance = $this->get_instance($id);
        }

        $form = $this->get_form($instance);

        if ($form->is_cancelled()) {
            redirect(new \moodle_url(static::get_base_url()));
        } else if ($data = $form->get_data()) {
            unset($data->submitbutton);
            try {
                if (empty($data->id)) {
                    $persistent = $this->get_instance(0, $data);
                    $persistent->create();

                    app_type_created::create_strict(
                        $persistent,
                        \context_system::instance()
                    )->trigger();
                    $this->trigger_enabled_event($persistent);
                } else {
                    $instance->from_record($data);
                    $instance->update();

                    app_type_updated::create_strict(
                        $instance,
                        \context_system::instance()
                    )->trigger();
                    $this->trigger_enabled_event($instance);
                }
                notification::success(get_string('changessaved'));
            } catch (\Exception $e) {
                notification::error($e->getMessage());
            }
            redirect(new \moodle_url(static::get_base_url()));
        } else {
            if (empty($instance)) {
                $this->header($this->get_new_heading());
            } else {
                if (!$instance->can_delete()) {
                    notification::warning(get_string('setting:cantdelete', 'applaunch'));
                }
                $this->header($this->get_edit_heading());
            }
        }

        $form->display();
        $this->footer();
    }

    /**
     * Execute delete action.
     *
     * @param int $id ID of the region.
     */
    protected function delete($id) {
        require_sesskey();
        $instance = $this->get_instance($id);

        if ($instance->can_delete()) {
            $instance->delete();
            notification::success(get_string('deleted'));

            app_type_deleted::create_strict(
                $id,
                \context_system::instance()
            )->trigger();

            redirect(new \moodle_url(static::get_base_url()));
        } else {
            notification::warning(get_string('setting:cantdelete', 'applaunch'));
            redirect(new \moodle_url(static::get_base_url()));
        }
    }

    /**
     * Execute view action.
     */
    protected function view() {
        global $PAGE;

        $this->header($this->get_view_heading());
        $this->print_add_button();
        $this->display_all_records();

        // TODO: JS for app_type management.
        $PAGE->requires->js_call_amd('mod_applaunch/managetypes', 'setup');

        $this->footer();
    }

    /**
     * Show the app_type.
     *
     * @param int $id The ID of the app_type to show.
     */
    protected function show(int $id) {
        $this->show_hide($id, 1);
    }

    /**
     * Hide the app_type.
     *
     * @param int $id The ID of the app_type to hide.
     */
    protected function hide($id) {
        $this->show_hide($id, 0);
    }

    /**
     * Show or Hide the app_type.
     *
     * @param int $id The ID of the app_type to hide.
     * @param int $visibility The intended visibility.
     */
    protected function show_hide(int $id, int $visibility) {
        require_sesskey();
        $type = $this->get_instance($id);
        $type->set('enabled', $visibility);
        $type->save();

        $this->trigger_enabled_event($type);

        redirect(new \moodle_url(self::get_base_url()));
    }

    /**
     * Print out add button.
     */
    protected function print_add_button() {
        echo $this->output->single_button(
            new \moodle_url(static::get_base_url(), ['action' => self::ACTION_ADD]),
            $this->get_create_button_text()
        );
    }

    /**
     * Print out page header.
     * @param string $title Title to display.
     */
    protected function header($title) {
        echo $this->output->header();
        echo $this->output->heading($title);
    }

    /**
     * Print out the page footer.
     *
     * @return void
     */
    protected function footer() {
        echo $this->output->footer();
    }

    /**
     * Helper function to fire off an event that informs of if a app_type is enabled or not.
     *
     * @param app_type $type The app_type persistent object.
     */
    private function trigger_enabled_event(app_type $type) {
        if ($type->get('enabled') == 0) {
            app_type_disabled::create_strict(
                $type,
                \context_system::instance()
            )->trigger();
        } else {
            app_type_enabled::create_strict(
                $type,
                \context_system::instance()
            )->trigger();
        }
    }
}
