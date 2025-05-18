// Chart instances
let categoryChart = null;
let trendChart = null;

document.addEventListener('DOMContentLoaded', function() {
    let currentPath = window.location.pathname;

    let pageLinks = {
        "homepage_staff.php": "home",
        "orderHistory.php": "history",
        "receiveOrder.php": "receive",
        "records.php": "records"
    };

    for(let page in pageLinks) {
        if(currentPath.includes(page)){
            let element = document.getElementById(pageLinks[page]);
            if(element) {
                element.classList.add("active");
            }
            break;
        }
    }

    // Collapsible Sales Summary
    document.querySelectorAll('.category-row').forEach(function(row) {
        row.addEventListener('click', function() {
            const cat = row.getAttribute('data-category');
            const tbody = document.querySelector('.items-tbody[data-category="' + cat + '"]');
            const isOpen = row.classList.contains('open');
            // Close all
            document.querySelectorAll('.category-row').forEach(r => r.classList.remove('open'));
            document.querySelectorAll('.items-tbody').forEach(tb => tb.style.display = 'none');
            if (!isOpen) {
                row.classList.add('open');
                if (tbody) tbody.style.display = '';
            }
        });
    });

    // Initialize FullCalendar
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek'
        },
        events: function(info, successCallback, failureCallback) {
            fetch('get_daily_sales.php')
                .then(response => response.json())
                .then(data => {
                    const events = data.map(sale => ({
                        title: `₱${sale.total_sales}`,
                        date: sale.date,
                        backgroundColor: '#4CAF50',
                        borderColor: '#4CAF50'
                    }));
                    successCallback(events);
                })
                .catch(error => {
                    console.error('Error fetching sales data:', error);
                    failureCallback(error);
                });
        },
        dateClick: function(info) {
            fetchOrdersForDate(info.dateStr);
        }
    });
    calendar.render();

    // Initialize analytics
    initializeAnalytics();

    // Show today's orders by default
    const today = new Date().toISOString().split('T')[0];
    fetchOrdersForDate(today);

    // Listen for order approval events
    document.addEventListener('orderApproved', function() {
        fetchOrdersForDate(today);
    });
});

// Function to fetch and display orders for a specific date
function fetchOrdersForDate(date) {
    console.log('Fetching orders for date:', date);
    document.getElementById('selected-date').textContent = new Date(date).toLocaleDateString();
    
    fetch(`get_orders_by_date.php?date=${date}`)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data);
            if (!data.orders || data.orders.length === 0) {
                console.log('No orders in response data');
            }
            updateDailySummary(data);
            updateOrderHistoryTable(data.orders);
        })
        .catch(error => {
            console.error('Error fetching orders:', error);
        });
}

// Function to update daily summary
function updateDailySummary(data) {
    document.getElementById('total-orders').textContent = data.total_orders;
    document.getElementById('total-sales').textContent = `₱${data.total_sales.toFixed(2)}`;
}

// Function to update order history table
function updateOrderHistoryTable(orders) {
    console.log('Updating order history table with orders:', orders);
    const tbody = document.getElementById('order-history-body');
    tbody.innerHTML = '';
    
    if (!orders || orders.length === 0) {
        console.log('No orders to display');
        tbody.innerHTML = '<tr><td colspan="7">No orders found.</td></tr>';
        return;
    }

    orders.forEach(order => {
        console.log('Processing order:', order);
        const timestamp = new Date(order.Timestamps);
        const date = timestamp.toLocaleDateString();
        const time = timestamp.toLocaleTimeString();
        const status = order.orderStatus ? order.orderStatus.trim() : '';
        
        console.log('Order details:', {
            HistoryID: order.HistoryID,
            OrderID: order.OrderID,
            date: date,
            time: time,
            status: status
        });
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${order.HistoryID}</td>
            <td>${order.OrderID}</td>
            <td>${date}</td>
            <td>${time}</td>
            <td>₱${parseFloat(order.totalPrice).toFixed(2)}</td>
            <td><span class="status-badge ${status.toLowerCase()}">${
                status.charAt(0).toUpperCase() + status.slice(1)
            }</span></td>
            <td>
                <button onclick="viewOrderDetails(${order.HistoryID})" class="view-btn">
                    <span class="cart-icon">View Order</span>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Function to view order details
function viewOrderDetails(historyId) {
    console.log('Fetching order details for HistoryID:', historyId);
    fetch(`get_order_details.php?history_id=${historyId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            console.log('Order details fetched:', data);
            showOrderDetailsModal(data);
        })
        .catch(error => {
            console.error('Error fetching order details:', error);
        });
}

function showOrderDetailsModal(orderDetails) {
    console.log('Showing order details modal with data:', orderDetails);
    const modal = document.createElement('div');
    modal.className = 'modal';
    
    const items = orderDetails.items;
    const itemsList = Object.entries(items).map(([item, details]) => `
        <tr>
            <td>${item}</td>
            <td>${details.quantity}</td>
            <td>₱${parseFloat(details.price).toFixed(2)}</td>
            <td>₱${(parseFloat(details.price) * details.quantity).toFixed(2)}</td>
        </tr>
    `).join('');

    modal.innerHTML = `
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Order Details</h2>
            <div class="order-details">
                <div class="order-info">
                    <p><strong>Order ID:</strong> ${orderDetails.OrderID}</p>
                    <p><strong>Date:</strong> ${new Date(orderDetails.Timestamps).toLocaleString()}</p>
                    <p><strong>Status:</strong> <span class="status-badge ${orderDetails.orderStatus || 'unknown'}">${
                        orderDetails.orderStatus
                            ? orderDetails.orderStatus.charAt(0).toUpperCase() + orderDetails.orderStatus.slice(1)
                            : 'Unknown'
                    }</span></p>
                </div>
                <div class="items-table">
                    <h3>Order Items</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsList}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                                <td><strong>₱${parseFloat(orderDetails.totalPrice).toFixed(2)}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    modal.style.display = "block";

    const closeBtn = modal.querySelector('.close');
    closeBtn.onclick = function() {
        modal.remove();
    };

    window.onclick = function(event) {
        if (event.target === modal) {
            modal.remove();
        }
    };
}

// Analytics Functions
function initializeAnalytics() {
    // Get categories for filter
    fetch('get_analytics.php?time_range=all&category=all')
        .then(response => response.json())
        .then(data => {
            const categoryFilter = document.getElementById('category-filter');
            Object.keys(data.categories).forEach(category => {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                categoryFilter.appendChild(option);
            });
            
            // Initial chart render
            updateAnalytics();
        })
        .catch(error => {
            console.error('Error initializing analytics:', error);
        });

    // Add event listeners for filters
    document.getElementById('time-range').addEventListener('change', updateAnalytics);
    document.getElementById('category-filter').addEventListener('change', updateAnalytics);
}

function updateAnalytics() {
    const timeRange = document.getElementById('time-range').value;
    const category = document.getElementById('category-filter').value;

    fetch(`get_analytics.php?time_range=${timeRange}&category=${category}`)
        .then(response => response.json())
        .then(data => {
            if (category === 'all') {
                updateCategoryChart(data);
            } else {
                updateCategoryItemsChart(data, category);
            }
            updateTrendChart(data);
        })
        .catch(error => {
            console.error('Error updating analytics:', error);
        });
}

function updateCategoryItemsChart(data, category) {
    const ctx = document.getElementById('categoryChart').getContext('2d');
    
    if (categoryChart) {
        categoryChart.destroy();
    }

    // Get all items in the selected category
    const items = data.topItems[category] || {};
    const itemNames = Object.keys(items);
    const quantities = itemNames.map(item => items[item].quantity);
    const colors = generateColors(itemNames.length);

    // Create a horizontal bar chart for better label visibility
    categoryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: itemNames,
            datasets: [{
                label: 'Items Sold',
                data: quantities,
                backgroundColor: colors,
                borderColor: colors.map(color => color.replace('0.7', '1')),
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y', // This makes the chart horizontal
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: `${category} - Items Breakdown`,
                    font: {
                        size: 16
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const item = itemNames[context.dataIndex];
                            const details = items[item];
                            return [
                                `Quantity: ${details.quantity}`,
                                `Revenue: ₱${details.revenue.toFixed(2)}`,
                                `Unit Price: ₱${details.price.toFixed(2)}`
                            ];
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Quantity Sold'
                    }
                },
                y: {
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });

    // Update the summary section
    const totalItems = quantities.reduce((a, b) => a + b, 0);
    const totalRevenue = Object.values(items).reduce((sum, item) => sum + item.revenue, 0);
    const averagePrice = totalRevenue / totalItems;
    
    const summaryHtml = `
        <div class="category-summary">
            <h3>${category} Summary</h3>
            <div class="summary-stats">
                <div class="stat-box">
                    <h4>Total Items Sold</h4>
                    <p>${totalItems}</p>
                </div>
                <div class="stat-box">
                    <h4>Total Revenue</h4>
                    <p>₱${totalRevenue.toFixed(2)}</p>
                </div>
                <div class="stat-box">
                    <h4>Average Price</h4>
                    <p>₱${averagePrice.toFixed(2)}</p>
                </div>
            </div>
        </div>
    `;

    // Insert summary before the chart
    const chartContainer = document.querySelector('.chart-container');
    const existingSummary = chartContainer.querySelector('.category-summary');
    if (existingSummary) {
        existingSummary.remove();
    }
    chartContainer.insertAdjacentHTML('afterbegin', summaryHtml);

    // Adjust chart container height based on number of items
    const minHeight = 400;
    const heightPerItem = 40;
    const newHeight = Math.max(minHeight, itemNames.length * heightPerItem);
    chartContainer.style.height = `${newHeight}px`;
}

function updateCategoryChart(data) {
    const ctx = document.getElementById('categoryChart').getContext('2d');
    
    if (categoryChart) {
        categoryChart.destroy();
    }

    const categories = Object.keys(data.categories);
    const quantities = Object.values(data.categories);
    const colors = generateColors(categories.length);

    // Create tooltip content with item details
    const tooltipContent = (context) => {
        const category = context.label;
        const items = data.topItems[category] || {};
        let tooltip = `Total Items: ${context.raw}\n\nTop Items:\n`;
        
        Object.entries(items).forEach(([item, details], index) => {
            tooltip += `${index + 1}. ${item}\n`;
            tooltip += `   Quantity: ${details.quantity}\n`;
            tooltip += `   Revenue: ₱${details.revenue.toFixed(2)}\n`;
        });
        
        return tooltip;
    };

    categoryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: categories,
            datasets: [{
                label: 'Items Sold by Category',
                data: quantities,
                backgroundColor: colors,
                borderColor: colors.map(color => color.replace('0.7', '1')),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Sales by Category'
                },
                tooltip: {
                    callbacks: {
                        label: tooltipContent
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Quantity Sold'
                    }
                }
            },
            onClick: (event, elements) => {
                if (elements.length > 0) {
                    const category = categories[elements[0].index];
                    showCategoryDetails(category, data.topItems[category]);
                }
            }
        }
    });
}

function showCategoryDetails(category, items) {
    const modal = document.createElement('div');
    modal.className = 'modal';
    
    let itemsHtml = '';
    Object.entries(items).forEach(([item, details]) => {
        itemsHtml += `
            <tr>
                <td>${item}</td>
                <td>${details.quantity}</td>
                <td>₱${details.price.toFixed(2)}</td>
                <td>₱${details.revenue.toFixed(2)}</td>
            </tr>
        `;
    });

    modal.innerHTML = `
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>${category} - Item Details</h2>
            <div class="category-details">
                <table>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity Sold</th>
                            <th>Unit Price</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                </table>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    const closeBtn = modal.querySelector('.close');
    closeBtn.onclick = function() {
        modal.remove();
    };

    window.onclick = function(event) {
        if (event.target === modal) {
            modal.remove();
        }
    };
}

function updateTrendChart(data) {
    const ctx = document.getElementById('trendChart').getContext('2d');
    
    if (trendChart) {
        trendChart.destroy();
    }

    const dates = Object.keys(data.dailySales);
    const sales = Object.values(data.dailySales);

    trendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Daily Sales',
                data: sales,
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Sales Trend'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Total Sales (₱)'
                    }
                }
            }
        }
    });
}

function generateColors(count) {
    const colors = [
        'rgba(76, 175, 80, 0.7)',   // Green
        'rgba(33, 150, 243, 0.7)',  // Blue
        'rgba(255, 193, 7, 0.7)',   // Yellow
        'rgba(233, 30, 99, 0.7)',   // Pink
        'rgba(156, 39, 176, 0.7)',  // Purple
        'rgba(255, 87, 34, 0.7)',   // Orange
        'rgba(0, 188, 212, 0.7)',   // Cyan
        'rgba(121, 85, 72, 0.7)'    // Brown
    ];
    
    while (colors.length < count) {
        colors.push(...colors);
    }
    
    return colors.slice(0, count);
}