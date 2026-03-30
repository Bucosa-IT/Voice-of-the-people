<?php
/**
 * Results & Tallying System
 * Calculates and displays voting results
 */

require_once 'candidates.php';
require_once 'voting.php';
require_once 'voter_session.php';

class ResultsEngine {
    private $candidateManager;
    private $votingEngine;
    private $voterSession;
    
    public function __construct() {
        $this->candidateManager = new CandidateManager();
        $this->votingEngine = new VotingEngine();
        $this->voterSession = new VoterSessionManager();
    }
    
    /**
     * Get results for all positions
     */
    public function getAllResults() {
        $positions = $this->candidateManager->getPositions();
        $results = [];
        
        foreach ($positions as $position) {
            $results[$position] = $this->getResultsByPosition($position);
        }
        
        return $results;
    }
    
    /**
     * Get results for a specific position
     */
    public function getResultsByPosition($position) {
        $candidates = $this->candidateManager->getCandidatesByPosition($position);
        
        // Sort by votes in descending order
        usort($candidates, function($a, $b) {
            return $b['votes'] - $a['votes'];
        });
        
        $totalVotes = array_sum(array_map(function($c) { return $c['votes']; }, $candidates));
        
        $result = [
            'position' => $position,
            'totalVotes' => $totalVotes,
            'candidates' => []
        ];
        
        foreach ($candidates as $candidate) {
            $percentage = $totalVotes > 0 ? round(($candidate['votes'] / $totalVotes) * 100, 2) : 0;
            
            $result['candidates'][] = [
                'id' => $candidate['id'],
                'name' => $candidate['name'],
                'batch' => $candidate['batch'],
                'votes' => $candidate['votes'],
                'percentage' => $percentage,
                'rank' => 0
            ];
        }
        
        // Add ranking
        $rank = 1;
        foreach ($result['candidates'] as &$candidate) {
            $candidate['rank'] = $rank++;
        }
        
        return $result;
    }
    
    /**
     * Get winner for a position
     */
    public function getWinner($position) {
        $results = $this->getResultsByPosition($position);
        
        if (empty($results['candidates'])) {
            return null;
        }
        
        return $results['candidates'][0];
    }
    
    /**
     * Get all winners
     */
    public function getAllWinners() {
        $positions = $this->candidateManager->getPositions();
        $winners = [];
        
        foreach ($positions as $position) {
            $winner = $this->getWinner($position);
            if ($winner) {
                $winners[$position] = $winner;
            }
        }
        
        return $winners;
    }
    
    /**
     * Get voting statistics
     */
    public function getVotingStatistics() {
        $stats = $this->voterSession->getVotingStats();
        $positions = $this->candidateManager->getPositions();
        
        $totalCandidates = count($this->candidateManager->getAllCandidates());
        
        return [
            'voting_stats' => $stats,
            'total_positions' => count($positions),
            'total_candidates' => $totalCandidates,
            'positions' => $positions
        ];
    }
    
    /**
     * Get detailed report
     */
    public function getDetailedReport() {
        return [
            'generated_at' => date('Y-m-d H:i:s'),
            'results' => $this->getAllResults(),
            'winners' => $this->getAllWinners(),
            'statistics' => $this->getVotingStatistics(),
            'batch_breakdown' => $this->votingEngine->getVoteCountByBatch()
        ];
    }
    
    /**
     * Export results as JSON
     */
    public function exportAsJSON() {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="voting_results_' . date('Y-m-d_H-i-s') . '.json"');
        
        return json_encode($this->getDetailedReport(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    
    /**
     * Get results as HTML table
     */
    public function getResultsAsHTML($position = null) {
        if ($position) {
            $results = [$this->getResultsByPosition($position)];
        } else {
            $allResults = $this->getAllResults();
            $results = array_values($allResults);
        }
        
        $html = '<div class="results-container">';
        
        foreach ($results as $posResult) {
            $html .= '<div class="position-results">';
            $html .= '<h3>' . htmlspecialchars($posResult['position']) . '</h3>';
            $html .= '<table class="results-table">';
            $html .= '<tr><th>Rank</th><th>Candidate Name</th><th>Batch</th><th>Votes</th><th>Percentage</th></tr>';
            
            foreach ($posResult['candidates'] as $candidate) {
                $html .= '<tr>';
                $html .= '<td>' . $candidate['rank'] . '</td>';
                $html .= '<td>' . htmlspecialchars($candidate['name']) . '</td>';
                $html .= '<td>' . htmlspecialchars($candidate['batch']) . '</td>';
                $html .= '<td>' . $candidate['votes'] . '</td>';
                $html .= '<td>' . $candidate['percentage'] . '%</td>';
                $html .= '</tr>';
            }
            
            $html .= '</table>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
?>
