<?php

$db = new SQLite3('../data/db.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);

switch ($_SERVER['REQUEST_METHOD']) {
    
    case 'GET':

        $menu = [];
        
        $stmt = $db->prepare('SELECT * FROM "menu_items"');
        $result = $stmt->execute();

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            array_push($menu, $row);
        }

        exit(json_encode($menu));
}