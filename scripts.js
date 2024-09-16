// Handle registration form submission
document.getElementById('registrationForm')?.addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission

    // Retrieve and trim input values
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    // Basic validation
    if (!username || !email || !password || !confirmPassword) {
        alert("All fields are required!");
        return;
    }

    if (password !== confirmPassword) {
        alert("Passwords do not match!");
        return;
    }

    // Prepare data for submission
    const data = {
        username: username,
        email: email,
        password: password
    };

    // Send data to the backend API
    fetch('http://cloudcrew-1275067821.ap-south-1.elb.amazonaws.com/api.php?action=register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.message === "User registered successfully") {
            // Redirect to login page
            window.location.href = "login.html";
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("An error occurred during registration.");
    });
});

// Handle login form submission
document.getElementById('loginForm')?.addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission

    const email = document.getElementById('loginEmail').value.trim(); // Use the correct ID
    const password = document.getElementById('loginPassword').value; // Use the correct ID

    // Basic validation
    if (!email || !password) {
        alert("Email and password are required!");
        return;
    }

    // Prepare data for submission
    const data = {
        username: email,
        password: password
    };

    // Send data to the backend API
    fetch('http://cloudcrew-1275067821.ap-south-1.elb.amazonaws.com/api.php?action=login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.message === "Login successful") {
            // Redirect to the products page
            window.location.href = "products.html";
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("An error occurred during login.");
    });
});
