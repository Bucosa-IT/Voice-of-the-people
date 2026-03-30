<?php
/**
 * Voter Session Management
 * Manages voter authentication and prevents duplicate votes
 */

session_start();

class VoterSessionManager {
    private $registeredVotersFile;
    private $votedVotersFile;
    
    public function __construct() {
        $this->registeredVotersFile = 'data/registered_voters.json';
        $this->votedVotersFile = 'data/voted_voters.json';
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
     * Authenticate voter with Voter ID and Voter Key (Student ID)
     */
    public function authenticateVoter($voterId, $voterKey) {
        $voters = $this->getRegisteredVoters();
        
        foreach ($voters as $voter) {
            if ($voter['voterId'] === $voterId && $voter['voterKey'] === $voterKey) {
                if ($this->hasVoted($voterId)) {
                    return ['success' => false, 'message' => 'You have already voted!'];
                }
                
                $voter['loginTime'] = date('Y-m-d H:i:s');
                $_SESSION['voter_id'] = $voterId;
                $_SESSION['voter_name'] = $voter['name'];
                $_SESSION['voter_batch'] = $voter['batch'];
                $_SESSION['authenticated'] = true;
                
                return ['success' => true, 'message' => 'Authentication successful', 'voter' => $voter];
            }
        }
        
        return ['success' => false, 'message' => 'Invalid Voter ID or Key'];
    }
    
    /**
     * Check if voter has already voted
     */
    public function hasVoted($voterId) {
        $votedVoters = $this->getVotedVoters();
        
        foreach ($votedVoters as $voter) {
            if ($voter['voterId'] === $voterId) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Mark voter as having voted
     */
    public function markAsVoted($voterId, $voterName = '', $batch = '') {
        $votedVoters = $this->getVotedVoters();
        
        $votedVoters[] = [
            'voterId' => $voterId,
            'voterName' => $voterName,
            'batch' => $batch,
            'voteTime' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents($this->votedVotersFile, json_encode($votedVoters, JSON_PRETTY_PRINT));
    }
    
    /**
     * Check if user is authenticated
     */
    public function isAuthenticated() {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }
    
    /**
     * Get current session voter info
     */
    public function getCurrentVoter() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return [
            'voterId' => $_SESSION['voter_id'] ?? '',
            'name' => $_SESSION['voter_name'] ?? '',
            'batch' => $_SESSION['voter_batch'] ?? ''
        ];
    }
    
    /**
     * Logout voter
     */
    public function logout() {
        session_destroy();
        return true;
    }
    
    /**
     * Register a new voter (for admin)
     */
    public function registerVoter($voterId, $voterKey, $name, $batch) {
        $voters = $this->getRegisteredVoters();
        
        // Check if voter already exists
        foreach ($voters as $voter) {
            if ($voter['voterId'] === $voterId) {
                return ['success' => false, 'message' => 'Voter ID already exists'];
            }
        }
        
        $newVoter = [
            'voterId' => $voterId,
            'voterKey' => $voterKey,
            'name' => htmlspecialchars($name),
            'batch' => htmlspecialchars($batch),
            'registeredDate' => date('Y-m-d H:i:s')
        ];
        
        $voters[] = $newVoter;
        file_put_contents($this->registeredVotersFile, json_encode($voters, JSON_PRETTY_PRINT));
        
        return ['success' => true, 'message' => 'Voter registered successfully', 'voter' => $newVoter];
    }
    
    /**
     * Get all registered voters
     */
    public function getRegisteredVoters() {
        if (!file_exists($this->registeredVotersFile)) {
            return [];
        }
        
        $data = file_get_contents($this->registeredVotersFile);
        return json_decode($data, true) ?: [];
    }
    
    /**
     * Get all voted voters
     */
    public function getVotedVoters() {
        if (!file_exists($this->votedVotersFile)) {
            return [];
        }
        
        $data = file_get_contents($this->votedVotersFile);
        return json_decode($data, true) ?: [];
    }
    
    /**
     * Get voting statistics
     */
    public function getVotingStats() {
        $totalVoters = count($this->getRegisteredVoters());
        $votedVoters = count($this->getVotedVoters());
        
        return [
            'total_registered' => $totalVoters,
            'total_voted' => $votedVoters,
            'pending' => $totalVoters - $votedVoters,
            'turnout_percentage' => $totalVoters > 0 ? round(($votedVoters / $totalVoters) * 100, 2) : 0
        ];
    }
}
?>
