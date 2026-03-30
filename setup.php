<?php
/**
 * System Initialization & Setup Script
 * Run this once to initialize the voting system with sample data
 * Access via: http://your-domain/setup.php (password protected)
 */

require_once 'config.php';
require_once 'candidates.php';
require_once 'voter_session.php';
require_once 'admin.php';

// SECURITY: Change this password before deployment
$SETUP_PASSWORD = 'admin123'; // ⚠️ CHANGE THIS!

// Check for setup password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if ($password !== $SETUP_PASSWORD) {
        die('<h2>❌ Invalid password. Setup access denied.</h2>');
    }
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'initialize') {
        initializeSystem();
    } elseif ($action === 'add_candidates') {
        addSampleCandidates();
    } elseif ($action === 'add_voters') {
        addSampleVoters();
    } elseif ($action === 'add_admin') {
        addAdminUser();
    }
}

/**
 * Initialize system directories and files
 */
function initializeSystem() {
    echo '<h2>✅ System Initialization</h2>';
    
    // Create data directory
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
        echo '<p>✓ Data directory created</p>';
    } else {
        echo '<p>✓ Data directory exists</p>';
    }
    
    // Create logs directory
    if (!is_dir(LOG_DIR)) {
        mkdir(LOG_DIR, 0755, true);
        echo '<p>✓ Logs directory created</p>';
    } else {
        echo '<p>✓ Logs directory exists</p>';
    }
    
    // Initialize empty files
    $files = [
        DATA_DIR . '/candidates.json',
        DATA_DIR . '/votes.json',
        DATA_DIR . '/registered_voters.json',
        DATA_DIR . '/voted_voters.json',
        DATA_DIR . '/admin_users.json'
    ];
    
    foreach ($files as $file) {
        if (!file_exists($file)) {
            file_put_contents($file, '[]');
            echo '<p>✓ Created ' . basename($file) . '</p>';
        }
    }
    
    Logger::log('INFO', 'System initialized');
    echo '<p><strong>System ready!</strong></p>';
}

/**
 * Add sample candidates
 */
function addSampleCandidates() {
    echo '<h2>✅ Adding Sample Candidates</h2>';
    
    $manager = new CandidateManager();
    
    $candidates = [
        // School President
        ['Alice Johnson', 'School President', '2024'],
        ['Bob Smith', 'School President', '2024'],
        ['Carol Davis', 'School President', '2023'],
        
        // School Vice President
        ['David Wilson', 'School Vice President', '2024'],
        ['Emma Brown', 'School Vice President', '2024'],
        
        // School Secretary
        ['Frank Miller', 'School Secretary', '2023'],
        ['Grace Lee', 'School Secretary', '2023'],
        
        // School Treasurer
        ['Henry Taylor', 'School Treasurer', '2022'],
        ['Iris Martinez', 'School Treasurer', '2022'],
        
        // Class Representative
        ['Jack Anderson', 'Class Representative', '2024'],
        ['Karen Thomas', 'Class Representative', '2024'],
    ];
    
    foreach ($candidates as $cand) {
        $result = $manager->addCandidate($cand[0], $cand[1], $cand[2]);
        echo '<p>✓ Added: ' . $result['name'] . ' (' . $result['position'] . ', ' . $result['batch'] . ')</p>';
    }
    
    Logger::log('INFO', 'Sample candidates added', ['count' => count($candidates)]);
}

/**
 * Add sample voters
 */
function addSampleVoters() {
    echo '<h2>✅ Registering Sample Voters</h2>';
    
    $session = new VoterSessionManager();
    
    $voters = [
        // 2024 batch
        ['STU001', '20240001', 'John Student', '2024'],
        ['STU002', '20240002', 'Jane Doe', '2024'],
        ['STU003', '20240003', 'Michael Chen', '2024'],
        
        // 2023 batch
        ['STU004', '20230004', 'Sarah Wilson', '2023'],
        ['STU005', '20230005', 'Ahmed Khan', '2023'],
        
        // 2022 batch
        ['STU006', '20220006', 'Lisa Anderson', '2022'],
        ['STU007', '20220007', 'Carlos Rodriguez', '2022'],
    ];
    
    foreach ($voters as $voter) {
        $result = $session->registerVoter($voter[0], $voter[1], $voter[2], $voter[3]);
        if ($result['success']) {
            echo '<p>✓ Registered: ' . $voter[2] . ' (' . $voter[3] . ' batch)</p>';
        }
    }
    
    Logger::log('INFO', 'Sample voters added', ['count' => count($voters)]);
}

/**
 * Add admin user
 */
function addAdminUser() {
    echo '<h2>✅ Creating Admin User</h2>';
    
    $admin = new AdminPanel();
    
    // Default admin credentials (CHANGE THESE!)
    $result = $admin->addAdminUser('admin', 'password123');
    
    if ($result['success']) {
        echo '<p>✓ Admin user created</p>';
        echo '<p style="color: red;"><strong>⚠️ WARNING:</strong> Change default admin credentials immediately!</p>';
        echo '<p>Username: <code>admin</code></p>';
        echo '<p>Password: <code>password123</code></p>';
    } else {
        echo '<p>' . $result['message'] . '</p>';
    }
    
    Logger::log('INFO', 'Admin user created');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUCOSA Voting System - Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0098ff;
            text-align: center;
        }
        .setup-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .form-group {
            margin: 15px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .button-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 20px;
        }
        button {
            padding: 10px 20px;
            background: #81c31c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        button:hover {
            background: #628b23;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .success {
            background: #d4edda;
            border: 1px solid #28a745;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #0098ff;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗳️ BUCOSA Voting System Setup</h1>
        
        <div class="warning">
            <strong>⚠️ Security Notice:</strong> This setup page should be protected or deleted after initialization.
            Change the setup password and remove this file from production.
        </div>
        
        <div class="info-box">
            <strong>Welcome!</strong> This page helps you initialize the voting system with sample data.
            Enter the setup password to proceed.
        </div>
        
        <form method="POST" class="setup-form">
            <div class="form-group">
                <label for="password">Setup Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="button-group">
                <button type="submit" name="action" value="initialize">Initialize System</button>
                <button type="submit" name="action" value="add_candidates">Add Sample Candidates</button>
                <button type="submit" name="action" value="add_voters">Add Sample Voters</button>
                <button type="submit" name="action" value="add_admin">Create Admin User</button>
            </div>
        </form>
        
        <div class="info-box">
            <h3>Setup Instructions:</h3>
            <ol>
                <li>Enter the setup password (default: <code>admin123</code>)</li>
                <li>Click "Initialize System" to create directories and files</li>
                <li>Click "Add Sample Candidates" to add test candidates</li>
                <li>Click "Add Sample Voters" to register test voters</li>
                <li>Click "Create Admin User" to set up admin account</li>
                <li>Delete or move this file to a secure location</li>
                <li>Change all default passwords</li>
            </ol>
        </div>
        
        <div class="info-box">
            <h3>Sample Test Credentials (after setup):</h3>
            <p><strong>Student Login (Voter ID / Voter Key):</strong></p>
            <ul>
                <li>STU001 / 20240001 (John Student)</li>
                <li>STU002 / 20240002 (Jane Doe)</li>
                <li>STU003 / 20240003 (Michael Chen)</li>
            </ul>
            <p><strong>Admin Login:</strong></p>
            <ul>
                <li>Username: admin</li>
                <li>Password: password123</li>
            </ul>
        </div>
    </div>
</body>
</html>
