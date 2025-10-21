<!-- Accessibility Edit Modal -->
<div class="modal-overlay" id="accessibility-modal" style="display: none;">
    <div class="modal-content accessibility-modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-universal-access"></i> Edit Accessibility Needs</h3>
            <button type="button" class="close-modal-btn" id="close-accessibility-modal-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <form id="accessibility-form">
                
                <!-- Disability Type Selection -->
                <div class="accessibility-section">
                    <h4><i class="fas fa-user-check"></i> Disability Type</h4>
                    <div class="radio-group">
                        <div class="radio-item">
                            <input type="radio" id="apparent-disability" name="disability_type" value="apparent" 
                                   <?php echo ($accommodations && $accommodations['disability_type'] == 'apparent') ? 'checked' : ''; ?>>
                            <label for="apparent-disability">
                                <i class="fas fa-eye"></i>
                                <span>Apparent Disability</span>
                                <small>Visible or obvious to others</small>
                            </label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="non-apparent-disability" name="disability_type" value="non-apparent" 
                                   <?php echo ($accommodations && $accommodations['disability_type'] == 'non-apparent') ? 'checked' : ''; ?>>
                            <label for="non-apparent-disability">
                                <i class="fas fa-eye-slash"></i>
                                <span>Non-Apparent Disability</span>
                                <small>Not immediately visible to others</small>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Accommodation Needs -->
                <div class="accessibility-section">
                    <h4><i class="fas fa-hands-helping"></i> Select Your Accommodation Needs</h4>
                    
                    <!-- Common Accommodations Grid -->
                    <div class="accommodations-grid">
                        <?php 
                        $common_accommodations = [
                            ['value' => 'Screen reader compatible documents', 'icon' => 'fa-low-vision', 'category' => 'visual'],
                            ['value' => 'Large print materials', 'icon' => 'fa-text-height', 'category' => 'visual'],
                            ['value' => 'High contrast interfaces', 'icon' => 'fa-palette', 'category' => 'visual'],
                            ['value' => 'Keyboard navigation support', 'icon' => 'fa-keyboard', 'category' => 'motor'],
                            ['value' => 'Closed captions for videos', 'icon' => 'fa-closed-captioning', 'category' => 'hearing'],
                            ['value' => 'Sign language interpretation', 'icon' => 'fa-sign-language', 'category' => 'hearing'],
                            ['value' => 'Simple, clear language', 'icon' => 'fa-align-left', 'category' => 'cognitive'],
                            ['value' => 'Accessible restrooms', 'icon' => 'fa-restroom', 'category' => 'mobility'],
                            ['value' => 'Wheelchair accessibility', 'icon' => 'fa-wheelchair', 'category' => 'mobility'],
                            ['value' => 'Flexible work hours', 'icon' => 'fa-clock', 'category' => 'other'],
                            ['value' => 'Reduced stimulation space', 'icon' => 'fa-volume-mute', 'category' => 'sensory'],
                            ['value' => 'Reading assistance', 'icon' => 'fa-book-reader', 'category' => 'cognitive']
                        ];
                        
                        foreach ($common_accommodations as $accommodation): 
                            $checked = in_array($accommodation['value'], $accommodation_list) ? 'checked' : '';
                        ?>
                        <div class="accommodation-item">
                            <input type="checkbox" 
                                   id="need-<?php echo str_replace(' ', '-', strtolower($accommodation['value'])); ?>" 
                                   name="accommodations[]" 
                                   value="<?php echo $accommodation['value']; ?>"
                                   <?php echo $checked; ?>>
                            <label for="need-<?php echo str_replace(' ', '-', strtolower($accommodation['value'])); ?>">
                                <div class="accommodation-icon <?php echo $accommodation['category']; ?>">
                                    <i class="fas <?php echo $accommodation['icon']; ?>"></i>
                                </div>
                                <span class="accommodation-text"><?php echo $accommodation['value']; ?></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- No Accommodations Option -->
                    <div class="no-accommodations-option">
                        <div class="accommodation-item special">
                            <input type="checkbox" id="no-accommodations" name="no_accommodations" value="1"
                                   <?php echo ($accommodations && $accommodations['no_accommodations_needed'] == 1) ? 'checked' : ''; ?>>
                            <label for="no-accommodations">
                                <div class="accommodation-icon success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <span class="accommodation-text">I don't need any specific accommodations</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Notes -->
                <div class="accessibility-section">
                    <h4><i class="fas fa-sticky-note"></i> Additional Notes</h4>
                    <textarea id="additional-notes" name="additional_notes" rows="4" 
                              placeholder="Provide any additional details about your accessibility needs, preferred accommodations, or specific requirements..."></textarea>
                </div>
                
                <!-- Disclosure Preferences -->
                <div class="accessibility-section">
                    <h4><i class="fas fa-user-shield"></i> Disclosure Preferences</h4>
                    <div class="disclosure-options">
                        <div class="disclosure-item">
                            <input type="checkbox" id="edit-disclose-application" name="disclose_application" checked>
                            <label for="edit-disclose-application">
                                <div class="disclosure-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="disclosure-content">
                                    <span class="disclosure-title">Disclose my disability in job applications</span>
                                    <small class="disclosure-desc">Share disability information when applying for positions</small>
                                </div>
                            </label>
                        </div>
                        <div class="disclosure-item">
                            <input type="checkbox" id="edit-disclose-interview" name="disclose_interview" checked>
                            <label for="edit-disclose-interview">
                                <div class="disclosure-icon">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <div class="disclosure-content">
                                    <span class="disclosure-title">Disclose my accessibility needs for interviews</span>
                                    <small class="disclosure-desc">Share accommodation needs during interview scheduling</small>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                
            </form>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn secondary-btn" onclick="closeAccessibilityModal()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button type="button" class="btn primary-btn" onclick="saveAccessibilityNeeds()">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>
</div>