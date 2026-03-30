<?php
/**
 * Core Voting Engine
 * Handles vote recording, validation, and storage
 */

require_once 'voter_session.php';
require_once 'candidates.php';

class VotingEngine {
    private $votesFile;
    private $voterSession;
    private $candidateManager;
    
    public function __construct() {
        $this->votesFile = 'data/votes.json';
        $this->voterSession = new VoterSessionManager();
        $this->candidateManager = new CandidateManager();
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
     * Validate and cast a vote
     */
    public function castVote($voterId, $candidateId, $position) {
        // Verify voter authentication
        if (!$this->voterSession->isAuthenticated()) {
            return ['success' => false, 'error' => 'Voter not authenticated'];
        }
        
        // Check if voter has already voted
        if ($this->voterSession->hasVoted($voterId)) {
            return ['success' => false, 'error' => 'This voter has already voted'];
        }
        
        // Verify candidate exists
        $candidate = $this->candidateManager->getCandidateById($candidateId);
        if (!$candidate) {
            return ['success' => false, 'error' => 'Invalid candidate'];
        }
        
        // Verify candidate matches position
        if ($candidate['position'] !== $position) {
            return ['success' => false, 'error' => 'Candidate does not match position'];
        }
        
        // Record the vote
        $vote = [
            'id' => uniqid('vote_'),
            'voterId' => $voterId,
            'voterName' => $_SESSION['voter_name'] ?? 'Unknown',
            'voterBatch' => $_SESSION['voter_batch'] ?? 'Unknown',
            'candidateId' => $candidateId,
            'candidateName' => $candidate['name'],
            'position' => $position,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Save vote
        $votes = $this->getAllVotes();
        $votes[] = $vote;
        file_put_contents($this->votesFile, json_encode($votes, JSON_PRETTY_PRINT));
        
        // Increment candidate vote count
        $this->candidateManager->incrementVoteCount($candidateId);
        
        // Mark voter as voted
        $this->voterSession->markAsVoted($voterId, $_SESSION['voter_name'] ?? '', $_SESSION['voter_batch'] ?? '');
        
        return ['success' => true, 'message' => 'Vote cast successfully', 'vote' => $vote];
    }
    
    /**
     * Get all votes
     */
    public function getAllVotes() {
        if (!file_exists($this->votesFile)) {
            return [];
        }
        
        $data = file_get_contents($this->votesFile);
        return json_decode($data, true) ?: [];
    }
    
    /**
     * Get votes by position
     */
    public function getVotesByPosition($position) {
        $votes = $this->getAllVotes();
        $filtered = [];
        
        foreach ($votes as $vote) {
            if ($vote['position'] === $position) {
                $filtered[] = $vote;
            }
        }
        
        return $filtered;
    }
    
    /**
     * Verify vote authenticity
     */
    public function verifyVote($voteId) {
        $votes = $this->getAllVotes();
        
        foreach ($votes as $vote) {
            if ($vote['id'] === $voteId) {
                return $vote;
            }
        }
        
        return null;
    }
    
    /**
     * Get vote count by batch
     */
    public function getVoteCountByBatch() {
        $votes = $this->getAllVotes();
        $batchCounts = [];
        
        foreach ($votes as $vote) {
            $batch = $vote['voterBatch'];
            if (!isset($batchCounts[$batch])) {
                $batchCounts[$batch] = 0;
            }
            $batchCounts[$batch]++;
        }
        
        return $batchCounts;
    }
}
?>
