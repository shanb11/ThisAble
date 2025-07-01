<?php
// Fetch workplace accommodations
$accom_query = "SELECT * FROM workplace_accommodations WHERE seeker_id = :seeker_id";
$accom_stmt = $conn->prepare($accom_query);
$accom_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
$accom_stmt->execute();
$accommodations = $accom_stmt->fetch(PDO::FETCH_ASSOC);

// Parse accommodation list
$accommodation_list = [];
if ($accommodations && !empty($accommodations['accommodation_list'])) {
    $accommodation_list = json_decode($accommodations['accommodation_list'], true);
}

// Define common accommodation options
$common_accommodations = [
    'Screen reader compatible documents',
    'Large print materials',
    'High contrast interfaces',
    'Keyboard navigation support',
    'Closed captions for videos',
    'Sign language interpretation',
    'Simple, clear language',
    'Accessible restrooms',
    'Wheelchair accessibility',
    'Flexible work hours',
    'Reduced stimulation space',
    'Reading assistance'
];
?>

<div class="edit-form" id="accessibility-edit-form">
    <form id="accessibility-form">
        <div class="form-group">
            <label>Select Disability Type</label>
            <div class="radio-group">
                <div class="radio-item">
                    <input type="radio" id="apparent-disability" name="disability_type" value="apparent" 
                           <?php echo ($accommodations && $accommodations['disability_type'] == 'apparent') ? 'checked' : ''; ?>>
                    <label for="apparent-disability">Apparent Disability</label>
                </div>
                <div class="radio-item">
                    <input type="radio" id="non-apparent-disability" name="disability_type" value="non-apparent" 
                           <?php echo ($accommodations && $accommodations['disability_type'] == 'non-apparent') ? 'checked' : ''; ?>>
                    <label for="non-apparent-disability">Non-Apparent Disability</label>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label>Select Accessibility Needs</label>
            <div class="checkbox-grid">
                <?php foreach ($common_accommodations as $accommodation): ?>
                <div class="checkbox-item">
                    <input type="checkbox" id="need-<?php echo str_replace(' ', '-', strtolower($accommodation)); ?>" 
                           name="accommodations[]" value="<?php echo $accommodation; ?>"
                           <?php echo in_array($accommodation, $accommodation_list) ? 'checked' : ''; ?>>
                    <label for="need-<?php echo str_replace(' ', '-', strtolower($accommodation)); ?>"><?php echo $accommodation; ?></label>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="checkbox-item mt-3">
                <input type="checkbox" id="no-accommodations" name="no_accommodations" value="1"
                       <?php echo ($accommodations && $accommodations['no_accommodations_needed'] == 1) ? 'checked' : ''; ?>>
                <label for="no-accommodations">I don't need any specific accommodations</label>
            </div>
        </div>
        
        <div class="form-group">
            <label for="additional-notes">Additional Notes</label>
            <textarea id="additional-notes" name="additional_notes" rows="4" placeholder="Provide any additional details about your accessibility needs..."></textarea>
        </div>
        
        <div class="form-group">
            <label>Disclosure Preferences</label>
            <div class="checkbox-item">
                <input type="checkbox" id="edit-disclose-application" name="disclose_application" checked>
                <label for="edit-disclose-application">Disclose my disability in job applications</label>
            </div>
            <div class="checkbox-item">
                <input type="checkbox" id="edit-disclose-interview" name="disclose_interview" checked>
                <label for="edit-disclose-interview">Disclose my accessibility needs for interviews</label>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="button" class="btn cancel-btn" data-section="accessibility">Cancel</button>
            <button type="submit" class="btn save-btn" data-section="accessibility">Save Changes</button>
        </div>
    </form>
</div>