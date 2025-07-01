<div class="application-stats" id="application-stats">
    <!-- Stats will be populated dynamically by JavaScript -->
    <div class="stat-card" id="stat-total">
        <div class="icon">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="number" id="total-number">...</div>
        <div class="label">Total Applications</div>
        <div class="trend" id="total-trend" style="display: none;"></div>
    </div>
    
    <div class="stat-card" id="stat-reviewed">
        <div class="icon">
            <i class="fas fa-eye"></i>
        </div>
        <div class="number" id="reviewed-number">...</div>
        <div class="label">Applications Reviewed</div>
        <div class="percentage" id="reviewed-percentage" style="display: none;"></div>
    </div>
    
    <div class="stat-card" id="stat-interviews">
        <div class="icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="number" id="interviews-number">...</div>
        <div class="label">Interviews Scheduled</div>
        <div class="upcoming" id="upcoming-interviews" style="display: none;"></div>
    </div>
    
    <div class="stat-card" id="stat-offers">
        <div class="icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="number" id="offers-number">...</div>
        <div class="label">Job Offers</div>
        <div class="percentage" id="success-percentage" style="display: none;"></div>
    </div>
</div>

<style>
/* Additional styles for dynamic stats */
.stat-card {
    position: relative;
    transition: all 0.3s ease;
}

.stat-card .trend {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 12px;
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: 600;
}

.stat-card .trend.positive {
    background: #d4edda;
    color: #155724;
}

.stat-card .trend.negative {
    background: #f8d7da;
    color: #721c24;
}

.stat-card .percentage {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
}

.stat-card .upcoming {
    font-size: 12px;
    color: #007bff;
    margin-top: 4px;
    font-weight: 500;
}

.stat-card.loading .number {
    position: relative;
}

.stat-card.loading .number::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

.stat-card.error {
    opacity: 0.6;
}

.stat-card.error .number {
    color: #dc3545;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .application-stats {
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    
    .stat-card {
        padding: 15px;
    }
    
    .stat-card .number {
        font-size: 24px;
    }
    
    .stat-card .trend {
        position: static;
        margin-top: 5px;
        display: inline-block;
    }
}

@media (max-width: 480px) {
    .application-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Initialize stats loading state
document.addEventListener('DOMContentLoaded', function() {
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.classList.add('loading');
    });
});

// Function to update stats display (called from applications.js)
function updateStatsDisplay() {
    const statCards = document.querySelectorAll('.stat-card');
    
    if (statCards.length >= 4 && window.applicationStats && window.applicationStats.cards) {
        // Remove loading state
        statCards.forEach(card => {
            card.classList.remove('loading', 'error');
        });
        
        // Update each stat card with real data
        window.applicationStats.cards.forEach((card, index) => {
            if (statCards[index]) {
                const numberEl = statCards[index].querySelector('.number');
                const labelEl = statCards[index].querySelector('.label');
                const iconEl = statCards[index].querySelector('.icon i');
                
                if (numberEl) numberEl.textContent = card.number;
                if (labelEl) labelEl.textContent = card.label;
                if (iconEl) iconEl.className = card.icon;
                
                // Add trend indicator if available
                if (card.trend && card.trend !== '') {
                    let trendEl = statCards[index].querySelector('.trend');
                    if (trendEl) {
                        trendEl.textContent = card.trend;
                        trendEl.className = `trend ${card.trend_positive ? 'positive' : 'negative'}`;
                        trendEl.style.display = 'block';
                    }
                }
                
                // Add percentage if available
                if (card.percentage !== undefined) {
                    let percentEl = statCards[index].querySelector('.percentage');
                    if (percentEl) {
                        percentEl.textContent = `${card.percentage}% response rate`;
                        percentEl.style.display = 'block';
                    }
                }
                
                // Add upcoming count for interviews
                if (index === 2 && card.upcoming !== undefined) {
                    let upcomingEl = statCards[index].querySelector('.upcoming');
                    if (upcomingEl && card.upcoming > 0) {
                        upcomingEl.textContent = `${card.upcoming} upcoming`;
                        upcomingEl.style.display = 'block';
                    }
                }
            }
        });
    }
}

// Function to show stats error state
function showStatsError() {
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.classList.remove('loading');
        card.classList.add('error');
        const numberEl = card.querySelector('.number');
        if (numberEl) numberEl.textContent = '—';
    });
}

// Listen for stats update events
document.addEventListener('statsLoaded', updateStatsDisplay);
document.addEventListener('statsError', showStatsError);
</script>