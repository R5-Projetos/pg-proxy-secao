<?php
defined('MOODLE_INTERNAL') || die();

function local_topico_ai_proxy_extend_navigation(global_navigation $nav) {
    global $PAGE, $COURSE, $USER;

    // Only inject on course view page
    if ($PAGE->pagetype === 'course-view-remui' || $PAGE->pagetype === 'course-view-topics') {
        
        // Check permissions
        $context = context_course::instance($COURSE->id);
        if (has_capability('moodle/course:update', $context)) {
            
            // Inject JS Strings
            $PAGE->requires->strings_for_js(['generate_with_ai', 'generating', 'success_msg', 'error_msg'], 'local_topico_ai_proxy');
            
            // Inject AMD Module
            $PAGE->requires->js_call_amd('local_topico_ai_proxy/ui', 'init', [$COURSE->id]);
        }
    }
}

/**
 * Register external services/functions
 */
function local_topico_ai_proxy_external_services() {
    return [
        'local_topico_ai_proxy_service' => [
            'functions' => ['local_topico_ai_proxy_generate_program'],
            'restrictedusers' => 0,
            'enabled' => 1,
            'shortname' => 'topico_ai_proxy'
        ]
    ];
}
