<?php

class block_create_course_key extends block_base {

	function init()
	{
		$this->title = get_string('create_course_keys', 'block_create_course_key');
		$this->version = 2012080301;
		$this->cron = 0;
	}

	function cron()
	{
		return true;
	}

	function get_content()
	{
		global $USER, $CFG, $SESSION, $DB, $COURSE;

		if ($COURSE->id == $this->instance->pageid) {
			$course = $COURSE;
		} else {
			$course = $DB->get_record('course', 'id', $this->instance->pageid);
		}
		$courseid = $course->id;
		$has_groups = 0;
		$schoolid = 0;
		if (!empty($USER->schoolid)) {
			$schoolid = $USER->schoolid;
		}

		$school = $DB->get_record_sql('SELECT * FROM ' . $CFG->prefix . "schools where code like '" . $schoolid . "'");
		if (has_capability('block/create_course_key:createcoursekeyblock', get_context_instance(CONTEXT_COURSE, $courseid))) {
			// and ($school->keys_left>0 or is_siteadmin($USER->id))) {

			if ($this->content !== null) {
				return $this->content;
			}
			$this->content = new stdClass;
			$this->content->text = '';
			$this->content->footer = '';

			if (empty($this->instance)) {

			} else {
				$own_groups = array();
				if ($course->groupmode > 0) {
					if (!has_capability('block/create_course_key:viewallgroups', get_context_instance(CONTEXT_COURSE, $courseid)) and has_capability('block/create_course_key:viewowngroups', get_context_instance(CONTEXT_COURSE, $courseid))) {
						$key_sets = $DB->get_records_sql("select * from {$CFG->prefix}course_key_sets where courseid = " . $courseid . " and created_by_id = " . $USER->id);
						foreach ($key_sets as $key_set) {
							$own_groups[$key_set->groupid] = 1;
						}
					}

					$has_groups = 1;
					$groups = groups_get_all_groups($courseid);
					$group_select_box = "<tr><td colspan='2'>" . get_string('group', 'block_use_course_key') . "</td></tr>";
					$group_select_box .= "<tr><td colspan='3'><select id='group' onchange='javascript:updateGroupselectboxStatus();' name='group'>\n";
					$group_select_box .= "<option value='new'>" . get_string('new_group', 'block_use_course_key') . "</option>\n";
					if (!empty($groups)) {
						foreach ($groups as $group) {
							if (has_capability('block/create_course_key:viewallgroups', get_context_instance(CONTEXT_COURSE, $courseid)) or (has_capability('block/create_course_key:viewowngroups', get_context_instance(CONTEXT_COURSE, $courseid)) and $own_groups[$group->id] == 1)) {
								$group_select_box .= "<option value='" . $group->id . "'>" . $group->name . "</option>\n";
							}
						}
					}
					$group_select_box .= "</select></td></tr>\n";

					if (has_capability('block/create_course_key:viewallgroups', get_context_instance(CONTEXT_COURSE, $courseid)) or (has_capability('block/create_course_key:viewowngroups', get_context_instance(CONTEXT_COURSE, $courseid)) and $own_groups[$group->id] == 1)) {
						$group_select_box .= "<tr><td colspan='2'>" . get_string('name_group', 'block_use_course_key') . "</td></tr>";
						$group_select_box .= "<tr><td colspan='2'><input id='groupname' type='text' name='groupname' /></td></tr>";
					}
				}
				$context = get_context_instance(CONTEXT_COURSE, $courseid);

				$this->content->text = '<style type="text/css">@import url( ' . $CFG->wwwroot . '/course_keys/jscalendar/calendar-win2k-1.css);</style>
                    <script type="text/javascript" src="' . $CFG->wwwroot . '/course_keys/jscalendar/calendar.js"></script>
                    <script type="text/javascript" src="' . $CFG->wwwroot . '/course_keys/jscalendar/lang/calendar-en.js"></script>
                    <script type="text/javascript" src="' . $CFG->wwwroot . '/blocks/create_course_key/createcoursekey.js"></script>
                    <script type="text/javascript" src="' . $CFG->wwwroot . '/course_keys/jscalendar/calendar-setup.js"></script>' . '<form id="course_key_generator" method="post" action="' . $CFG->wwwroot . '/course_keys/generate.php"><div>' . '<input type="hidden" id="id" name="id" value="' . $courseid . '" /> <input type="hidden" id="has_groups" name="has_groups" value="' . $has_groups . '" />' . '<table>' . '<tr><td colspan="2"><label for="block_number_of_keys">' . get_string('number_of_keys', 'block_use_course_key') . '</label><br/>' . '<input id="block_number_of_keys" type="text" name="number_of_keys" /></td></tr>' . '<tr><td>' . get_string('expire_date', 'block_use_course_key') . '<br/>
                    <input type="text" id="key_expire_date" name="key_expire_date" disabled style="width:75px;" value="' . (date("m", time())) . date("/d/Y", time()) . '"/>
                    </td>' . '<td>' . get_string('enrollment_days', 'block_use_course_key') . '<br/><input type="text" name="key_enrolment_days" style="width:75px;" /></td></tr>' . ($has_groups == 1 ? $group_select_box : '') . '<tr><td colspan="2">
                    <input type="checkbox" id="no_expire_date" name="no_expire_date" checked value="1" onclick="

                if (document.getElementById(\'no_expire_date\').checked==true) {
                    document.getElementById(\'key_expire_date\').disabled=true;
                } else {
                    document.getElementById(\'key_expire_date\').disabled=false;
                }
                " /> ' . get_string('no_expire_date', 'block_use_course_key') . '<br/>';

				if (has_capability('block/create_course_key:create_stat_key', get_context_instance(CONTEXT_COURSE, $courseid)))
					$this->content->text .= '<input type="checkbox" name="create_stat_key" /> ' . get_string('create_stat_key', 'block_use_course_key') . '<br/>';

				$this->content->text .= '
                </td>' . '</table>' . '<br>' . '<input id="coursekey-create" type="submit" value="' . get_string('create', 'block_use_course_key') . '" />' . '</div></form>' . '<script type="text/javascript">
                Calendar.setup(
                {
                    inputField  : "key_expire_date",         // ID of the input field
                    ifFormat    : "%m/%d/%Y",    // the date format
                    button      : "key_expire_date"       // ID of the button
                }
                );
                </script>';
			}
			return $this->content;
		}
	}

}

?>