<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk Menu</title>
    <link rel="stylesheet" href="order_style.css">
    <script defer src="order_script.js"></script>
</head>
<body>

    <div class="kiosk">
        <div class="top-section">
            <h2>Welcome, <br><span style="color: #008000; font-weight: bold;">OUR DEAR CUSTOMER</span></h2>
            <button id="">View Order</button>
            <button>🏠</button>
        </div> 

        <p>Choose category to explore the menu</p>
        <div class="category-buttons">
            <img src="../Image_source/toppings.jpg" onclick="showCategory('toppings')" alt="Toppings" width="50">
            <img src="../Image_source/viands.jpg" onclick="showCategory('viands')" alt="Viands" width="50">
            <img src="../Image_source/addons.jpg" onclick="showCategory('addons')" alt="Add-ons" width="50">
            <img src="../Image_source/drinks.jpg" onclick="showCategory('drinks')" alt="Drinks" width="50">
        </div>
        <div class="menu" id="menu-container">
            <!-- Items will be displayed here dynamically -->
        </div>
        <div id="order-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">X</span>
            <h2 style="color: black;">View Order</h2>
            <ul id="modal-order-list"></ul>
            <p>Total: ₱<span id="modal-total-price">0.00</span></p>
            <button onclick="submitOrder()">Review + Pay For Order</button>
        </div>
    </div>
    <script src="Order Interface/order_script.js"></script>
</body>
</html>
