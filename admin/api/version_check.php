<?php
/**
 * Version Checker API
 * Checks for new commits in the main branch and returns the number of commits behind
 */

header('Content-Type: application/json');

// Security check - ensure user is an admin
session_start();
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
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
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
