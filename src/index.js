document.addEventListener('DOMContentLoaded', async (event) => {
    
    const response = await fetch('http://localhost:8000/api/menu.php');

    class App {

        constructor(props, parent) {
            this.props = props;
            this.parent = parent;
            this.render();
        }

        render = () => {
            const menu_dl = document.getElementById('menu');
            menu_dl.innerHTML = '';
            this.props.menu.forEach(item => {
                new MenuItemRow(item, menu_dl);
            });

            const tbody = document.getElementById('order-items');
            tbody.innerHTML = '';
            new OrderTableBody(this.props.order, tbody);
            new OrderTableFoot(this.props.order, null);

            document.getElementById('submit').addEventListener('click', this.placeOrder);
        }

        addOrderItem = (menu_item_id) => {
    
            const order_item = this.props.order.items.find(item => item.menu_item_id == menu_item_id);
    
            if (order_item) {
                order_item.quantity = order_item.quantity + 1;
            }
            else {
                const menu_item = this.props.menu.find(item => item.id == menu_item_id);
                if (menu_item) {
                    this.props.order.items.push({
                        menu_item_id: menu_item_id,
                        name: menu_item.name,
                        price: menu_item.price,
                        quantity: 1
                    });
                }
            }

            this.render();
        };

        placeOrder = async () => {
            const response = await fetch('http://localhost:8000/api/orders.php', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(this.props.order)
            });
            console.log(response);
            this.props.order = { items: [] };
            this.render();
        };
    }

    class MenuItemRow {

        constructor(props, parent) {
            this.props = props;
            this.parent = parent;
            this.render();
        }

        render = () => {
            let dt = document.createElement('dt')
            dt.innerText = `${this.props.name} (${this.props.price})`;
            this.parent.appendChild(dt);

            let dd = document.createElement('dd');
            dd.innerText = this.props.description;
            this.parent.appendChild(dd);

            dt.addEventListener('click', this.handleClick);
            dd.addEventListener('click', this.handleClick);
        }

        handleClick = (event) => {
            app.addOrderItem(this.props.id);
        }
    }

    class OrderItemRow {

        constructor(props, parent) {
            this.props = props;
            this.parent = parent;
            this.render();
        }

        render = () => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
<td>${this.props.name}</td>
<td>${this.props.quantity}</td>
<td>${this.props.price}</td>
<td>
    <button>-</button>
    <button>+</button>
</td>`;
            this.parent.appendChild(tr);
            let buttons = tr.getElementsByTagName('button');
            buttons[0].addEventListener('click', this.handleClickMinus);
            buttons[1].addEventListener('click', this.handleClickPlus);
        }

        handleClickMinus = (event) => {
            this.props.quantity = this.props.quantity - 1;
            app.render();
        }

        handleClickPlus = (event) => {
            this.props.quantity = this.props.quantity + 1;
            app.render();
        }
    }
    
    class OrderTableBody {
        
        constructor(props, parent) {
            this.props = props;
            this.parent = parent;
            this.render();
        }

        render = () => {
            this.props.items.forEach(item => {
                let tr = new OrderItemRow(item, this.parent);
            });
        }
    };

    class OrderTableFoot {

        constructor(props, parent) {
            this.props = props;
            this.parent = parent;
            this.render();
        }

        render = () => {
            const totalQuantity = this.props.items.map(item => item.quantity).reduce((acc, x) => { return acc + x }, 0);
            const totalPrice = this.props.items.map(item => item.quantity * item.price).reduce((acc, x) => { return acc + x }, 0);

            const tds = document.querySelectorAll('tfoot td');
            console.log(tds);
            tds[1].innerText = totalQuantity;
            tds[2].innerText = totalPrice;
        }
    }

    const app = new App({
        menu: await response.json(),
        order: { items: [] }
    });

    app.render();
});
