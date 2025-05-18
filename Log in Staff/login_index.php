<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register & Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="style_login.css">
</head>
<body>
   <div class="container" id="signUpForm" style="display:none;">
<h1 class="form-little">Register</h1>
<form method="post" action="register.php">
    <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="fName" id="fName" placeholder="First Name" required>
        <label for="fname">First Name</label>
    </div>
    <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="lName" id="lName" placeholder="Last Name" required>
        <label for="lname">Last Name</label>
    </div>
    <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="StaffID" id="StaffID" placeholder="Staff ID" required>
        <label for="StaffID">Staff ID</label>
    </div>
    <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="Password" id="Password" placeholder="Password" required>
        <label for="Password">Password</label>
    </div>
    <input type="submit" class="btn" value="Sign Up" name="signUp">
</form> 
<p class="or">
--------or--------    
</p>
<div id="hidden-section" style="display: none;">
    <div class="icons">
     <i class="fab fa-google"></i>
     <i class="fab fa-facebook"></i>
    </div>
</div>
<div class="links">
    <p>Already member of our team?</p>
    <button id="signInButton">Sign In</button>
</div>
   </div>

   <div class="container" id="signInForm">
        <h1 class="form-little">Staff Log In</h1>
        <form method="post" action="login.php">
        
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="StaffID" id="StaffID" placeholder="Staff ID" required>
                <label for="StaffID">Staff ID</label>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="Password" name="Password" id="Password" placeholder="Password" required>
                <label for="Password">Password</label>
            </div>
            <p class="recover">
                <!-- <a href="#">Recover Password</a> -->
            </p>
            <input type="submit" class="btn" value="Sign In" name="signIn">
        </form> 
        <button id="returnButton" style="margin-top: 15px; padding: 10px 20px; font-size: 16px; cursor: pointer;">Return</button>
        <p class="or">
        <!-- --------or--------       -->
        </p>
        <div id="hidden-section" style="display: none;">
            <div class="icons">
             <i class="fab fa-google"></i>
             <i class="fab fa-facebook"></i>
            </div>
        </div>
        <!---<div class="links"
            <p>Not yet member of our team?</p>
            <button id="signUpButton">Sign Up</button>
        </div>--->
    </div>
    <script src="script_login.js"></script>
</body>
</html>