<?php session_start() ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Selection</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap');
        *{
            margin:0; 
            padding:0;
            box-sizing:border-box;
            font-family:"poppins", sans-serif;
        }   
        body {
            font-family: Arial, sans-serif;
            background-image: url(Image_source/background_pastil.png);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            text-align: center;
            margin: 0;
            display: flex;
            flex-direction: flex-start;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding-top: 50px;
        }

        .logo {
            width: 300px; 
            height: auto;
            display: block;
            margin-bottom: 50px;
            transform: rotate(0deg); 
            
        }

        .container {
            background: rgb(12, 61, 19);
            width: 400px;
            padding: 20px;
            border-radius: 10px;
            box-shadow:0 10px 30px #f1c40f;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            top: -40px;
        }

        .title {
            color: #f1c40f;
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .button-group {
            width: 100%;
        }

        .button-group button {
            font-size: 16px;
            padding: 10px;
            margin: 8px 0;
            border-radius: 20px;
            border: solid 2px black;
            width: 90%;
            background: #fddc57fa;
            color: black;
            cursor: pointer;
            transition: 0.3s ease-in-out;
        }

        .button-group button:hover {
            background: #573901;
            color: white;
            box-shadow: 0 0 10px #e4f30c, 0 0 40px #b1ba33, 0 0 80px #ff8c00;
        }
    </style>
</head>
<body>
    <img class="logo" src="Image_source/puss’n till_logo.png" alt="logo">
    <div class="container">
        <div class="header">
            <h1 class="title">Welcome to Puss 'n Till</h1>
        </div>
        <div class="button-group">
            <button onclick="window.location.href='Order Interface/order.php'">MAKE AN ORDER</button>
            <button onclick="window.location.href='Log in Staff/login_index.php'">STAFF LOG IN</button>
            <button onclick="exitApp()">EXIT</button>
        </div>
    </div>
    <script>
        function exitApp() {
            alert("Exiting application...");
            window.close();
        }
    </script>
</body>
</html>
