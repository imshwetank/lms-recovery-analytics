<?php

namespace Core;

abstract class ApiController extends Controller {
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        return json_encode([
            'status' => $status >= 200 && $status < 300 ? 'success' : 'error',
            'data' => $data
        ]);
    }

    protected function success($data = null, $message = '', $status = 200) {
        return $this->json([
            'message' => $message,
            'data' => $data
        ], $status);
    }

    protected function error($message, $status = 400, $errors = []) {
        return $this->json([
            'message' => $message,
            'errors' => $errors
        ], $status);
    }

    protected function validateJson($rules) {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->error('Invalid JSON payload', 400);
        }
        
        if (!$this->validate($data, $rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }
        
        return $data;
    }

    protected function requireAuth() {
        if (!$this->session->get('user')) {
            return $this->error('Unauthorized', 401);
        }
    }

    protected function getAuthUser() {
        return $this->session->get('user');
    }
}
