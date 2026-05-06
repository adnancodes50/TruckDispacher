<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - TruckDispatcher</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 100%;
            padding: 40px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #1f2937;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #6b7280;
            font-size: 14px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert.error {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }
        
        .alert.success {
            background: #efe;
            color: #3c3;
            border-left: 4px solid #3c3;
        }
        
        .alert.info {
            background: #eef;
            color: #33c;
            border-left: 4px solid #33c;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            color: #374151;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .loader {
            display: none;
            text-align: center;
            margin-top: 20px;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .success-message {
            text-align: center;
            color: #10b981;
        }
        
        .success-message svg {
            width: 60px;
            height: 60px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Reset Password</h1>
            <p>Enter your new password below</p>
        </div>
        
        <div id="alerts"></div>
        
        <form id="resetForm" style="display: none;">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required readonly>
            </div>
            
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" placeholder="At least 6 characters" required>
            </div>
            
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm your password" required>
            </div>
            
            <button type="submit">Reset Password</button>
            
            <div class="loader" id="loader">
                <div class="spinner"></div>
                <p style="margin-top: 10px; color: #6b7280;">Resetting password...</p>
            </div>
        </form>
        
        <div id="successMessage" style="display: none;">
            <div class="success-message">
                <h2 style="color: #10b981; margin-bottom: 20px;">✅ Password Reset Successfully!</h2>
                <p style="color: #6b7280; margin-bottom: 20px;">Your password has been reset successfully.</p>
                <p style="color: #6b7280;">You can now log in with your new password.</p>
                <button onclick="window.location.href='https://app.truckconnect.com/login'" style="margin-top: 20px;">Go to Login</button>
            </div>
        </div>
    </div>
    
    <script>
        // Get URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const email = urlParams.get('email');
        const token = urlParams.get('token');
        
        // Validate token on page load
        async function validateToken() {
            try {
                const response = await fetch(`/api/reset-password?email=${encodeURIComponent(email)}&token=${encodeURIComponent(token)}`);
                const data = await response.json();
                
                if (data.status) {
                    // Token is valid, show form
                    document.getElementById('email').value = email;
                    document.getElementById('resetForm').style.display = 'block';
                    showAlert('info', '✅ Token validated. Enter your new password below.');
                } else {
                    // Token is invalid
                    showAlert('error', '❌ ' + data.message);
                }
            } catch (error) {
                showAlert('error', '❌ Error validating token: ' + error.message);
            }
        }
        
        // Show alert message
        function showAlert(type, message) {
            const alertsDiv = document.getElementById('alerts');
            alertsDiv.innerHTML = `<div class="alert ${type}">${message}</div>`;
        }
        
        // Handle form submission
        document.getElementById('resetForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const passwordConfirmation = document.getElementById('password_confirmation').value;
            
            // Validate passwords match
            if (password !== passwordConfirmation) {
                showAlert('error', '❌ Passwords do not match');
                return;
            }
            
            // Validate password length
            if (password.length < 6) {
                showAlert('error', '❌ Password must be at least 6 characters');
                return;
            }
            
            // Show loader
            document.getElementById('loader').style.display = 'block';
            document.querySelector('button[type="submit"]').disabled = true;
            
            try {
                const response = await fetch('/api/reset-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        token: token,
                        password: password,
                        password_confirmation: passwordConfirmation
                    })
                });
                
                const data = await response.json();
                
                if (data.status) {
                    // Success!
                    document.getElementById('resetForm').style.display = 'none';
                    document.getElementById('successMessage').style.display = 'block';
                    document.getElementById('alerts').innerHTML = '';
                } else {
                    // Error
                    showAlert('error', '❌ ' + data.message);
                }
            } catch (error) {
                showAlert('error', '❌ Error: ' + error.message);
            } finally {
                // Hide loader
                document.getElementById('loader').style.display = 'none';
                document.querySelector('button[type="submit"]').disabled = false;
            }
        });
        
        // Validate on page load
        window.addEventListener('load', validateToken);
    </script>
</body>
</html>
