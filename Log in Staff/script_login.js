document.addEventListener("DOMContentLoaded", function() {
    const signUpButton = document.getElementById('signUpButton');
    const signInButton = document.getElementById('signInButton');
    const signInForm = document.getElementById('signInForm');
    const signUpForm = document.getElementById('signUpForm');

    if (signUpButton) {
        signUpButton.addEventListener('click', function() {
            console.log("Sign Up button clicked!");
            signInForm.style.display = "none";
            signUpForm.style.display = "flex";
        });
    }

    if (signInButton) {
        signInButton.addEventListener('click', function() {
            signInForm.style.display = "flex";
            signUpForm.style.display = "none";
        });
    }

    const returnButton = document.getElementById('returnButton');
    if (returnButton) {
        returnButton.addEventListener('click', function() {
            window.location.href = "../1stPage.php";
        });
    }
});
