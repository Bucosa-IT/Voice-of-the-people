<?php
/**
 * Candidates Management System
 * Handles adding, retrieving, and managing candidates for student voting
 */

class CandidateManager {
    private $candidatesFile;
    
    public function __construct() {
        $this->candidatesFile = 'data/candidates.json';
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
     * Add a new candidate
     */
    public function addCandidate($name, $position, $batch, $imageUrl = '') {
        $candidates = $this->getAllCandidates();
        
        $candidate = [
            'id' => uniqid('cand_'),
            'name' => htmlspecialchars($name),
            'position' => htmlspecialchars($position),
            'batch' => htmlspecialchars($batch),
            'imageUrl' => htmlspecialchars($imageUrl),
            'votes' => 0,
            'dateAdded' => date('Y-m-d H:i:s')
        ];
        
        $candidates[] = $candidate;
        $this->saveCandidates($candidates);
        
        return $candidate;
    }
    
    /**
     * Get all candidates
     */
    public function getAllCandidates() {
        if (!file_exists($this->candidatesFile)) {
            return [];
        }
        
        $data = file_get_contents($this->candidatesFile);
        return json_decode($data, true) ?: [];
    }
    
    /**
     * Get candidate by ID
     */
    public function getCandidateById($candidateId) {
        $candidates = $this->getAllCandidates();
        
        foreach ($candidates as $candidate) {
            if ($candidate['id'] === $candidateId) {
                return $candidate;
            }
        }
        
        return null;
    }
    
    /**
     * Get candidates by position
     */
    public function getCandidatesByPosition($position) {
        $candidates = $this->getAllCandidates();
        $filtered = [];
        
        foreach ($candidates as $candidate) {
            if ($candidate['position'] === $position) {
                $filtered[] = $candidate;
            }
        }
        
        return $filtered;
    }
    
    /**
     * Get all unique positions
     */
    public function getPositions() {
        $candidates = $this->getAllCandidates();
        $positions = [];
        
        foreach ($candidates as $candidate) {
            if (!in_array($candidate['position'], $positions)) {
                $positions[] = $candidate['position'];
            }
        }
        
        return $positions;
    }
    
    /**
     * Update candidate vote count
     */
    public function incrementVoteCount($candidateId) {
        $candidates = $this->getAllCandidates();
        
        foreach ($candidates as &$candidate) {
            if ($candidate['id'] === $candidateId) {
                $candidate['votes']++;
                break;
            }
        }
        
        $this->saveCandidates($candidates);
    }
    
    /**
     * Delete candidate
     */
    public function deleteCandidate($candidateId) {
        $candidates = $this->getAllCandidates();
        $candidates = array_filter($candidates, function($c) use ($candidateId) {
            return $c['id'] !== $candidateId;
        });
        
        $this->saveCandidates(array_values($candidates));
    }
    
    /**
     * Save candidates to file
     */
    private function saveCandidates($candidates) {
        file_put_contents($this->candidatesFile, json_encode($candidates, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
?>
