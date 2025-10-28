    // Contact Form Submission
    document.getElementById('contact-form').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Thank you for your message! We will get back to you shortly.');
    this.reset();
});

// Footer navigation links
document.getElementById('footer-browse-jobs').addEventListener('click', function(e) {
    e.preventDefault();
    window.location.href = 'landing_jobs.php';
});

document.getElementById('footer-post-job').addEventListener('click', function(e) {
    e.preventDefault();
    window.location.href = 'landing_jobs.php';
});

// Accessibility feature - High contrast toggle
function toggleHighContrast() {
    document.body.classList.toggle('high-contrast');
}