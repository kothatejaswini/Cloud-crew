// Utility function to handle form submissions
function handleFormSubmission(formId, apiUrl, successRedirectUrl, successMessage) {
    document.getElementById(formId)?.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Retrieve and trim input values
        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value.trim();
        });

        // Basic validation for specific forms
        if (data.password && data.confirmPassword && data.password !== data.confirmPassword) {
            alert("Passwords do not match!");
            return;
        }

        // Prepare data for submission
        const jsonData = JSON.stringify(data);

        // Send data to the backend API
        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: jsonData
        })
        .then(response => response.json())
        .then(responseData => {
            alert(responseData.message);
            if (responseData.message === successMessage) {
                // Redirect upon successful registration or login
                if (successRedirectUrl) {
                    window.location.href = successRedirectUrl;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred.");
        });
    });
}

// Handle registration form submission
handleFormSubmission(
    'registrationForm',
    'https://cloudcrew-1275067821.ap-south-1.elb.amazonaws.com/api.php?action=register',
    'login.html',
    'User registered successfully'
);

// Handle login form submission
handleFormSubmission(
    'loginForm',
    'https://cloudcrew-1275067821.ap-south-1.elb.amazonaws.com/api.php?action=login',
    'products.html',
    'Login successful'
);
