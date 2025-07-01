<div class="modal-overlay" id="forgotPasswordModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Reset Password</h3>
            <button class="modal-close" onclick="closeForgotPassword()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Enter your company email address and we'll send you a link to reset your password.</p>
            <div class="input-box">
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" placeholder="Company Email Address" id="reset-email" required>
            </div>
            <div id="reset-message" class="message" style="display: none; margin-top: 1rem;"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeForgotPassword()">Cancel</button>
            <button class="btn btn-primary" onclick="sendResetEmail()" id="reset-btn">
                Send Reset Link
            </button>
        </div>
    </div>
</div>

<style>
/* Modal styles to match your existing design */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-overlay.active {
    display: flex;
}

.modal {
    background: white;
    border-radius: 16px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.modal-header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    color: #0f172a;
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #64748b;
    cursor: pointer;
    transition: color 0.3s ease;
}

.modal-close:hover {
    color: #0f172a;
}

.modal-body {
    padding: 2rem;
}

.modal-body p {
    color: #475569;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.modal-footer {
    padding: 1rem 2rem 2rem;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.3s ease;
}

.btn-secondary {
    background: #f1f5f9;
    color: #475569;
}

.btn-secondary:hover {
    background: #e2e8f0;
}

.btn-primary {
    background: #2563eb;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #1e40af;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.message {
    padding: 12px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
}

.message.success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.message.error {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 500;
}

.alert.success {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.alert.error {
    background-color: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.alert.info {
    background-color: #dbeafe;
    color: #1e40af;
    border: 1px solid #bfdbfe;
}
</style>