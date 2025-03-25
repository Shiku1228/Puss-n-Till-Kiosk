        let orders = {};
        let totalPrice = 0;
        
        const menuItems = {
            toppings: [
                { name: 'Cheyk-in', price: 10.00, img: '../Image_source/chicken_pastil.jpg' },
                { name: 'Spy See Cheyk-In', price: 10.00, img: '../Image_source/spicychicken_pastil.jpg' },
                { name: 'Cheyk-in Khar ey', price: 10.00, img: '../Image_source/curry_pastil.jpg'},
                { name: 'Cheyk-in Adee-boo', price: 10.00, img: '../Image_source/adobo_pastil.jpg' }
            ],
            addons: [
                { name: 'Taylong', price: 0.00, img: '../Image_source/talong.jpg' },
                { name: 'Alawmung', price: 0.00, img: '../Image_source/alamang.jpg' },
                { name: 'Soap', price: 0.00, img: '../Image_source/soup.jpg' },
                { name: 'Chilley-owel', price: 0.00, img: '../Image_source/chili_oil.jpg' }
            ],
            viands: [
                { name: 'Freight Cheyk-In', price: 25.00, img: '../Image_source/fried_chicken.jpg' },
                { name: 'Gurrlick Cheyk-In', price: 25.00, img: '../Image_source/garlic_fried_chicken.jpg' },
                { name: 'Shenghey', price: 5.00, img: '../Image_source/shanghai.jpg' },
                { name: 'Shoemey', price: 5.00, img: '../Image_source/siomai.jpg' }
            ],
            drinks: [
                { name: 'Peepsey', price: 15.00, img: '../Image_source/pepsi.jpg' },
                { name: 'Rawyal', price: 15.00, img: '../Image_source/royal.jpg' },
                { name: 'Cock', price: 15.00, img: '../Image_source/coke.jpg' },
                { name: 'Streight', price: 15.00, img: '../Image_source/sprite.jpg' }
            ]
        };
        
        function showCategory(category) {
            const menuContainer = document.getElementById("menu-container");
            menuContainer.innerHTML = "";

            menuItems[category].forEach(item => {
                const div = document.createElement("div");
                
                div.className = "item";
                
                div.onclick = () => addToOrder(item.name, item.price);
                
                div.innerHTML = `
                    <img src="${item.img}" alt="${item.name}">
                    <div class="item-label">
                         <span class="item-name">${item.name}</span>
                        <span class="item-price">₱${item.price.toFixed(2)}</span>
                    </div>
                `;

                menuContainer.appendChild(div);
            });
        }
        
        function addToOrder(item, price) {
            if (!orders[item]) {
                orders[item] = { quantity: 1, price };
            } else {
                orders[item].quantity++;
            }
            updateModalOrderList();
        }

        function changeQuantity(item, amount) {
            if (orders[item]) {
                orders[item].quantity += amount;
                if (orders[item].quantity <= 0) {
                    delete orders[item];
                }
                updateModalOrderList();
            }
        }

        function submitOrder() {
            alert("Order submitted! Total: ₱" + modalTotalPrice.toFixed(2));
        }

        document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("order-modal");
        const viewOrderBtn = document.querySelector(".top-section button");
        const closeBtn = document.querySelector(".close-btn");

        viewOrderBtn.addEventListener("click", function () {
            updateModalOrderList();
            modal.style.display = "block";
        });

        closeBtn.addEventListener("click", function () {
            modal.style.display = "none";
         });

        window.addEventListener("click", function (event) {
            if (event.target === modal) {
            modal.style.display = "none";
            }
        });
        });

        function updateModalOrderList() {
            const modalOrderList = document.getElementById("modal-order-list");
            modalOrderList.innerHTML = "";
            let modalTotalPrice = 0;

            for (let item in orders) {
                const listItem = document.createElement("li");

                modalTotalPrice += orders[item].quantity * orders[item].price;

                listItem.innerHTML = `
                    ${item} - ₱${orders[item].price.toFixed(2)} x ${orders[item].quantity}
                    <button class="opButt" onclick="changeQuantity('${item}', -1)">-</button>
                    ${orders[item].quantity}
                    <button class="opButt" onclick="changeQuantity('${item}', 1)">+</button>
                `;
                modalOrderList.appendChild(listItem);
            }

            document.getElementById("modal-total-price").textContent = modalTotalPrice.toFixed(2);
}   