<?php
/**
 * Version Checker API
 * Checks for new commits in the main branch and returns the number of commits behind
 * Caches results for 1 hour to avoid running git commands on every page load
 */

header('Content-Type: application/json');

// Security check - ensure user is an admin
session_start();
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once(__DIR__ . '/../../src/db.php');

try {
    // Create version_cache table if it doesn't exist
    $db->query("CREATE TABLE IF NOT EXISTS version_cache (
        id INT PRIMARY KEY DEFAULT 1,
        current_branch VARCHAR(255),
        current_commit VARCHAR(40),
        remote_commit VARCHAR(40),
        commits_behind INT,
        commits_ahead INT,
        last_commit_date VARCHAR(255),
        last_commit_message TEXT,
        last_commit_author VARCHAR(255),
        last_checked TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT single_row CHECK (id = 1)
    )");
    
    // Check if we have cached data that's less than 1 hour old
    $cacheQuery = $db->query("SELECT *, UNIX_TIMESTAMP(last_checked) as last_checked_unix FROM version_cache WHERE id = 1");
    $cachedData = $cacheQuery ? $cacheQuery->fetch_assoc() : null;
    
    $cacheExpiry = 3600; // 1 hour in seconds
    $currentTime = time();
    
    // If we have valid cached data, return it
    if ($cachedData && ($currentTime - $cachedData['last_checked_unix']) < $cacheExpiry) {
        $response = [
            'success' => true,
            'currentBranch' => $cachedData['current_branch'],
            'currentCommit' => substr($cachedData['current_commit'], 0, 7),
            'remoteCommit' => substr($cachedData['remote_commit'], 0, 7),
            'commitsBehind' => (int)$cachedData['commits_behind'],
            'commitsAhead' => (int)$cachedData['commits_ahead'],
            'upToDate' => (int)$cachedData['commits_behind'] === 0,
            'lastCommitDate' => $cachedData['last_commit_date'],
            'lastCommitMessage' => $cachedData['last_commit_message'],
            'lastCommitAuthor' => $cachedData['last_commit_author'],
            'timestamp' => $cachedData['last_checked'],
            'cached' => true
        ];
        
        echo json_encode($response);
        exit;
    }
    
    // Cache expired or doesn't exist, fetch new data from git
    // Get the repository root directory (parent of admin folder)
    $repoPath = realpath(__DIR__ . '/../../');
    
    if (!$repoPath || !is_dir($repoPath . '/.git')) {
        throw new Exception('Not a git repository');
    }
    
    // Change to repository directory
    chdir($repoPath);
    
    // Fetch latest changes from remote (without pulling)
    exec('git fetch origin main 2>&1', $fetchOutput, $fetchReturn);
    
    if ($fetchReturn !== 0) {
        throw new Exception('Failed to fetch from remote: ' . implode("\n", $fetchOutput));
    }
    
    // Get current branch
    exec('git rev-parse --abbrev-ref HEAD 2>&1', $branchOutput, $branchReturn);
    $currentBranch = trim($branchOutput[0] ?? 'main');
    
    // Get current commit hash
    exec('git rev-parse HEAD 2>&1', $currentCommitOutput, $currentCommitReturn);
    $currentCommit = trim($currentCommitOutput[0] ?? '');
    
    // Get remote commit hash for main branch
    exec('git rev-parse origin/main 2>&1', $remoteCommitOutput, $remoteCommitReturn);
    $remoteCommit = trim($remoteCommitOutput[0] ?? '');
    
    // Count commits behind
    exec('git rev-list --count HEAD..origin/main 2>&1', $behindOutput, $behindReturn);
    $commitsBehind = intval(trim($behindOutput[0] ?? '0'));
    
    // Count commits ahead (if any)
    exec('git rev-list --count origin/main..HEAD 2>&1', $aheadOutput, $aheadReturn);
    $commitsAhead = intval(trim($aheadOutput[0] ?? '0'));
    
    // Get last commit date on remote
    exec('git log origin/main -1 --format=%ci 2>&1', $dateOutput, $dateReturn);
    $lastCommitDate = trim($dateOutput[0] ?? '');
    
    // Get last commit message on remote
    exec('git log origin/main -1 --format=%s 2>&1', $messageOutput, $messageReturn);
    $lastCommitMessage = trim($messageOutput[0] ?? '');
    
    // Get last commit author on remote
    exec('git log origin/main -1 --format=%an 2>&1', $authorOutput, $authorReturn);
    $lastCommitAuthor = trim($authorOutput[0] ?? '');
    
    // Prepare response data
    $response = [
        'success' => true,
        'currentBranch' => $currentBranch,
        'currentCommit' => substr($currentCommit, 0, 7),
        'remoteCommit' => substr($remoteCommit, 0, 7),
        'commitsBehind' => $commitsBehind,
        'commitsAhead' => $commitsAhead,
        'upToDate' => $commitsBehind === 0,
        'lastCommitDate' => $lastCommitDate,
        'lastCommitMessage' => $lastCommitMessage,
        'lastCommitAuthor' => $lastCommitAuthor,
        'timestamp' => date('Y-m-d H:i:s'),
        'cached' => false
    ];
    
    // Save to cache (use INSERT ... ON DUPLICATE KEY UPDATE for single row)
    $stmt = $db->prepare("INSERT INTO version_cache (
        id, current_branch, current_commit, remote_commit, 
        commits_behind, commits_ahead, last_commit_date, 
        last_commit_message, last_commit_author
    ) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        current_branch = VALUES(current_branch),
        current_commit = VALUES(current_commit),
        remote_commit = VALUES(remote_commit),
        commits_behind = VALUES(commits_behind),
        commits_ahead = VALUES(commits_ahead),
        last_commit_date = VALUES(last_commit_date),
        last_commit_message = VALUES(last_commit_message),
        last_commit_author = VALUES(last_commit_author),
        last_checked = CURRENT_TIMESTAMP");
    
    if ($stmt) {
        $stmt->bind_param('sssiisss',
            $currentBranch,
            $currentCommit,
            $remoteCommit,
            $commitsBehind,
            $commitsAhead,
            $lastCommitDate,
            $lastCommitMessage,
            $lastCommitAuthor
        );
        $stmt->execute();
        $stmt->close();
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
