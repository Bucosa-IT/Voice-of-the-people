<?php
/**
 * REST API Endpoints
 * Provides API endpoints for voting system functionality
 */

header('Content-Type: application/json');
require_once 'config.php';
require_once 'voting.php';
require_once 'candidates.php';
require_once 'voter_session.php';
require_once 'results.php';
require_once 'admin.php';

session_start();

$request = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$query = $_GET;

// Initialize classes
$votingEngine = new VotingEngine();
$candidateManager = new CandidateManager();
$voterSession = new VoterSessionManager();
$resultsEngine = new ResultsEngine();
$adminPanel = new AdminPanel();

/**
 * Authenticate voter login
 */
if ($path === '/api/login' && $request === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $voterId = $input['voterId'] ?? null;
    $voterKey = $input['voterKey'] ?? null;
    
    if (!$voterId || !$voterKey) {
        echo ResponseFormatter::json(ResponseFormatter::error('Missing credentials', null, 400));
        exit;
    }
    
    $result = $voterSession->authenticateVoter($voterId, $voterKey);
    
    if ($result['success']) {
        echo ResponseFormatter::json(ResponseFormatter::success('Login successful', $result['voter']));
    } else {
        echo ResponseFormatter::json(ResponseFormatter::error($result['message'], null, 401));
    }
    exit;
}

/**
 * Cast a vote
 */
if ($path === '/api/cast-vote' && $request === 'POST') {
    if (!$voterSession->isAuthenticated()) {
        echo ResponseFormatter::json(ResponseFormatter::error('Not authenticated', null, 401));
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $voterId = $_SESSION['voter_id'] ?? null;
    $candidateId = $input['candidateId'] ?? null;
    $position = $input['position'] ?? null;
    
    if (!$candidateId || !$position) {
        echo ResponseFormatter::json(ResponseFormatter::error('Missing vote data', null, 400));
        exit;
    }
    
    $result = $votingEngine->castVote($voterId, $candidateId, $position);
    
    if ($result['success']) {
        Logger::log('INFO', 'Vote cast successfully', ['voterId' => $voterId, 'candidateId' => $candidateId]);
        echo ResponseFormatter::json(ResponseFormatter::success($result['message'], $result['vote']));
    } else {
        Logger::log('WARNING', 'Vote casting failed', ['voterId' => $voterId, 'error' => $result['error']]);
        echo ResponseFormatter::json(ResponseFormatter::error($result['error'], null, 400));
    }
    exit;
}

/**
 * Get candidates by position
 */
if ($path === '/api/candidates' && $request === 'GET') {
    $position = $query['position'] ?? null;
    
    if ($position) {
        $candidates = $candidateManager->getCandidatesByPosition($position);
    } else {
        $candidates = $candidateManager->getAllCandidates();
    }
    
    echo ResponseFormatter::json(ResponseFormatter::success('Candidates retrieved', ['candidates' => $candidates]));
    exit;
}

/**
 * Get all positions
 */
if ($path === '/api/positions' && $request === 'GET') {
    $positions = $candidateManager->getPositions();
    echo ResponseFormatter::json(ResponseFormatter::success('Positions retrieved', ['positions' => $positions]));
    exit;
}

/**
 * Get voting results for position
 */
if ($path === '/api/results' && $request === 'GET') {
    $position = $query['position'] ?? null;
    
    if ($position) {
        $results = $resultsEngine->getResultsByPosition($position);
    } else {
        $results = $resultsEngine->getAllResults();
    }
    
    echo ResponseFormatter::json(ResponseFormatter::success('Results retrieved', ['results' => $results]));
    exit;
}

/**
 * Get voting statistics
 */
if ($path === '/api/stats' && $request === 'GET') {
    $stats = $voterSession->getVotingStats();
    echo ResponseFormatter::json(ResponseFormatter::success('Statistics retrieved', $stats));
    exit;
}

/**
 * Logout
 */
if ($path === '/api/logout' && $request === 'POST') {
    $voterSession->logout();
    echo ResponseFormatter::json(ResponseFormatter::success('Logged out successfully'));
    exit;
}

/**
 * Admin authentication
 */
if ($path === '/api/admin/login' && $request === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $username = $input['username'] ?? null;
    $password = $input['password'] ?? null;
    
    if (!$username || !$password) {
        echo ResponseFormatter::json(ResponseFormatter::error('Missing credentials', null, 400));
        exit;
    }
    
    $result = $adminPanel->authenticateAdmin($username, $password);
    
    if ($result['success']) {
        echo ResponseFormatter::json(ResponseFormatter::success('Admin login successful'));
    } else {
        echo ResponseFormatter::json(ResponseFormatter::error($result['message'], null, 401));
    }
    exit;
}

/**
 * Get admin dashboard
 */
if ($path === '/api/admin/dashboard' && $request === 'GET') {
    if (!$adminPanel->isAdminAuthenticated()) {
        echo ResponseFormatter::json(ResponseFormatter::error('Not authenticated', null, 401));
        exit;
    }
    
    $stats = $adminPanel->getDashboardStats();
    echo ResponseFormatter::json(ResponseFormatter::success('Dashboard data retrieved', $stats));
    exit;
}

// 404 Not Found
http_response_code(404);
echo ResponseFormatter::json(ResponseFormatter::error('Endpoint not found', null, 404));
?>
