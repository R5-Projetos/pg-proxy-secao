<?php
namespace local_topico_ai_proxy\external;

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use context_course;
use core_user;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

class generate_program extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'ID of the course')
        ]);
    }

    public static function execute($courseid) {
        global $CFG, $USER;

        // 1. Validation
        $params = self::validate_parameters(self::execute_parameters(), ['courseid' => $courseid]);
        $context = context_course::instance($params['courseid']);
        
        self::validate_context($context);
        require_capability('moodle/course:update', $context); // Security Check

        // 2. Token Retrieval for Microservice
        // We need a valid WP Token for "keduka_api" service for THIS user.
        $token = self::get_user_token($USER->id);

        if (!$token) {
            throw new \moodle_exception('error_no_token', 'local_topico_ai_proxy', '', null, 'Could not generate token for user.');
        }

        // 3. Call External Microservice
        $url = 'http://md-api-secao:8000/api/course/programa'; // Internal Service Name in Docker/ECS
        // Or usage of public URL if configured
        
        // TODO: Move URL to config
        // $url = get_config('local_topico_ai_proxy', 'api_url');
        
        return self::call_ai_service($url, $params['courseid'], $token);
    }

    private static function get_user_token($userid) {
        global $DB;
        
        // Check for existing token
        $service = $DB->get_record('external_services', ['shortname' => 'keduka_api']);
        if (!$service) {
             // Fallback or Error
             return null; 
        }

        $tokenRecord = $DB->get_record('external_tokens', [
            'userid' => $userid, 
            'externalserviceid' => $service->id,
            'tokentype' => EXTERNAL_TOKEN_PERMANENT
        ]);

        if ($tokenRecord) {
            return $tokenRecord->token;
        }

        // Generate new token via internal API
        // This simulates login/token.php but internally
        $token = \external_generate_token(EXTERNAL_TOKEN_PERMANENT, $service->id, $userid, context_system::instance(), 0, 0);
        return $token;
    }

    private static function call_ai_service($url, $courseid, $token) {
        $curl = new \curl();
        
        $headers = [
            'Content-Type: application/json',
            'X-Moodle-Token: ' . $token
        ];

        $data = json_encode(['course_id' => $courseid]);
        
        $options = [
            'CURLOPT_HTTPHEADER' => $headers,
            'CURLOPT_TIMEOUT' => 300 // AI can be slow
        ];

        // Moodle curl wrapper usually takes options differently or use post()
        // Let's use simpler post with header injection
        $curl->setHeader('X-Moodle-Token: ' . $token);
        $curl->setHeader('Content-Type: application/json');
        
        $response = $curl->post($url, $data);
        $info = $curl->get_info();

        if ($info['http_code'] != 200) {
            throw new \moodle_exception('error_api_call', 'local_topico_ai_proxy', '', null, "API Error: " . $response);
        }

        return json_decode($response, true);
    }

    public static function execute_returns() {
        return new external_single_structure([
             'status' => new external_value(PARAM_TEXT, 'Status of operation'),
             'message' => new external_value(PARAM_TEXT, 'Message')
        ]);
        // To prevent strict return structure issues with dynamic AI response, 
        // usually we return a JSON string or simplified structure.
        // For now, let's just return a success message. API response handling is mostly client side updates.
    }
}
