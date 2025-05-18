    let menuData = {};
    let cart = {};
    let currentOpenCategory = null;
    let recentOrders = JSON.parse(localStorage.getItem('recentOrders') || '[]');
    let currentFilter = 'all';

    function showCategory(category) {
        const menuContainer = document.getElementById('menu-container');
        // Toggle logic: if the same category is tapped, hide items
        if (currentOpenCategory === category) {
            menuContainer.innerHTML = '';
            menuContainer.classList.remove('menu-items-grid');
            currentOpenCategory = null;
            return;
        }
        currentOpenCategory = category;
        menuContainer.innerHTML = '';
        menuContainer.classList.add('menu-items-grid');

        const items = menuData[category] || [];
        if (items.length === 0) {
            menuContainer.innerHTML = '<p>No items available in this category.</p>';
            return;
        }

        items.forEach(item => {
            const stock = Number(item.Quantity) || 0;
            let isOutOfStock = stock <= 0;
            const showStock = stock <= 10;
            const div = document.createElement('div');
            div.className = 'menu-item' + (isOutOfStock ? ' out-of-stock' : '');
            div.innerHTML = `
                <h3>${item.Name}</h3>
                <p>Price: ₱${parseFloat(item.Price).toFixed(2)}</p>
                ${showStock ? `<p>Available: ${stock}</p>` : ''}
                <img src="${item.ImagePath}" alt="${item.Name}" style="max-width:100px;" onerror="this.onerror=null; this.src='../Image_source/default.jpg';">
                ${isOutOfStock ? '<span class="out-of-stock-label">Out of Stock</span>' : `<button class="add-to-cart-btn" data-id="${item.ItemID}" data-cat="${item.Category}">Add to Order</button>`}
            `;
            menuContainer.appendChild(div);
        });

        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const cat = this.getAttribute('data-cat');
                const id = this.getAttribute('data-id');
                const item = (menuData[cat] || []).find(i => i.ItemID === id);
                const stock = Number(item.Quantity) || 0;
                if (item && stock > 0) addToCart(item);
            });
        });
    }

    function addToCart(item) {
        const maxQty = Number(item.Quantity) || 0;
        if (maxQty <= 0) return;
        if (!cart[item.ItemID]) {
            cart[item.ItemID] = { ...item, quantity: 1 };
        } else if (cart[item.ItemID].quantity < maxQty) {
            cart[item.ItemID].quantity++;
        }
        updateCartModal();
        updateCartBadge();
        updateRecentOrders(item);
    }

    function removeFromCart(itemId) {
        if (cart[itemId]) {
            delete cart[itemId];
            updateCartModal();
            updateCartBadge();
        }
    }

    function updateCartBadge() {
        const badge = document.getElementById('cart-badge');
        const totalItems = Object.values(cart).reduce((sum, item) => sum + item.quantity, 0);
        badge.textContent = totalItems;
        
        // Add animation class if items are added
        if (totalItems > 0) {
            badge.classList.add('cart-badge-active');
        } else {
            badge.classList.remove('cart-badge-active');
        }
    }

    function updateCartModal() {
        const modalList = document.getElementById('modal-order-list');
        const totalPriceSpan = document.getElementById('modal-total-price');
        modalList.innerHTML = '';
        let total = 0;

        Object.values(cart).forEach(item => {
            const maxQty = Number(item.Quantity) || 0;
            const li = document.createElement('li');
            li.innerHTML = `
                <div class="cart-item-row">
                    <span class="cart-item-name">${item.Name}</span>
                    <div class="cart-qty-controls">
                        <button class="decrease-qty-btn" data-id="${item.ItemID}">-</button>
                        <span class="cart-qty-number">${item.quantity}</span>
                        <button class="increase-qty-btn" data-id="${item.ItemID}" ${item.quantity >= maxQty ? 'disabled' : ''}>+</button>
                    </div>
                    <span class="cart-item-price">₱${(item.Price * item.quantity).toFixed(2)}</span>
                    <button class="remove-from-cart-btn" data-id="${item.ItemID}">Remove</button>
                </div>
            `;
            modalList.appendChild(li);
            total += item.Price * item.quantity;
        });

        totalPriceSpan.textContent = total.toFixed(2);

        document.querySelectorAll('.remove-from-cart-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                removeFromCart(this.getAttribute('data-id'));
            });
        });
        document.querySelectorAll('.increase-qty-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                if (cart[id]) {
                    const maxQty = Number(cart[id].Quantity) || 0;
                    if (cart[id].quantity < maxQty) {
                        cart[id].quantity++;
                        updateCartModal();
                        updateCartBadge();
                    }
                }
            });
        });
        document.querySelectorAll('.decrease-qty-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                if (cart[id] && cart[id].quantity > 1) {
                    cart[id].quantity--;
                    updateCartModal();
                    updateCartBadge();
                }
            });
        });
    }

    function submitOrder() {
        if (Object.keys(cart).length === 0) {
            showNotification("Your cart is empty!", "error");
            return;
        }

        // Show confirmation dialog
        if (!confirm("Are you sure you want to submit this order?")) {
            return;
        }

        // Show loading state
        const submitButton = document.querySelector('#order-modal button');
        const originalButtonText = submitButton.textContent;
        submitButton.textContent = "Processing...";
        submitButton.disabled = true;

        const orderData = {
            orderDetails: {},
            totalPrice: 0,
            timestamp: new Date().toISOString()
        };

        // Validate and sanitize order data
        try {
            Object.values(cart).forEach(item => {
                if (!item.Name || !item.Price || !item.quantity) {
                    throw new Error("Invalid item data");
                }
                
                // Sanitize item data
                const sanitizedName = item.Name.replace(/[<>]/g, '');
                const sanitizedPrice = parseFloat(item.Price);
                const sanitizedQuantity = parseInt(item.quantity);

                if (isNaN(sanitizedPrice) || isNaN(sanitizedQuantity)) {
                    throw new Error("Invalid price or quantity");
                }

                orderData.orderDetails[sanitizedName] = {
                    quantity: sanitizedQuantity,
                    price: sanitizedPrice
                };
                orderData.totalPrice += sanitizedPrice * sanitizedQuantity;
            });

            // Add CSRF token if available
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const headers = {
                'Content-Type': 'application/json'
            };
            if (csrfToken) {
                headers['X-CSRF-Token'] = csrfToken;
            }

            fetch('../DASHBOARD/Receive Order/receiveOrder.php', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(orderData)
            })
            .then(async res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    // Hide the order modal
                    document.getElementById('order-modal').style.display = 'none';
                    
                    // Reset cart and update UI
                    cart = {};
                    updateCartModal();
                    updateCartBadge();
                    
                    // Show success notification
                    showNotification("Order placed successfully!", "success");
                    
                    // Show receipt
                    showReceipt(orderData, data.orderID);
                    
                    // Reload menu data to update stock
                    reloadMenuData();
                } else {
                    throw new Error(data.message || "Unknown error");
                }
            })
            .catch(err => {
                showNotification("Order failed: " + err.message, "error");
            })
            .finally(() => {
                // Reset button state
                submitButton.textContent = originalButtonText;
                submitButton.disabled = false;
            });
        } catch (error) {
            showNotification("Error processing order: " + error.message, "error");
            submitButton.textContent = originalButtonText;
            submitButton.disabled = false;
        }
    }

    function showReceipt(orderData, orderId) {
        // Create receipt modal if it doesn't exist
        let receiptModal = document.getElementById('receipt-modal');
        if (!receiptModal) {
            receiptModal = document.createElement('div');
            receiptModal.id = 'receipt-modal';
            receiptModal.style.display = 'none';
            receiptModal.style.position = 'fixed';
            receiptModal.style.zIndex = '1000';
            receiptModal.style.left = '0';
            receiptModal.style.top = '0';
            receiptModal.style.width = '100%';
            receiptModal.style.height = '100%';
            receiptModal.style.backgroundColor = 'rgba(0,0,0,0.5)';
            document.body.appendChild(receiptModal);
        }

        // Create receipt content
        const receiptContent = document.createElement('div');
        receiptContent.style.backgroundColor = 'white';
        receiptContent.style.position = 'absolute';
        receiptContent.style.top = '50%';
        receiptContent.style.left = '50%';
        receiptContent.style.transform = 'translate(-50%, -50%)';
        receiptContent.style.padding = '20px';
        receiptContent.style.width = '300px';
        receiptContent.style.borderRadius = '5px';
        receiptContent.style.boxShadow = '0 4px 24px rgba(0,0,0,0.18)';

        // Add receipt header
        const header = document.createElement('div');
        header.style.textAlign = 'center';
        header.style.marginBottom = '20px';
        header.innerHTML = `
            <h2 style="margin: 0; color: #004d00;">Puss'n Till Kiosk</h2>
            <p style="margin: 5px 0;">Receipt</p>
            <div style="
                background-color: #004d00;
                color: white;
                padding: 8px 15px;
                border-radius: 5px;
                margin: 10px auto;
                display: inline-block;
                font-size: 1.2em;
                font-weight: bold;
                letter-spacing: 1px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            ">
                Order #${orderId}
            </div>
            <p style="margin: 5px 0;">${new Date().toLocaleString()}</p>
            <hr style="border: 1px dashed #000;">
        `;
        receiptContent.appendChild(header);

        // Add order items
        const itemsList = document.createElement('div');
        itemsList.style.marginBottom = '20px';
        Object.entries(orderData.orderDetails).forEach(([name, details]) => {
            const itemDiv = document.createElement('div');
            itemDiv.style.display = 'flex';
            itemDiv.style.justifyContent = 'space-between';
            itemDiv.style.marginBottom = '5px';
            itemDiv.innerHTML = `
                <span>${name} x${details.quantity}</span>
                <span>₱${(details.price * details.quantity).toFixed(2)}</span>
            `;
            itemsList.appendChild(itemDiv);
        });
        receiptContent.appendChild(itemsList);

        // Add total
        const totalDiv = document.createElement('div');
        totalDiv.style.borderTop = '1px dashed #000';
        totalDiv.style.paddingTop = '10px';
        totalDiv.style.display = 'flex';
        totalDiv.style.justifyContent = 'space-between';
        totalDiv.style.fontWeight = 'bold';
        totalDiv.innerHTML = `
            <span>Total:</span>
            <span>₱${orderData.totalPrice.toFixed(2)}</span>
        `;
        receiptContent.appendChild(totalDiv);

        // Add thank you message
        const thankYou = document.createElement('div');
        thankYou.style.textAlign = 'center';
        thankYou.style.marginTop = '20px';
        thankYou.innerHTML = `
            <p style="margin: 5px 0;">Thank you for your order!</p>
            <p style="margin: 5px 0;">Please wait for your order number.</p>
            <div style="
                background-color: #f0f0f0;
                border: 2px solid #004d00;
                padding: 10px;
                border-radius: 5px;
                margin: 15px auto;
                max-width: 200px;
            ">
                <p style="
                    margin: 0;
                    color: #004d00;
                    font-size: 1.1em;
                    font-weight: bold;
                ">Your Order #${orderId}</p>
            </div>
        `;
        receiptContent.appendChild(thankYou);

        // Add close button
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = 'Close';
        closeBtn.style.display = 'block';
        closeBtn.style.margin = '20px auto 0';
        closeBtn.style.padding = '8px 20px';
        closeBtn.style.backgroundColor = '#004d00';
        closeBtn.style.color = 'white';
        closeBtn.style.border = 'none';
        closeBtn.style.borderRadius = '5px';
        closeBtn.style.cursor = 'pointer';
        closeBtn.onclick = function() {
            receiptModal.style.display = 'none';
        };
        receiptContent.appendChild(closeBtn);

        // Clear previous content and show modal
        receiptModal.innerHTML = '';
        receiptModal.appendChild(receiptContent);
        receiptModal.style.display = 'block';
    }

    // After a successful order, reload menu data to reflect new stock
    function reloadMenuData() {
        fetch("./DATABASE/getMenuItems.php")
            .then(res => res.json())
            .then(data => {
                if (data.success && data.categories) {
                    menuData = data.categories;
                    // If a category is open, refresh it
                    if (currentOpenCategory) showCategory(currentOpenCategory);
                }
            });
    }

    // Add notification system
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        // Style the notification
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.padding = '15px 25px';
        notification.style.borderRadius = '5px';
        notification.style.color = 'white';
        notification.style.zIndex = '1000';
        notification.style.animation = 'slideIn 0.5s ease-out';
        
        // Set background color based on type
        switch(type) {
            case 'success':
                notification.style.backgroundColor = '#4CAF50';
                break;
            case 'error':
                notification.style.backgroundColor = '#f44336';
                break;
            default:
                notification.style.backgroundColor = '#2196F3';
        }
        
        document.body.appendChild(notification);
        
        // Remove notification after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.5s ease-out';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 500);
        }, 3000);
    }

    // Add CSS for notifications
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);

    function updateRecentOrders(item) {
        const index = recentOrders.findIndex(order => order.ItemID === item.ItemID);
        if (index !== -1) {
            recentOrders.splice(index, 1);
        }
        recentOrders.unshift(item);
        if (recentOrders.length > 5) {
            recentOrders.pop();
        }
        localStorage.setItem('recentOrders', JSON.stringify(recentOrders));
    }

    function filterMenuItems() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const menuContainer = document.getElementById('menu-container');
        menuContainer.innerHTML = '';
        menuContainer.classList.add('menu-items-grid');

        let filteredItems = [];
        Object.values(menuData).flat().forEach(item => {
            const matchesSearch = item.Name.toLowerCase().includes(searchTerm);
            const matchesFilter = 
                currentFilter === 'all' ? true :
                currentFilter === 'available' ? Number(item.Quantity) > 0 :
                currentFilter === 'recent' ? recentOrders.some(recent => recent.ItemID === item.ItemID) :
                true;

            if (matchesSearch && matchesFilter) {
                filteredItems.push(item);
            }
        });

        if (filteredItems.length === 0) {
            menuContainer.innerHTML = '<p>No items found matching your criteria.</p>';
            return;
        }

        filteredItems.forEach(item => {
            const stock = Number(item.Quantity) || 0;
            let isOutOfStock = stock <= 0;
            const showStock = stock <= 10;
            
            const div = document.createElement('div');
            div.className = 'menu-item' + (isOutOfStock ? ' out-of-stock' : '');
            div.innerHTML = `
                <h3>${item.Name}</h3>
                <p>Price: ₱${parseFloat(item.Price).toFixed(2)}</p>
                ${showStock ? `<p>Available: ${stock}</p>` : ''}
                <img src="${item.ImagePath}" alt="${item.Name}" style="max-width:100px;" onerror="this.onerror=null; this.src='../Image_source/default.jpg';">
                ${isOutOfStock ? '<span class="out-of-stock-label">Out of Stock</span>' : `<button class="add-to-cart-btn" data-id="${item.ItemID}" data-cat="${item.Category}">Add to Order</button>`}
            `;
            menuContainer.appendChild(div);
        });

        // Add event listeners to the new buttons
        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const cat = this.getAttribute('data-cat');
                const id = this.getAttribute('data-id');
                const item = (menuData[cat] || []).find(i => i.ItemID === id);
                if (item) {
                    addToCart(item);
                    updateRecentOrders(item);
                }
            });
        });
    }

    document.addEventListener("DOMContentLoaded", () => {
        console.log('DOM Content Loaded');
        
        // Add home button functionality first
        const homeButton = document.getElementById('homeButton');
        if (homeButton) {
            console.log('Home button found, adding click listener');
            homeButton.addEventListener('click', function() {
                console.log('Home button clicked');
                window.location.href = '../1stPage.php';
            });
        } else {
            console.log('Home button not found');
        }

        // Fetch menu data
        console.log('Fetching menu data...');
        fetch("./DATABASE/getMenuItems.php")
            .then(res => {
                console.log('Response status:', res.status);
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.text().then(text => {
                    console.log('Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        throw new Error('Invalid JSON response');
                    }
                });
            })
            .then(data => {
                console.log('Parsed data:', data);
                if (data.success && data.categories) {
                    menuData = data.categories;
                    console.log('Menu data loaded:', menuData);

                    // Dynamically create circular category images in a specific order
                    const desiredOrder = ['Toppings', 'Viands', 'Add-Ons', 'Drinks'];
                    const categoryButtons = document.getElementById('category-buttons');
                    categoryButtons.innerHTML = '';
                    desiredOrder.forEach(desiredCat => {
                        // Find the actual category key that matches (case-insensitive, ignore dashes)
                        const actualCat = Object.keys(menuData).find(cat => cat.replace(/[- ]/g, '').toLowerCase() === desiredCat.replace(/[- ]/g, '').toLowerCase());
                        if (!actualCat) return;
                        let imgSrc = '';
                        if (actualCat.toLowerCase().includes('topping')) imgSrc = '../Image_source/toppings.jpg';
                        else if (actualCat.toLowerCase().includes('viand')) imgSrc = '../Image_source/viands.jpg';
                        else if (actualCat.toLowerCase().includes('add')) imgSrc = '../Image_source/addons.jpg';
                        else if (actualCat.toLowerCase().includes('drink')) imgSrc = '../Image_source/drinks.jpg';
                        else imgSrc = '../Image_source/default.jpg';

                        const img = document.createElement('img');
                        img.src = imgSrc;
                        img.setAttribute('data-category', actualCat);
                        img.alt = actualCat;
                        img.className = '';
                        img.style.width = '75px';
                        img.style.height = '75px';
                        img.style.borderRadius = '50%';
                        img.style.objectFit = 'cover';
                        img.style.border = '4px solid #004d00';
                        img.style.padding = '6px';
                        img.style.backgroundColor = 'white';
                        img.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
                        img.style.cursor = 'pointer';
                        img.style.margin = '0 10px';
                        img.addEventListener('click', function() {
                            showCategory(actualCat);
                        });
                        categoryButtons.appendChild(img);
                    });

                    // Do NOT show all items by default; keep menu hidden until a category is tapped
                    document.getElementById('menu-container').innerHTML = '';
                } else {
                    console.error('Menu fetch failed:', data.message);
                    document.getElementById('menu-container').innerHTML = '<p>Error loading menu: ' + (data.message || 'Unknown error') + '</p>';
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                document.getElementById('menu-container').innerHTML = '<p>Error loading menu. Please try again later.</p>';
            });

        // Modal logic
        const modal = document.getElementById('order-modal');
        const viewOrderBtn = document.getElementById('viewErrorButton');
        const closeBtn = document.querySelector('.close-btn');

        viewOrderBtn.addEventListener('click', () => modal.style.display = 'block');
        closeBtn.addEventListener('click', () => modal.style.display = 'none');
        window.onclick = function(e) {
            if (e.target == modal) modal.style.display = 'none';
        };

        updateCartModal();

        // Add search and filter functionality
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        const filterButtons = document.querySelectorAll('.filter-btn[data-filter]');

        searchInput.addEventListener('input', filterMenuItems);
        searchBtn.addEventListener('click', filterMenuItems);

        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                filterButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.getAttribute('data-filter');
                filterMenuItems();
            });
        });

        document.querySelectorAll('.menu-item h3').forEach(e => {
            e.innerText = 'TEST NAME';
            e.style.color = 'red';
            e.style.fontSize = '32px';
            e.style.background = 'yellow';
            e.style.display = 'block';
            e.style.position = 'relative';
            e.style.zIndex = 9999;
        });
    });
