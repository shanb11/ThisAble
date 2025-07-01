<?php
// backend/candidate/get_seeker_skills.php
session_start();
require_once '../db.php';

if (!isset($_SESSION['seeker_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

$seeker_id = $_SESSION['seeker_id'];

// Get all skill categories
$category_query = "SELECT * FROM skill_categories ORDER BY category_name";
$category_result = $conn->query($category_query);

$categories = [];
while ($row = $category_result->fetch_assoc()) {
    $categories[] = $row;
}

// Get user's skills
$user_skills_query = "SELECT s.skill_id, s.skill_name, s.category_id FROM seeker_skills ss
                      JOIN skills s ON ss.skill_id = s.skill_id
                      WHERE ss.seeker_id = ?";
$stmt = $conn->prepare($user_skills_query);
$stmt->bind_param("i", $seeker_id);
$stmt->execute();
$skills_result = $stmt->get_result();

$user_skills = [];
while ($row = $skills_result->fetch_assoc()) {
    $user_skills[] = $row;
}

echo json_encode([
    'status' => 'success',
    'categories' => $categories,
    'user_skills' => $user_skills
]);
?>