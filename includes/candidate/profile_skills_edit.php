<!-- Skills Edit Modal -->
<div class="modal-overlay" id="skills-modal" style="display: none;">
    <div class="modal-content skills-modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-tools"></i> Edit Skills</h3>
            <button type="button" class="close-modal-btn" onclick="closeSkillsModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <form id="skills-form">
                
                <!-- Search Skills -->
                <div class="skills-search-container">
                    <div class="search-input-group">
                        <i class="fas fa-search"></i>
                        <input type="text" id="skills-search" placeholder="Search skills or add your own..." autocomplete="off">
                        <button type="button" id="add-custom-skill-btn" class="add-custom-btn">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                    <div class="search-results" id="skills-search-results" style="display: none;"></div>
                </div>
                
                <!-- Selected Skills -->
                <div class="selected-skills-section">
                    <h4><i class="fas fa-check-circle"></i> Your Skills</h4>
                    <div class="selected-skills-container" id="selected-skills-container">
                        <!-- Selected skills will appear here -->
                    </div>
                </div>
                
                <!-- Popular Skills by Category -->
                <div class="popular-skills-section">
                    <h4><i class="fas fa-star"></i> Popular Skills</h4>
                    
                    <div class="skills-tabs">
                        <button type="button" class="skills-tab active" data-category="technical">
                            <i class="fas fa-code"></i> Technical
                        </button>
                        <button type="button" class="skills-tab" data-category="soft">
                            <i class="fas fa-heart"></i> Soft Skills
                        </button>
                        <button type="button" class="skills-tab" data-category="language">
                            <i class="fas fa-globe"></i> Languages
                        </button>
                    </div>
                    
                    <div class="skills-tab-content">
                        <!-- Technical Skills -->
                        <div class="skills-category-content active" data-category="technical">
                            <div class="popular-skills-grid">
                                <?php
                                $technical_skills = [
                                    'JavaScript', 'PHP', 'Python', 'Java', 'HTML/CSS', 'React',
                                    'Node.js', 'MySQL', 'WordPress', 'Photoshop', 'Excel',
                                    'Data Entry', 'Web Development', 'Database Management'
                                ];
                                foreach($technical_skills as $skill) {
                                    echo '<button type="button" class="popular-skill-btn" data-skill="'.$skill.'">'.$skill.'</button>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <!-- Soft Skills -->
                        <div class="skills-category-content" data-category="soft">
                            <div class="popular-skills-grid">
                                <?php
                                $soft_skills = [
                                    'Communication', 'Problem Solving', 'Team Work', 'Leadership',
                                    'Time Management', 'Adaptability', 'Critical Thinking', 'Creativity',
                                    'Customer Service', 'Attention to Detail', 'Organization', 'Multitasking'
                                ];
                                foreach($soft_skills as $skill) {
                                    echo '<button type="button" class="popular-skill-btn" data-skill="'.$skill.'">'.$skill.'</button>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <!-- Languages -->
                        <div class="skills-category-content" data-category="language">
                            <div class="popular-skills-grid">
                                <?php
                                $languages = [
                                    'Filipino', 'English', 'Spanish', 'Mandarin', 'Japanese',
                                    'Korean', 'French', 'German', 'Arabic', 'Sign Language'
                                ];
                                foreach($languages as $skill) {
                                    echo '<button type="button" class="popular-skill-btn" data-skill="'.$skill.'">'.$skill.'</button>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
            </form>
        </div>
        
        <div class="modal-footer">
<button type="button" class="close-modal-btn" onclick="closeSkillsModal()">
    <i class="fas fa-times"></i>
</button>
            <button type="button" class="btn primary-btn" onclick="saveSkills()">
                <i class="fas fa-save"></i> Save Skills
            </button>
        </div>
    </div>
</div>