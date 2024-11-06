<?php

function get_post_data() {
    return json_decode(file_get_contents('php://input'));
}

function list_orders($db) {

    $orders = [];
        
    $stmt = $db->prepare(<<<SQL
SELECT orders.id, created, SUM(order_items.quantity) as items
FROM "orders"
JOIN "order_items" ON orders.id = order_items.order_id
GROUP BY orders.id
SQL
    );
    $result = $stmt->execute();

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {

        array_push($orders, $row);
    }

    return array_values($orders);
}

function view_order($db, $order_id) {
        
    $stmt = $db->prepare(<<<SQL
SELECT orders.id, created, quantity, name, price
FROM "orders"
JOIN "order_items" ON orders.id = order_items.order_id
JOIN "menu_items" ON order_items.menu_item_id = menu_items.id
WHERE "order_id" = :order_id
SQL
    );
    $stmt->bindValue(':order_id', $order_id);
    $result = $stmt->execute();

    $order = [ 'id' => $order_id, 'items' => [] ];
    
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {

        $order['created'] = $row['created'];

        array_push($order['items'], [
            'name' => $row['name'],
            'price' => $row['price'],
            'quantity' => $row['quantity']
        ]);
    }

    return $order;
}

function create_order($db, $data) {

    $stmt = $db->prepare(<<<SQL
INSERT INTO "orders" ("created")
VALUES (CURRENT_TIMESTAMP)
SQL
    );
    $stmt->execute();
    $order_id = $db->lastInsertRowID();

    $stmt = $db->prepare(<<<SQL
INSERT INTO "order_items" ("order_id", "menu_item_id", "quantity")
VALUES (:order_id, :menu_item_id, :quantity)
SQL
    );
    foreach ($data->items as $item) {
        $stmt->bindValue(':order_id', $order_id);
        $stmt->bindValue(':menu_item_id', $item->menu_item_id);
        $stmt->bindValue(':quantity', $item->quantity);
        $stmt->execute();
    }
    
    return $order_id;
}

$db = new SQLite3('../data/db.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);

switch ($_SERVER['REQUEST_METHOD']) {
    
    case 'GET':

        if ($_GET['id']) {
            // GET /api/orders.php?id={order_id}
            exit(json_encode(view_order($db, $_GET['id'])));
        }
        else {
            // GET /api/orders.php
            exit(json_encode(list_orders($db)));
        }

    case 'POST':
        // POST /api/orders.php

        $data = get_post_data();
        $order_id = create_order($db, $data);
        
        header('HTTP/1.1 201 Created');
        exit(json_encode($order_id));
}