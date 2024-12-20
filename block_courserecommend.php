<?php
class block_courserecommend extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'local_courserecommend');
    }

    public function get_content() {
        global $COURSE, $DB, $OUTPUT, $USER, $CFG;
        require_once($CFG->dirroot . '/course/lib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        // Get recommended courses for current user with recommender info
        $sql = "SELECT c.*, cc.name as categoryname, u.firstname, u.lastname, cr.timemodified, u.id as recommenderid
                FROM {course} c
                JOIN {local_courserecommend} cr ON c.id = cr.courseid
                JOIN {user} u ON u.id = cr.recommendedby
                JOIN {course_categories} cc ON c.category = cc.id
                WHERE cr.userid = :userid
                ORDER BY cr.timemodified DESC";
        $params = array('userid' => $USER->id);
        $recommendedcourses = $DB->get_records_sql($sql, $params);

        if (!empty($recommendedcourses)) {
            $textX = '';
            
            foreach ($recommendedcourses as $course) {
                // Get course image URL
                $courseobj = get_course($course->id);
                $context = context_course::instance($courseobj->id);
                $courseimage = '';
                
                $fs = get_file_storage();
                $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', 0);
                foreach ($files as $file) {
                    if ($file->is_valid_image()) {
                        $courseimage = moodle_url::make_pluginfile_url(
                            $file->get_contextid(),
                            $file->get_component(),
                            $file->get_filearea(),
                            null,
                            $file->get_filepath(),
                            $file->get_filename()
                        );
                        break;
                    }
                }
                
                // If no image found, use default
                if (empty($courseimage)) {
                    $courseimage = $OUTPUT->image_url('course-default', 'block_courserecommend');
                }

                // Create course URL
                $courselink = new moodle_url('/course/view.php', array('id' => $course->id));
                $coursename = format_string($course->fullname);
                $coursecat = $course->categoryname;
                $courseimg = $courseimage;

                $textX .= <<<EOD
                      <a class="card dashboard-card" href="$courselink">
                        <div class="card-img dashboard-card-img" style='background-image: url("$courseimg");'></div>
                        <div class="card-body pr-1 course-info-container c-card-cont">
                            <p class="c-name">$coursename</p>
                            <p class="c-cat-name">$coursecat</p>
                        </div>
                      </a>
                      EOD;
            }
            
            $this->content->text = <<<EOD
                                <div class="card-deck dashboard-card-deck">
                                    $textX
                                </div>
                                EOD;
            
        } else {
            $this->content->text = html_writer::tag('div', 
                get_string('no_recommendations', 'local_courserecommend'),
                array('class' => 'alert alert-info')
            );
        }

        return $this->content;
    }

    public function applicable_formats() {
        return array('my' => true);
    }

    public function has_config() {
        return false;
    }
}
