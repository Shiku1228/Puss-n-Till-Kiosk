document.addEventListener("DOMContentLoaded", function() {
    const signUpButton = document.getElementById('signUpButton');
    const signInButton = document.getElementById('signInButton');
    const signInForm = document.getElementById('signInForm');
    const signUpForm = document.getElementById('signUpForm');

    signUpButton.addEventListener('click', function() {
        console.log("Sign Up button clicked!");
        signInForm.style.display = "none";
        signUpForm.style.display = "flex";
    });

    signInButton.addEventListener('click', function() {
        signInForm.style.display = "flex";
        signUpForm.style.display = "none";
    });
});
