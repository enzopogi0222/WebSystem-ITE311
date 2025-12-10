<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ITE311 Auth System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center">
                        <h4><i class="fas fa-sign-in-alt"></i> Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger">
                                <?= session()->getFlashdata('error') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success">
                                <?= session()->getFlashdata('success') ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= base_url('/login') ?>">
                            <?= csrf_field() ?>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" 
                                           class="form-control <?= isset($validation) && $validation->hasError('email') ? 'is-invalid' : '' ?>" 
                                           id="email" 
                                           name="email" 
                                           value="<?= old('email') ?>" 
                                           pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"
                                           title="Email must contain @ and no special characters except @, ., _, %, +, -"
                                           required>
                                    <?php if (isset($validation) && $validation->hasError('email')): ?>
                                        <div class="invalid-feedback">
                                            <?= $validation->getError('email') ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="invalid-feedback" id="email-error" style="display: none;">
                                        Email must contain @ and no special characters except @, ., _, %, +, -
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" 
                                           class="form-control <?= isset($validation) && $validation->hasError('password') ? 'is-invalid' : '' ?>" 
                                           id="password" 
                                           name="password" 
                                           pattern="[a-zA-Z0-9]+"
                                           title="Password must contain only letters and numbers. No special characters allowed."
                                           required>
                                    <?php if (isset($validation) && $validation->hasError('password')): ?>
                                        <div class="invalid-feedback">
                                            <?= $validation->getError('password') ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="invalid-feedback" id="password-error" style="display: none;">
                                        Password must contain only letters and numbers. No special characters allowed.
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </button>
                            </div>
                                    </form>
                            <div class="text-center mt-3">
                            <a href="<?= base_url('/') ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Frontend validation for email and password
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const form = document.querySelector('form');

            // Email validation
            emailInput.addEventListener('input', function() {
                const email = this.value;
                const emailError = document.getElementById('email-error');
                
                if (email && !email.includes('@')) {
                    this.setCustomValidity('Email must contain @ symbol');
                    emailError.style.display = 'block';
                    this.classList.add('is-invalid');
                } else if (email && !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)) {
                    this.setCustomValidity('Invalid email format. No special characters except @, ., _, %, +, -');
                    emailError.style.display = 'block';
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    emailError.style.display = 'none';
                    this.classList.remove('is-invalid');
                }
            });

            // Password validation (alphanumeric only)
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const passwordError = document.getElementById('password-error');
                
                if (password && !/^[a-zA-Z0-9]+$/.test(password)) {
                    this.setCustomValidity('Password must contain only letters and numbers. No special characters allowed.');
                    passwordError.style.display = 'block';
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    passwordError.style.display = 'none';
                    this.classList.remove('is-invalid');
                }
            });

            // Form submission validation
            form.addEventListener('submit', function(e) {
                const email = emailInput.value;
                const password = passwordInput.value;

                // Validate email
                if (!email.includes('@') || !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)) {
                    e.preventDefault();
                    emailInput.focus();
                    return false;
                }

                // Validate password
                if (!/^[a-zA-Z0-9]+$/.test(password)) {
                    e.preventDefault();
                    passwordInput.focus();
                    return false;
                }
            });
        });
    </script>
</body>
</html>