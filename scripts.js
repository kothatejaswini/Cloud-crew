// Utility function to handle form submissions
function handleFormSubmission(formId, apiUrl, successRedirectUrl, successMessage) {
    const form = document.getElementById(formId);
    if (!form) {
        console.error(`Form with ID ${formId} not found.`);
        return;
    }

    form.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Retrieve and trim input values
        const formData = new FormData(form);
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
            handleResponse(responseData, successMessage, successRedirectUrl);
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred.");
        });
    });
}

// Utility function to handle response
function handleResponse(responseData, successMessage, successRedirectUrl) {
    alert(responseData.message);
    if (responseData.message === successMessage) {
        // Redirect upon successful registration or login
        if (successRedirectUrl) {
            window.location.href = successRedirectUrl;
        }
    }
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
