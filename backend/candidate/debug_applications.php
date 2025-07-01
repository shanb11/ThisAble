<?php
// backend/candidate/debug_applications.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../db.php';
require_once '../../includes/candidate/session_check.php';

// Check if user is logged in
if (!is_logged_in()) {
    echo "Not logged in. Current session: ";
    print_r($_SESSION);
    exit;
}

$seeker_id = get_seeker_id();
echo "<h2>Debug Applications for Seeker ID: $seeker_id</h2>";

try {
    // Check available applications
    $query = "
        SELECT 
            ja.application_id,
            ja.job_id,
            ja.seeker_id,
            ja.application_status,
            ja.applied_at,
            jp.job_title,
            e.company_name
        FROM job_applications ja
        INNER JOIN job_posts jp ON ja.job_id = jp.job_id
        INNER JOIN employers e ON jp.employer_id = e.employer_id
        WHERE ja.seeker_id = :seeker_id
        ORDER BY ja.applied_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->execute();
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Found " . count($applications) . " applications:</h3>";
    
    if (empty($applications)) {
        echo "<p>No applications found for this user.</p>";
        
        // Check if there are any applications at all
        $allQuery = "SELECT COUNT(*) as total FROM job_applications";
        $allStmt = $conn->prepare($allQuery);
        $allStmt->execute();
        $total = $allStmt->fetch()['total'];
        echo "<p>Total applications in database: $total</p>";
        
        // Check if user exists
        $userQuery = "SELECT * FROM job_seekers WHERE seeker_id = :seeker_id";
        $userStmt = $conn->prepare($userQuery);
        $userStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
        $userStmt->execute();
        $user = $userStmt->fetch();
        echo "<p>User exists: " . ($user ? "Yes" : "No") . "</p>";
        
    } else {
        echo "<table border='1'>";
        echo "<tr><th>App ID</th><th>Job Title</th><th>Company</th><th>Status</th><th>Applied Date</th></tr>";
        foreach ($applications as $app) {
            echo "<tr>";
            echo "<td>{$app['application_id']}</td>";
            echo "<td>{$app['job_title']}</td>";
            echo "<td>{$app['company_name']}</td>";
            echo "<td>{$app['application_status']}</td>";
            echo "<td>{$app['applied_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>Test URLs:</h3>";
        foreach ($applications as $app) {
            echo "<p><a href='get_application_details.php?application_id={$app['application_id']}' target='_blank'>Test Application ID {$app['application_id']}</a></p>";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>