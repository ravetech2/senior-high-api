<?php
// Send a JSON response and stop execution.
function json(int $status, array $body): void
{
    http_response_code($status);
    echo json_encode($body, JSON_PRETTY_PRINT);
    exit;
}
