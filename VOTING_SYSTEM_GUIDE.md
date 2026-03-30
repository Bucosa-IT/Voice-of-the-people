# Voting System Architecture Guide

## System Overview

This voting system is modular and designed for student voting in schools. All components work together to provide a complete, secure voting platform.

## File Structure & Dependencies

```
Voice-of-the-people/
├── Public/
│   ├── index.html (existing login page)
│   └── style.css (existing styles)
├── candidates.php (NEW - Candidate Management)
├── voter_session.php (NEW - Session & Auth)
├── voting.php (NEW - Core Voting Logic)
├── results.php (NEW - Results & Rankings)
├── admin.php (NEW - Admin Functions)
├── config.php (NEW - Configuration & Utils)
├── api.php (NEW - REST API)
└── data/ (auto-created - stores JSON data)
    ├── candidates.json
    ├── votes.json
    ├── registered_voters.json
    ├── voted_voters.json
    └── admin_users.json
```

## Module Descriptions

### 1. **candidates.php** - Candidate Management
Manages all candidates across different voting positions.

**Key Methods:**
- `addCandidate($name, $position, $batch)` - Add a candidate
- `getAllCandidates()` - Get all candidates
- `getCandidatesByPosition($position)` - Filter by position
- `incrementVoteCount($candidateId)` - Update vote count
- `getPositions()` - Get all unique positions

**Example:**
```php
require_once 'candidates.php';
$manager = new CandidateManager();
$manager->addCandidate('John Doe', 'School President', '2024');
```

---

### 2. **voter_session.php** - Authentication & Session Management
Manages voter login, prevents duplicate votes, and tracks voting participation.

**Key Methods:**
- `authenticateVoter($voterId, $voterKey)` - Login voter
- `registerVoter($voterId, $voterKey, $name, $batch)` - Add voter
- `hasVoted($voterId)` - Check if already voted
- `markAsVoted($voterId)` - Record vote submission
- `getVotingStats()` - Get participation stats

**Login Flow:**
1. Voter enters Voter ID + Voter Key (Student ID)
2. System verifies credentials
3. Creates secure session
4. Prevents second vote attempt

---

### 3. **voting.php** - Core Voting Engine
Validates and records votes securely.

**Key Methods:**
- `castVote($voterId, $candidateId, $position)` - Submit vote
- `getAllVotes()` - Retrieve all votes
- `getVotesByPosition($position)` - Filter votes by position
- `getVoteCountByBatch()` - Voting breakdown by student batch

**Vote Validation:**
✓ Voter must be authenticated
✓ Voter cannot vote twice
✓ Candidate must exist
✓ Candidate position must match ballot position

---

### 4. **results.php** - Results & Tallying
Calculates final results, rankings, and reports.

**Key Methods:**
- `getAllResults()` - Results for all positions
- `getResultsByPosition($position)` - Specific position results
- `getWinner($position)` - Top candidate for position
- `getAllWinners()` - Winners across all positions
- `getDetailedReport()` - Comprehensive voting report

**Output Example:**
```json
{
  "position": "School President",
  "totalVotes": 450,
  "candidates": [
    {
      "rank": 1,
      "name": "John Doe",
      "votes": 180,
      "percentage": 40.0
    }
  ]
}
```

---

### 5. **admin.php** - Admin Dashboard
Administrative functions for system management.

**Key Methods:**
- `authenticateAdmin($username, $password)` - Admin login
- `getDashboardStats()` - Overview statistics
- `getVoterManagement()` - Registered & voted voters
- `resetVotingSystem()` - Clear votes (reset all counts)
- `generateAuditReport()` - Complete system report

**Dashboard Stats Include:**
- Total positions & candidates
- Voter turnout percentage
- Votes by student batch
- Registered vs. voted count

---

### 6. **config.php** - Configuration & Utilities
Global configuration and helper functions.

**Components:**
- **Constants:** System name, version, student batches
- **Logger Class:** Log system events
- **Validator Class:** Validate inputs
- **ResponseFormatter:** Format API responses

**Student Batches:**
```php
STUDENT_BATCHES = ['2024', '2023', '2022', '2021']
```

**Voting Positions:**
```php
VOTING_POSITIONS = [
    'School President',
    'School Vice President',
    'School Secretary',
    'School Treasurer',
    'Class Representative'
]
```

---

### 7. **api.php** - REST API Endpoints
Provides HTTP endpoints for frontend integration.

**Endpoints:**

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/login` | POST | Authenticate voter |
| `/api/cast-vote` | POST | Submit vote |
| `/api/candidates` | GET | Get candidates (optionally by position) |
| `/api/positions` | GET | Get all voting positions |
| `/api/results` | GET | Get results (optionally by position) |
| `/api/stats` | GET | Get voting statistics |
| `/api/logout` | POST | End voter session |
| `/api/admin/login` | POST | Authenticate admin |
| `/api/admin/dashboard` | GET | Get admin dashboard |

---

## Integration Guide

### Connecting the Frontend (index.html)

Modify your login form to use the API:

```javascript
// Handle login form submission
document.querySelector('form').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const voterId = document.querySelector('input[name="voterId"]').value;
  const voterKey = document.querySelector('input[name="Voter-key"]').value;
  
  const response = await fetch('/api/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ voterId, voterKey })
  });
  
  const result = await response.json();
  
  if (result.status === 'success') {
    // Redirect to voting page
    window.location.href = '/voting.html';
  } else {
    alert(result.message);
  }
});
```

---

## Usage Examples

### 1. Add Candidates (Admin)
```php
require_once 'candidates.php';
$manager = new CandidateManager();

$manager->addCandidate('Alice Johnson', 'School President', '2024');
$manager->addCandidate('Bob Smith', 'School President', '2024');
$manager->addCandidate('Carol Davis', 'School VP', '2023');
```

### 2. Register Voter (Admin)
```php
require_once 'voter_session.php';
$session = new VoterSessionManager();

$session->registerVoter('STU001', '20240001', 'John Student', '2024');
```

### 3. Cast Vote (During Voting)
```php
require_once 'voting.php';
$engine = new VotingEngine();

// After student is authenticated
$result = $engine->castVote('STU001', 'cand_xyz123', 'School President');
```

### 4. Get Results (After Voting)
```php
require_once 'results.php';
$results = new ResultsEngine();

$allResults = $results->getAllResults();
$winners = $results->getAllWinners();
```

### 5. Generate Report (Admin)
```php
require_once 'admin.php';
$admin = new AdminPanel();

if ($admin->isAdminAuthenticated()) {
    $report = $admin->generateAuditReport();
    echo json_encode($report);
}
```

---

## Security Features

✅ **Password Hashing** - Bcrypt for admin passwords
✅ **Session Management** - PHP sessions for voter tracking
✅ **Input Validation** - Sanitization & type checking
✅ **Duplicate Vote Prevention** - Maintains voted voter list
✅ **Audit Logging** - All actions logged for transparency
✅ **Admin Authentication** - Separate admin login system

---

## Data Storage (JSON Format)

### candidates.json
```json
[
  {
    "id": "cand_abc123",
    "name": "John Doe",
    "position": "School President",
    "batch": "2024",
    "votes": 125
  }
]
```

### votes.json
```json
[
  {
    "id": "vote_xyz789",
    "voterId": "STU001",
    "candidateId": "cand_abc123",
    "position": "School President",
    "timestamp": "2024-03-31 14:30:00"
  }
]
```

### registered_voters.json
```json
[
  {
    "voterId": "STU001",
    "voterKey": "20240001",
    "name": "John Student",
    "batch": "2024",
    "registeredDate": "2024-03-31 10:00:00"
  }
]
```

---

## Error Handling

All API responses follow this format:

**Success:**
```json
{
  "status": "success",
  "message": "Operation completed",
  "data": { ... },
  "timestamp": "2024-03-31 14:30:00"
}
```

**Error:**
```json
{
  "status": "error",
  "message": "Error description",
  "data": null,
  "timestamp": "2024-03-31 14:30:00"
}
```

---

## Next Steps

1. **Create voting HTML page** - Use candidates API to display ballot
2. **Create results page** - Use results API to show rankings
3. **Create admin dashboard** - Use admin endpoints for management
4. **Set up database** - Create `/data` folder with write permissions
5. **Deploy to server** - Ensure PHP 7.4+ support

---

## Support

For questions or issues, refer to the individual class documentation in each PHP file.
