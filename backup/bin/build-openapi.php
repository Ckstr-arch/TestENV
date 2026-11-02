<?php
declare(strict_types=1);

/**
 * Generate this standalone application's OpenAPI 3.1 contract using PHP only.
 * Run from backup/: php bin/build-openapi.php
 * This defines schema metadata and route contracts; it does not boot the app,
 * read credentials, access a database, or expose a public setup endpoint.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$schemas = [
    'Club' => ['type' => 'object', 'properties' => ['name' => ['type' => 'string', 'maxLength' => 120], 'sport' => ['type' => 'string', 'maxLength' => 60], 'description' => ['type' => 'string', 'maxLength' => 5000], 'coach' => ['type' => 'string', 'maxLength' => 100], 'location' => ['type' => 'string', 'maxLength' => 100], 'capacity' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 10000]], 'required' => ['name', 'sport', 'description', 'coach', 'location', 'capacity'], 'additionalProperties' => false],
    'Tournament' => ['type' => 'object', 'properties' => ['name' => ['type' => 'string', 'maxLength' => 120], 'sport' => ['type' => 'string', 'maxLength' => 60], 'description' => ['type' => 'string', 'maxLength' => 5000], 'location' => ['type' => 'string', 'maxLength' => 100], 'starts_at' => ['$ref' => '#/components/schemas/LocalDateTime'], 'ends_at' => ['$ref' => '#/components/schemas/LocalDateTime'], 'registration_deadline' => ['$ref' => '#/components/schemas/LocalDateTime'], 'capacity' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 10000], 'status' => ['type' => 'string', 'enum' => ['open', 'ongoing', 'completed']]], 'required' => ['name', 'sport', 'description', 'location', 'starts_at', 'ends_at', 'registration_deadline', 'capacity', 'status'], 'additionalProperties' => false],
    'Event' => ['type' => 'object', 'properties' => ['title' => ['type' => 'string', 'maxLength' => 100], 'club_id' => ['type' => 'integer', 'minimum' => 1], 'tournament_id' => ['type' => ['integer', 'null'], 'minimum' => 1], 'kind' => ['type' => 'string', 'enum' => ['training', 'match']], 'location' => ['type' => 'string', 'maxLength' => 100], 'starts_at' => ['$ref' => '#/components/schemas/LocalDateTime'], 'ends_at' => ['$ref' => '#/components/schemas/LocalDateTime'], 'opponent' => ['type' => 'string', 'maxLength' => 120], 'home_score' => ['type' => ['integer', 'null'], 'minimum' => 0, 'maximum' => 100000], 'away_score' => ['type' => ['integer', 'null'], 'minimum' => 0, 'maximum' => 100000]], 'required' => ['title', 'club_id', 'kind', 'location', 'starts_at', 'ends_at'], 'additionalProperties' => false],
    'Resource' => ['type' => 'object', 'properties' => ['name' => ['type' => 'string', 'maxLength' => 120], 'kind' => ['type' => 'string', 'enum' => ['facility', 'equipment']], 'description' => ['type' => 'string', 'maxLength' => 5000], 'location' => ['type' => 'string', 'maxLength' => 100]], 'required' => ['name', 'kind', 'description', 'location'], 'additionalProperties' => false],
    'Registration' => ['type' => 'object', 'properties' => ['name' => ['type' => 'string', 'maxLength' => 100], 'email' => ['type' => 'string', 'format' => 'email', 'maxLength' => 190], 'student_id' => ['type' => 'string', 'pattern' => '^[A-Za-z0-9-]{3,30}$'], 'faculty' => ['type' => 'string', 'maxLength' => 80], 'password' => ['type' => 'string', 'minLength' => 12, 'maxLength' => 128]], 'required' => ['name', 'email', 'student_id', 'faculty', 'password'], 'additionalProperties' => false],
    'Login' => ['type' => 'object', 'properties' => ['email' => ['type' => 'string', 'format' => 'email'], 'password' => ['type' => 'string']], 'required' => ['email', 'password'], 'additionalProperties' => false],
    'Code' => ['type' => 'object', 'properties' => ['code' => ['type' => 'string', 'description' => 'Six-digit TOTP code or unused 16-character recovery code.']], 'required' => ['code'], 'additionalProperties' => false],
    'SecondFactor' => ['type' => 'object', 'properties' => ['password' => ['type' => 'string'], 'code' => ['type' => 'string']], 'required' => ['password', 'code'], 'additionalProperties' => false],
    'Booking' => ['type' => 'object', 'properties' => ['resource_id' => ['type' => 'integer', 'minimum' => 1], 'starts_at' => ['$ref' => '#/components/schemas/LocalDateTime'], 'ends_at' => ['$ref' => '#/components/schemas/LocalDateTime'], 'purpose' => ['type' => 'string', 'maxLength' => 255]], 'required' => ['resource_id', 'starts_at', 'ends_at', 'purpose'], 'additionalProperties' => false],
    'Review' => ['type' => 'object', 'properties' => ['status' => ['type' => 'string', 'enum' => ['approved', 'rejected']]], 'required' => ['status'], 'additionalProperties' => false],
    'CommunityPost' => ['type' => 'object', 'properties' => ['body' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 5000]], 'required' => ['body'], 'additionalProperties' => false],
    'CommunityComment' => ['type' => 'object', 'properties' => ['body' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 2000]], 'required' => ['body'], 'additionalProperties' => false],
    'CommunityReport' => ['type' => 'object', 'properties' => ['reason' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 500]], 'required' => ['reason'], 'additionalProperties' => false],
    'Moderation' => ['type' => 'object', 'properties' => ['status' => ['type' => 'string', 'enum' => ['visible', 'hidden']], 'reason' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 500]], 'required' => ['status', 'reason'], 'additionalProperties' => false],
    'AttendanceUpdate' => ['type' => 'object', 'properties' => ['status' => ['type' => 'string', 'const' => 'attended']], 'required' => ['status'], 'additionalProperties' => false],
    'Empty' => ['type' => 'object', 'additionalProperties' => false],
    'LocalDateTime' => ['type' => 'string', 'pattern' => '^\\d{4}-\\d{2}-\\d{2}T\\d{2}:\\d{2}$', 'description' => 'Local Asia/Colombo date/time without an offset; for example 2026-12-01T16:00.'],
    'Response' => ['type' => 'object', 'required' => ['data', 'meta'], 'properties' => ['data' => (object) [], 'meta' => ['type' => 'object', 'required' => ['request_id', 'csrf_token'], 'properties' => ['request_id' => ['type' => 'string'], 'csrf_token' => ['type' => 'string'], 'redirect' => ['type' => 'string'], 'pagination' => ['type' => 'object', 'properties' => ['page' => ['type' => 'integer'], 'per_page' => ['type' => 'integer'], 'total' => ['type' => 'integer'], 'pages' => ['type' => 'integer']]]]]]],
    'Error' => ['type' => 'object', 'required' => ['error', 'meta'], 'properties' => ['error' => ['type' => 'object', 'required' => ['status', 'message'], 'properties' => ['status' => ['type' => 'integer'], 'message' => ['type' => 'string']]], 'meta' => ['type' => 'object', 'properties' => ['request_id' => ['type' => 'string'], 'csrf_token' => ['type' => ['string', 'null']]]]]],
];

$spec = [
    'openapi' => '3.1.0',
    'info' => [
        'title' => 'NSBM SportsHub REST API',
        'version' => '1.0.0',
        'description' => 'Same-origin cookie sessions, JSON requests, CSRF protection, student/admin authorization and TOTP. All local timestamps use Asia/Colombo. PUT replaces an administrative record. No bearer tokens or wildcard CORS.',
    ],
    'servers' => [['url' => '/api/v1']],
    'security' => [['sessionCookie' => []]],
    'paths' => [],
    'components' => [
        'securitySchemes' => ['sessionCookie' => [
            'type' => 'apiKey', 'in' => 'cookie', 'name' => 'sportshub_backup_session',
        ]],
        'schemas' => $schemas,
    ],
];

/** Add one operation, with uniform cookie/CSRF/error/pagination contracts. */
function add_operation(
    array &$spec,
    string $path,
    string $method,
    string $summary,
    ?string $body = null,
    bool $public = false,
    bool $paginated = false,
    array $filters = [],
    bool $created = false
): void {
    $operationId = $method . '_' . str_replace(['/', '{', '}', '-'], ['_', '', '', '_'], trim($path, '/'));
    $operation = [
        'summary' => $summary,
        'operationId' => $operationId,
        'responses' => [
            (string) ($created ? 201 : 200) => [
                'description' => 'Success',
                'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Response']]],
            ],
        ],
    ];
    if ($public) $operation['security'] = [];
    $parameters = [];
    preg_match_all('/\{([^}]+)\}/', $path, $matches);
    foreach ($matches[1] as $name) {
        $parameters[] = ['name' => $name, 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer', 'minimum' => 1]];
    }
    if ($method !== 'get') {
        $parameters[] = [
            'name' => 'X-CSRF-Token', 'in' => 'header', 'required' => true,
            'description' => 'Fetch from auth/session; preserve its session cookie. Token rotates after login/logout.',
            'schema' => ['type' => 'string'],
        ];
    }
    if ($paginated) {
        $parameters[] = ['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100000, 'default' => 1]];
        $parameters[] = ['name' => 'per_page', 'in' => 'query', 'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'default' => 20]];
    }
    foreach ($filters as $name) $parameters[] = ['name' => $name, 'in' => 'query', 'schema' => ['type' => 'string']];
    if ($parameters !== []) $operation['parameters'] = $parameters;
    if ($body !== null) {
        $operation['requestBody'] = [
            'required' => true,
            'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/' . $body]]],
        ];
    }
    $errors = [
        400 => 'Malformed request', 401 => 'Authentication required/invalid',
        403 => 'Forbidden or invalid CSRF/origin', 404 => 'Route or record not found',
        405 => 'Method not allowed (Allow header)', 409 => 'State conflict',
        413 => 'Request too large', 415 => 'JSON content type required',
        422 => 'Validation/business-rule failure', 429 => 'Rate limit (Retry-After: 900)',
        500 => 'Unexpected server error', 503 => 'Database unavailable',
    ];
    foreach ($errors as $status => $description) {
        $operation['responses'][(string) $status] = [
            'description' => $description,
            'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Error']]],
        ];
    }
    $spec['paths'][$path][$method] = $operation;
}

add_operation($spec, '/health', 'get', 'Database-backed readiness', public: true);
add_operation($spec, '/auth/session', 'get', 'Session identity and CSRF bootstrap', public: true);
foreach ([
    ['register', 'Registration', 'Create a student account'],
    ['login', 'Login', 'Password login; may return requires_2fa'],
    ['2fa', 'Code', 'Complete pending login with a second factor'],
] as [$path, $body, $summary]) {
    add_operation($spec, '/auth/' . $path, 'post', $summary, $body, public: true, created: $path === 'register');
}
add_operation($spec, '/auth/logout', 'post', 'End the authenticated session', 'Empty');
add_operation($spec, '/me', 'get', 'Current safe user profile');
add_operation($spec, '/me/security', 'get', '2FA status and remaining recovery-code count');
add_operation($spec, '/me/2fa/setup', 'post', 'Generate pending authenticator setup key (10-minute expiry)', 'Empty');
add_operation($spec, '/me/2fa', 'post', 'Enroll authenticator and return recovery codes exactly once', 'SecondFactor');
add_operation($spec, '/me/2fa', 'delete', 'Disable 2FA with password and second factor', 'SecondFactor');
foreach ([
    ['clubs', 'Club', ['q', 'sport']],
    ['tournaments', 'Tournament', ['q', 'sport', 'status']],
    ['events', 'Event', ['q', 'kind', 'club_id', 'tournament_id']],
    ['resources', 'Resource', ['q', 'kind']],
] as [$entity, $body, $filters]) {
    add_operation($spec, '/' . $entity, 'get', 'List ' . $entity, public: true, paginated: true, filters: $filters);
    add_operation($spec, '/' . $entity . '/{id}', 'get', 'Read ' . $entity . ' detail', public: true);
    add_operation($spec, '/admin/' . $entity, 'get', 'Admin list ' . $entity, paginated: true);
    add_operation($spec, '/admin/' . $entity, 'post', 'Create ' . $entity, $body, created: true);
    add_operation($spec, '/admin/' . $entity . '/{id}', 'get', 'Admin read ' . $entity);
    add_operation($spec, '/admin/' . $entity . '/{id}', 'put', 'Replace ' . $entity, $body);
    add_operation($spec, '/admin/' . $entity . '/{id}', 'delete', 'Delete ' . $entity . ' (linked data may cascade)', 'Empty');
}
foreach ([
    ['clubs/{id}/memberships', 'Apply for club membership'],
    ['tournaments/{id}/registrations', 'Register an individual tournament participant'],
    ['events/{id}/attendance', 'Register for an approved club session'],
] as [$path, $summary]) add_operation($spec, '/' . $path, 'post', $summary, 'Empty', created: true);
add_operation($spec, '/bookings', 'post', 'Request a future resource reservation (up to four hours)', 'Booking', created: true);
foreach (['memberships', 'registrations', 'attendance', 'bookings'] as $entity) {
    add_operation($spec, '/me/' . $entity, 'get', 'List own ' . $entity, paginated: true);
    add_operation($spec, '/me/' . $entity . '/{id}', 'delete', 'Cancel own eligible ' . $entity, 'Empty');
}
foreach (['memberships', 'bookings'] as $entity) {
    add_operation($spec, '/admin/' . $entity, 'get', 'List ' . $entity . ' requests', paginated: true, filters: ['status']);
    add_operation($spec, '/admin/' . $entity . '/{id}', 'patch', 'Review pending ' . $entity, 'Review');
}
add_operation($spec, '/admin/attendance', 'get', 'Student session registrations', paginated: true);
add_operation($spec, '/admin/attendance/{id}', 'patch', 'Mark attendance after a session starts', 'AttendanceUpdate');
add_operation($spec, '/admin/reports', 'get', 'Club, tournament and booking aggregates');
add_operation($spec, '/admin/audit-logs', 'get', 'Paginated administration audit history', paginated: true);
add_operation($spec, '/results', 'get', 'Recorded match scores', public: true, paginated: true, filters: ['tournament_id']);
add_operation($spec, '/standings', 'get', 'Club standings with win=3/draw=1/loss=0', public: true, filters: ['tournament_id']);
add_operation($spec, '/clubs/{club_id}/community', 'get', 'Approved-member club summary');
add_operation($spec, '/clubs/{club_id}/members', 'get', 'Approved-member roster without contact details', paginated: true);
add_operation($spec, '/clubs/{club_id}/posts', 'get', 'Visible club feed for approved members', paginated: true);
add_operation($spec, '/clubs/{club_id}/posts', 'post', 'Share a plain-text post with your club', 'CommunityPost', created: true);
add_operation($spec, '/clubs/{club_id}/posts/{post_id}', 'get', 'Read one visible club post');
add_operation($spec, '/clubs/{club_id}/posts/{post_id}', 'patch', 'Edit your own post; hidden posts stay hidden', 'CommunityPost');
add_operation($spec, '/clubs/{club_id}/posts/{post_id}', 'delete', 'Erase your post and its linked comments/reactions/reports', 'Empty');
add_operation($spec, '/clubs/{club_id}/posts/{post_id}/comments', 'get', 'Read visible-post comments', paginated: true);
add_operation($spec, '/clubs/{club_id}/posts/{post_id}/comments', 'post', 'Comment on a club post', 'CommunityComment', created: true);
add_operation($spec, '/clubs/{club_id}/posts/{post_id}/comments/{comment_id}', 'delete', 'Remove own comment; administrators may moderate comments', 'Empty');
add_operation($spec, '/clubs/{club_id}/posts/{post_id}/reaction', 'put', 'Add one reaction idempotently', 'Empty');
add_operation($spec, '/clubs/{club_id}/posts/{post_id}/reaction', 'delete', 'Remove your reaction idempotently', 'Empty');
add_operation($spec, '/clubs/{club_id}/posts/{post_id}/reports', 'post', 'Privately report a post once', 'CommunityReport', created: true);
add_operation($spec, '/admin/community', 'get', 'Admin-only post moderation queue', paginated: true, filters: ['status']);
add_operation($spec, '/admin/community/posts/{post_id}/reports', 'get', 'Admin-only report details', paginated: true);
add_operation($spec, '/admin/community/posts/{post_id}', 'patch', 'Hide or restore a post and resolve reports', 'Moderation');

$target = __DIR__ . '/../public/assets/openapi.json';
$json = json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
if (file_put_contents($target, $json, LOCK_EX) === false) {
    fwrite(STDERR, "Could not write the generated OpenAPI contract.\n");
    exit(1);
}
$count = array_sum(array_map('count', $spec['paths']));
echo 'Generated OpenAPI contract: ', $count, " operations\n";
