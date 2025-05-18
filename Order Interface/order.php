<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="<?php echo bin2hex(random_bytes(32)); ?>" />
    <title>Kiosk Menu</title>
    <link rel="stylesheet" href="order_style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        :root {
            --primary-color: #004d00;
            --secondary-color: #006600;
            --accent-color: #f1c40f;
            --text-color: #333;
            --background-color: #004d00;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--background-color);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            padding: 20px;
            background-image: url(../Image_source/background_pastil.png);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .kiosk {
            width: 100%;
            max-width: 900px;
            background: rgb(12, 61, 19);
            border-radius: 15px;
            box-shadow: 0 10px 30px #f1c40f;
            padding: 30px;
            margin: 0 auto;
            color: white;
        }

        .top-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--accent-color);
        }

        .top-section h2 {
            font-size: 2em;
            color: white;
        }

        .button-group {
            display: flex;
            gap: 15px;
        }

        .cart-button {
            background: var(--accent-color);
            color: var(--text-color);
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 1.1em;
            font-weight: bold;
            position: relative;
            overflow: hidden;
        }

        .cart-button:hover {
            background: #573901;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 0 10px #e4f30c, 0 0 40px #b1ba33, 0 0 80px #ff8c00;
        }

        .cart-button:active {
            transform: translateY(1px);
        }

        .cart-button::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease-out, height 0.6s ease-out;
        }

        .cart-button:hover::after {
            width: 300%;
            height: 300%;
        }

        .cart-badge {
            background: var(--primary-color);
            color: white;
            padding: 4px 8px;
            border-radius: 50%;
            font-weight: bold;
        }

        #homeButton {
            background: var(--accent-color);
            color: var(--text-color);
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2em;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        #homeButton:hover {
            background: #573901;
            color: white;
            transform: scale(1.1) rotate(360deg);
            box-shadow: 0 0 15px rgba(241, 196, 15, 0.5);
        }

        #homeButton:active {
            transform: scale(0.95);
        }

        #homeButton::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease-out, height 0.6s ease-out;
        }

        #homeButton:hover::before {
            width: 200%;
            height: 200%;
        }

        .search-filter-section {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: var(--card-shadow);
        }

        .search-box {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .search-box input {
            flex: 1;
            padding: 15px;
            border: 2px solid var(--accent-color);
            border-radius: 8px;
            font-size: 1.1em;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(241, 196, 15, 0.3);
        }

        .filter-options {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .filter-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            background: var(--accent-color);
            color: var(--text-color);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 1em;
            font-weight: bold;
            min-width: 120px;
            position: relative;
            overflow: hidden;
        }

        .filter-btn:hover {
            background: #573901;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(241, 196, 15, 0.3);
        }

        .filter-btn:active {
            transform: translateY(1px);
        }

        .filter-btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease-out, height 0.6s ease-out;
        }

        .filter-btn:hover::after {
            width: 300%;
            height: 300%;
        }

        .filter-btn.active {
            background: #573901;
            color: white;
            box-shadow: var(--hover-shadow);
        }

        .category-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
            flex-wrap: wrap;
        }

        .category-buttons img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--accent-color);
            padding: 8px;
            background: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .category-buttons img:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 8px 25px rgba(241, 196, 15, 0.3);
        }

        .category-buttons img:active {
            transform: scale(0.95);
        }

        .category-buttons img::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(241, 196, 15, 0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease-out;
        }

        .category-buttons img:hover::after {
            transform: translateX(100%);
        }

        .menu-items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 25px;
            padding: 20px 0;
        }

        .menu-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            color: white;
            height: 380px;
            overflow: hidden;
        }

        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(241, 196, 15, 0.2);
            background: rgba(255, 255, 255, 0.15);
        }

        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(241, 196, 15, 0.1), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease-out;
        }

        .menu-item:hover::before {
            transform: translateX(100%);
        }

        .menu-item h3 {
            color: white;
            margin: 10px 0;
            font-size: 1.2em;
            height: 2.8em;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .menu-item p {
            color: #f1c40f;
            margin: 5px 0;
            height: 1.5em;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .menu-item img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin: 5px 0;
            border: 3px solid var(--accent-color);
            flex-shrink: 0;
        }

        .add-to-cart-btn {
            background: var(--accent-color);
            color: var(--text-color);
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: auto;
            width: 100%;
            font-weight: bold;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1em;
            position: relative;
            overflow: hidden;
        }

        .add-to-cart-btn:hover {
            background: #573901;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(241, 196, 15, 0.3);
        }

        .add-to-cart-btn:active {
            transform: translateY(1px);
        }

        .add-to-cart-btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease-out, height 0.6s ease-out;
        }

        .add-to-cart-btn:hover::after {
            width: 300%;
            height: 300%;
        }

        .favorite-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            color: #ccc;
            cursor: pointer;
            font-size: 1.5em;
            transition: all 0.3s ease;
            z-index: 1;
        }

        .favorite-btn:hover {
            transform: scale(1.2);
        }

        .favorite-btn.active {
            color: var(--accent-color);
        }

        .out-of-stock-label {
            background: #ff4444;
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            margin-top: auto;
            font-weight: bold;
            width: 100%;
            text-align: center;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
        }

        .modal-content {
            background: rgb(12, 61, 19);
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px #f1c40f;
            position: relative;
            color: white;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.5em;
            cursor: pointer;
            color: var(--accent-color);
            transition: all 0.3s ease;
        }

        .close-btn:hover {
            color: white;
            transform: scale(1.1);
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .kiosk {
                padding: 15px;
            }

            .top-section {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .button-group {
                width: 100%;
                justify-content: center;
            }

            .search-box {
                flex-direction: column;
            }

            .search-box input {
                width: 100%;
            }

            .filter-options {
                justify-content: center;
            }

            .menu-items-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 15px;
            }

            .category-buttons img {
                width: 80px;
                height: 80px;
            }

            .modal-content {
                width: 95%;
                margin: 20px auto;
                padding: 20px;
            }
        }

        /* Loading Spinner */
        .loading {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(12, 61, 19, 0.9);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px #f1c40f;
            z-index: 1000;
        }

        .loading::after {
            content: '';
            display: block;
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--accent-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="kiosk">
        <div class="top-section">
            <h2>Welcome, <br /><span style="color: var(--accent-color); font-weight: bold;">OUR DEAR CUSTOMER</span></h2>
            <div class="button-group">
                <div style="position: relative; display: inline-block;">
                    <button id="viewErrorButton" class="cart-button">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-icon">View Order</span>
                    </button>
                    <span class="cart-badge" id="cart-badge">0</span>
                </div>
                <button id="homeButton" aria-label="Return to home">
                    <i class="fas fa-home"></i>
                </button>
            </div>
        </div>

        <div class="search-filter-section">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search menu items..." aria-label="Search menu items">
                <button class="filter-btn" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div class="filter-options">
                <button class="filter-btn" data-filter="all">
                    <i class="fas fa-th-list"></i> All Items
                </button>
                <button class="filter-btn" data-filter="available">
                    <i class="fas fa-check-circle"></i> Available Only
                </button>
            </div>
        </div>

        <p style="text-align: center; margin: 20px 0; font-size: 1.2em; color: var(--accent-color);">
            Choose category to explore the menu
        </p>
        <div class="category-buttons" id="category-buttons" role="navigation" aria-label="Menu categories"></div>
        <div class="menu" id="menu-container" role="main">
            <!-- Items will be displayed here dynamically -->
        </div>
        <div id="order-modal" class="modal">
            <div class="modal-content">
                <span class="close-btn" role="button" aria-label="Close modal">×</span>
                <h2 style="color: var(--accent-color); margin-bottom: 20px;">View Order</h2>
                <ul id="modal-order-list" style="list-style: none; margin-bottom: 20px;"></ul>
                <p style="font-size: 1.2em; font-weight: bold; margin: 20px 0; color: var(--accent-color);">
                    Total: ₱<span id="modal-total-price">0.00</span>
                </p>
                <button onclick="submitOrder()" id="submit-order-btn" class="cart-button" style="width: 100%;">
                    <i class="fas fa-check"></i> Review + Pay For Order
                </button>
            </div>
        </div>
    </div>
    
    <div class="loading" id="loading-spinner"></div>
    
    <script src="order_script.js"></script>
</body>
</html>
