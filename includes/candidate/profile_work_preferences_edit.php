<?php
// Fetch user preferences
$pref_query = "SELECT * FROM user_preferences WHERE seeker_id = :seeker_id";
$pref_stmt = $conn->prepare($pref_query);
$pref_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
$pref_stmt->execute();
$preferences = $pref_stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="edit-form" id="preferences-edit-form">
    <form id="preferences-form">
        <div class="form-grid">
            <div class="form-group">
                <label for="work-style">Work Style</label>
                <select id="work-style" name="work_style">
                    <option value="remote" <?php echo ($preferences && $preferences['work_style'] == 'remote') ? 'selected' : ''; ?>>Remote</option>
                    <option value="hybrid" <?php echo ($preferences && $preferences['work_style'] == 'hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                    <option value="onsite" <?php echo ($preferences && $preferences['work_style'] == 'onsite') ? 'selected' : ''; ?>>Onsite</option>
                </select>
            </div>
            <div class="form-group">
                <label for="job-type">Job Type</label>
                <select id="job-type" name="job_type">
                    <option value="fulltime" <?php echo ($preferences && $preferences['job_type'] == 'fulltime') ? 'selected' : ''; ?>>Full-Time</option>
                    <option value="parttime" <?php echo ($preferences && $preferences['job_type'] == 'parttime') ? 'selected' : ''; ?>>Part-Time</option>
                    <option value="freelance" <?php echo ($preferences && $preferences['job_type'] == 'freelance') ? 'selected' : ''; ?>>Freelance</option>
                </select>
            </div>
            <div class="form-group">
                <label for="salary-range">Expected Salary</label>
                <select id="salary-range" name="salary_range">
                    <option value="Below ₱20,000" <?php echo ($preferences && $preferences['salary_range'] == 'Below ₱20,000') ? 'selected' : ''; ?>>Below ₱20,000</option>
                    <option value="₱20,000 - ₱30,000" <?php echo ($preferences && $preferences['salary_range'] == '₱20,000 - ₱30,000') ? 'selected' : ''; ?>>₱20,000 - ₱30,000</option>
                    <option value="₱30,000 - ₱40,000" <?php echo ($preferences && $preferences['salary_range'] == '₱30,000 - ₱40,000') ? 'selected' : ''; ?>>₱30,000 - ₱40,000</option>
                    <option value="₱40,000 - ₱50,000" <?php echo ($preferences && $preferences['salary_range'] == '₱40,000 - ₱50,000') ? 'selected' : ''; ?>>₱40,000 - ₱50,000</option>
                    <option value="Above ₱50,000" <?php echo ($preferences && $preferences['salary_range'] == 'Above ₱50,000') ? 'selected' : ''; ?>>Above ₱50,000</option>
                </select>
            </div>
            <div class="form-group">
                <label for="availability">Availability</label>
                <select id="availability" name="availability">
                    <option value="Immediate" <?php echo ($preferences && $preferences['availability'] == 'Immediate') ? 'selected' : ''; ?>>Immediate</option>
                    <option value="2 Weeks Notice" <?php echo ($preferences && $preferences['availability'] == '2 Weeks Notice') ? 'selected' : ''; ?>>2 Weeks Notice</option>
                    <option value="1 Month Notice" <?php echo ($preferences && $preferences['availability'] == '1 Month Notice') ? 'selected' : ''; ?>>1 Month Notice</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button type="button" class="btn cancel-btn" data-section="preferences">Cancel</button>
            <button type="submit" class="btn save-btn" data-section="preferences">Save Changes</button>
        </div>
    </form>
</div>