<?php
if (is_single() && get_post_type() == 'course') {
    echo view('sensei.course')->render();
} elseif (is_single() && get_post_type() == 'lesson') {  // check
    echo view('sensei.lesson')->render();
} elseif (is_single() && get_post_type() == 'quiz') {  // check
    echo view('sensei.quiz')->render();
} elseif (is_single() && get_post_type() == 'sensei_message') { // check
    echo view('sensei.single-message')->render();
} elseif (is_post_type_archive('course')
                    || is_page(Sensei()->get_page_id('courses'))
                    || is_tax('course-category')) {
    echo view('sensei.archive')->render();
} elseif (is_post_type_archive('sensei_message')) {
    echo view('sensei.messages')->render();
} elseif (is_tax('lesson-tag')) {
    echo view('sensei.archive-lesson')->render();
} elseif (isset($wp_query->query_vars['learner_profile'])) {
    echo view('sensei.learner-profile')->render();
} elseif (isset($wp_query->query_vars['course_results'])) {
    echo view('sensei.course-results')->render();
} elseif (is_author()
                 && Sensei_Teacher::is_a_teacher(get_query_var('author'))
                 && ! user_can(get_query_var('author'), 'manage_options')) {
    echo view('sensei.teacher-archive')->render();
}
