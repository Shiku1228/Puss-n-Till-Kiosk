document.addEventListener("DOMContentLoaded", () => {
    const ordersList = document.querySelector('.orders-list');
    let isOrderDetailsOpen = false;
    const STACK_BOTTOM_OFFSET = 100; // px, space below header
    const STACK_GAP = 2; // px, gap between cards (shows even less of the bottom)

    function fetchOrders() {
        if (isOrderDetailsOpen) return;

        fetch('fetchOrders.php')
            .then(response => response.json())
            .then(orders => {
                ordersList.innerHTML = '<h2 style="margin-bottom: 60px;">Current Order Queue</h2>';
                
                if (Array.isArray(orders)) {
                    if (orders.length === 0) {
                        ordersList.innerHTML += '<p>No pending orders</p>';
                        return;
                    }
                    
                    orders.slice().reverse().forEach((order, index, arr) => {
                        const orderDetails = JSON.parse(order.OrderDetails);
                        const orderElement = document.createElement('div');
                        orderElement.className = 'order-card';
                        // Center all cards horizontally
                        orderElement.style.left = '50%';
                        orderElement.style.transform = 'translateX(-50%)';
                        // Stack: top card at top: 0, others offset downward
                        orderElement.style.top = `${(arr.length - index - 1) * STACK_GAP}px`;
                        orderElement.style.zIndex = `${index + 1}`;
                        // Attach click event to the top card (front)
                        if (index === arr.length - 1) {
                            orderElement.classList.add('top-card');
                            orderElement.addEventListener('click', () => openOrderDetails(orderElement, order));
                        }
                        
                        orderElement.innerHTML = `
                            <div class="order-header">
                                <h3>Order #${order.OrderID}</h3>
                                <span class="order-time">${new Date(order.orderTime).toLocaleString()}</span>
                            </div>
                            <div class="order-details">
                                <h4>Items:</h4>
                                <ul>
                                    ${
                                        Object.entries(orderDetails)
                                            .slice(0, 2)
                                            .map(([item, details]) => `
                                                <li>${item} x ${details.quantity} - ₱${(details.price * details.quantity).toFixed(2)}</li>
                                            `).join('')
                                    }
                                    ${
                                        Object.entries(orderDetails).length > 2
                                            ? `<li style="color:#888;">+ ${Object.entries(orderDetails).length - 2} more</li>`
                                            : ''
                                    }
                                </ul>
                                <p class="total">Total: ₱${order.totalPrice}</p>
                                <p class="staff-name">Staff: ${order.staffName}</p>
                            </div>
                        `;

                        ordersList.appendChild(orderElement);
                    });
                } else if (orders && orders.error) {
                    alert('Error: ' + orders.error);
                    ordersList.innerHTML += `<p style='color:red;'>${orders.error}</p>`;
                } else {
                    ordersList.innerHTML += '<p>Error loading orders. Please try again.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching orders:', error);
                document.querySelector('.orders-list').innerHTML = '<p>Error loading orders. Please try again.</p>';
            });
    }

    function openOrderDetails(card, order) {
        isOrderDetailsOpen = true;

        const overlay = document.createElement('div');
        overlay.className = 'overlay';
        document.body.appendChild(overlay);

        // Create a new modal element instead of reusing the card
        const modal = document.createElement('div');
        modal.className = 'enlarged-card';
        modal.innerHTML = `
            <button class="close-modal" aria-label="Close">&times;</button>
            <div class="order-details">
                <h3>Order #${order.OrderID} Details</h3>
                <p><strong>Time:</strong> ${new Date(order.orderTime).toLocaleString()}</p>
                <p><strong>Total:</strong> ₱${parseFloat(order.totalPrice).toFixed(2)}</p>
                <p><strong>Payment Method:</strong> ${order.payment_method || 'Cash'}</p>
                <p><strong>Staff:</strong> ${order.staffName}</p>
                <h4>Items:</h4>
                <ul>
                    ${Object.entries(JSON.parse(order.OrderDetails)).map(([item, details]) => `
                        <li>${item} x ${details.quantity} - ₱${(details.price * details.quantity).toFixed(2)}</li>
                    `).join('')}
                </ul>
                <div class="action-buttons">
                    <button class="approve-btn" aria-label="Approve"><span class="icon">&#10003;</span> Approve</button>
                    <button class="decline-btn" aria-label="Decline"><span class="icon">&#10007;</span> Decline</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Button event listeners
        modal.querySelector('.approve-btn').addEventListener('click', (e) => {
            e.stopPropagation();
            processOrder(order.OrderID, 'approved');
            closeOrderDetails(modal, overlay);
        });
        modal.querySelector('.decline-btn').addEventListener('click', (e) => {
            e.stopPropagation();
            processOrder(order.OrderID, 'declined');
            closeOrderDetails(modal, overlay);
        });
        modal.querySelector('.close-modal').addEventListener('click', (e) => {
            e.stopPropagation();
            closeOrderDetails(modal, overlay);
        });

        document.body.style.overflow = 'hidden';
        overlay.addEventListener('click', () => {
            closeOrderDetails(modal, overlay);
        });
    }

    function closeOrderDetails(card, overlay) {
        isOrderDetailsOpen = false;
        overlay.remove();
        card.remove(); // Remove the enlarged card from the DOM
        document.body.style.overflow = '';
        fetchOrders(); // Refresh list
    }

    function processOrder(orderId, status) {
        fetch('processOrder.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                orderId: orderId,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchOrders();
                showNotification(`Order #${orderId} ${status}`);
            } else {
                showNotification('Error: ' + data.error, 'error');
                throw new Error(data.error || 'Failed to process order');
            }
        })
        .catch(error => {
            console.error('Error processing order:', error);
            showNotification('Error processing order', 'error');
        });
    }

    function showNotification(message, type = 'success') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        // Add to document
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Add CSS for notifications
    const style = document.createElement('style');
    style.textContent = `
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 4px;
            color: white;
            font-weight: 500;
            transform: translateX(120%);
            transition: transform 0.3s ease-in-out;
            z-index: 1000;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            background-color: #4CAF50;
        }
        
        .notification.error {
            background-color: #f44336;
        }
    `;
    document.head.appendChild(style);

    // Initial fetch
    fetchOrders();
    
    // Refresh every 30 seconds
    setInterval(fetchOrders, 30000);
});
