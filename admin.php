<?php
/**
 * Admin Dashboard & Management
 * Admin functions for managing voting system
 */

session_start();

require_once 'candidates.php';
require_once 'voter_session.php';
require_once 'voting.php';
require_once 'results.php';

class AdminPanel {
    private $candidateManager;
    private $voterSession;
    private $votingEngine;
    private $resultsEngine;
    private $adminFile = 'data/admin_users.json';
    
    public function __construct() {
        $this->candidateManager = new CandidateManager();
        $this->voterSession = new VoterSessionManager();
        $this->votingEngine = new VotingEngine();
        $this->resultsEngine = new ResultsEngine();
        $this->ensureDataDirectory();
    }
    
    /**
     * Ensure data directory exists
     */
    private function ensureDataDirectory() {
        if (!is_dir('data')) {
            mkdir('data', 0755, true);
        }
    }
    
    /**
     * Authenticate admin user
     */
    public function authenticateAdmin($username, $password) {
        $adminUsers = $this->getAdminUsers();
        
        foreach ($adminUsers as $admin) {
            if ($admin['username'] === $username && password_verify($password, $admin['password'])) {
                $_SESSION['admin_authenticated'] = true;
                $_SESSION['admin_username'] = $username;
                return ['success' => true, 'message' => 'Admin authenticated'];
            }
        }
        
        return ['success' => false, 'message' => 'Invalid credentials'];
    }
    
    /**
     * Check if admin is authenticated
     */
    public function isAdminAuthenticated() {
        return isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true;
    }
    
    /**
     * Add admin user
     */
    public function addAdminUser($username, $password) {
        $admins = $this->getAdminUsers();
        
        foreach ($admins as $admin) {
            if ($admin['username'] === $username) {
                return ['success' => false, 'message' => 'Admin username already exists'];
            }
        }
        
        $admins[] = [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'createdDate' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents($this->adminFile, json_encode($admins, JSON_PRETTY_PRINT));
        
        return ['success' => true, 'message' => 'Admin user created'];
    }
    
    /**
     * Get all admin users
     */
    public function getAdminUsers() {
        if (!file_exists($this->adminFile)) {
            return [];
        }
        
        $data = file_get_contents($this->adminFile);
        return json_decode($data, true) ?: [];
    }
    
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $positions = $this->candidateManager->getPositions();
        $allCandidates = $this->candidateManager->getAllCandidates();
        $voterStats = $this->voterSession->getVotingStats();
        
        $candidatesByPosition = [];
        foreach ($positions as $pos) {
            $candidatesByPosition[$pos] = count($this->candidateManager->getCandidatesByPosition($pos));
        }
        
        return [
            'total_positions' => count($positions),
            'total_candidates' => count($allCandidates),
            'candidates_by_position' => $candidatesByPosition,
            'voter_turnout' => $voterStats,
            'positions_list' => $positions
        ];
    }
    
    /**
     * Get voter management data
     */
    public function getVoterManagement() {
        $registered = $this->voterSession->getRegisteredVoters();
        $voted = $this->voterSession->getVotedVoters();
        
        return [
            'total_registered' => count($registered),
            'total_voted' => count($voted),
            'registered_voters' => $registered,
            'voted_voters' => $voted
        ];
    }
    
    /**
     * Reset voting system
     */
    public function resetVotingSystem() {
        $this->deleteFile('data/votes.json');
        $this->deleteFile('data/voted_voters.json');
        
        // Reset candidate vote counts
        $candidates = $this->candidateManager->getAllCandidates();
        foreach ($candidates as &$candidate) {
            $candidate['votes'] = 0;
        }
        
        file_put_contents('data/candidates.json', json_encode($candidates, JSON_PRETTY_PRINT));
        
        return ['success' => true, 'message' => 'Voting system reset successfully'];
    }
    
    /**
     * Delete file
     */
    private function deleteFile($path) {
        if (file_exists($path)) {
            unlink($path);
            return true;
        }
        return false;
    }
    
    /**
     * Export voter list
     */
    public function exportVoterList() {
        $voters = $this->voterSession->getRegisteredVoters();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="voters_' . date('Y-m-d_H-i-s') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Voter ID', 'Name', 'Batch', 'Registered Date']);
        
        foreach ($voters as $voter) {
            fputcsv($output, [
                $voter['voterId'],
                $voter['name'],
                $voter['batch'],
                $voter['registeredDate']
            ]);
        }
        
        fclose($output);
    }
    
    /**
     * Export detailed results
     */
    public function exportDetailedResults() {
        return $this->resultsEngine->exportAsJSON();
    }
    
    /**
     * Get system audit log
     */
    public function generateAuditReport() {
        $report = [
            'generated_at' => date('Y-m-d H:i:s'),
            'dashboard' => $this->getDashboardStats(),
            'voters' => $this->getVoterManagement(),
            'results' => $this->resultsEngine->getDetailedReport()
        ];
        
        return $report;
    }
}
?>
