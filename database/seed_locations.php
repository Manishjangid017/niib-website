<?php
/**
 * One-off migration + seed:
 * 1) Create `locations` table
 * 2) Seed with NIIB regional data (Punjab + Chandigarh + J&K + UP + Bihar + Uttarakhand)
 * 3) Remove old "locations" page from `pages` so the new dynamic route takes over
 * 4) Update menu items to point to /locations (not /page/locations)
 *
 * Run once via CLI: php database/seed_locations.php
 */
define('NIIB_INIT', true);
require __DIR__ . '/../config/database.php';

$pdo = db();
$log = [];

try {
    // ---------- Create table ----------
    $pdo->exec("CREATE TABLE IF NOT EXISTS `locations` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `state` VARCHAR(80) NOT NULL,
        `state_slug` VARCHAR(80) NOT NULL,
        `state_sort` INT(11) NOT NULL DEFAULT 0,
        `city` VARCHAR(150) NOT NULL,
        `slug` VARCHAR(180) NOT NULL,
        `card_style` ENUM('featured','compact') NOT NULL DEFAULT 'compact',
        `hub_label` VARCHAR(80) DEFAULT NULL,
        `address` TEXT,
        `phone` VARCHAR(50) DEFAULT NULL,
        `email` VARCHAR(100) DEFAULT NULL,
        `map_url` VARCHAR(500) DEFAULT NULL,
        `short_note` VARCHAR(255) DEFAULT NULL,
        `sort_order` INT(11) NOT NULL DEFAULT 0,
        `status` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`),
        KEY `state_slug` (`state_slug`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $log[] = '✓ `locations` table ready';

    // ---------- Seed data ----------
    $pdo->exec("DELETE FROM locations");

    $rows = [
        // Punjab — state_sort = 1
        ['Punjab','punjab',1,'Ludhiana','ludhiana','featured','State Hub','Model Town & Ferozepur Road Centers','+91 99145 27724',null,'Ludhiana, Punjab',null,1],
        ['Punjab','punjab',1,'Mohali','mohali','featured','State Hub','Phase 3B2 & Phase 7, SAS Nagar','+91 84279 99185',null,'Mohali, Punjab',null,2],
        ['Punjab','punjab',1,'Amritsar','amritsar','featured','State Hub','Ranjit Avenue & Lawrence Road','+91 96460 55557',null,'Amritsar, Punjab',null,3],
        ['Punjab','punjab',1,'Jalandhar','jalandhar','compact','Signature Learning Center','Shakti Tower, 2nd Floor, Above Vishal Mega Mart','+91 99146 55557',null,'Jalandhar, Punjab','Signature Learning Center',4],
        ['Punjab','punjab',1,'Patiala','patiala','compact','Signature Learning Center',null,null,null,'Patiala, Punjab','Signature Learning Center',5],
        ['Punjab','punjab',1,'Bathinda','bathinda','compact','Signature Learning Center',null,null,null,'Bathinda, Punjab','Signature Learning Center',6],
        ['Punjab','punjab',1,'Hoshiarpur','hoshiarpur','compact','Strategic Center',null,null,null,'Hoshiarpur, Punjab','Strategic Center',7],
        ['Punjab','punjab',1,'Moga','moga','compact','Strategic Center',null,null,null,'Moga, Punjab','Strategic Center',8],
        ['Punjab','punjab',1,'Phagwara','phagwara','compact','Strategic Center',null,null,null,'Phagwara, Punjab','Strategic Center',9],
        ['Punjab','punjab',1,'Pathankot','pathankot','compact','Strategic Center',null,null,null,'Pathankot, Punjab','Strategic Center',10],
        ['Punjab','punjab',1,'Abohar','abohar','compact','Strategic Center',null,null,null,'Abohar, Punjab','Strategic Center',11],

        // Chandigarh — state_sort = 2
        ['Chandigarh U.T.','chandigarh',2,'Sector 34-A Hub','chandigarh-sector-34','featured','Union Territory Hub','SCO 60-61, Sector 34-A, Educational Hub, Chandigarh','+91 96460 55557','cm.chd@newimageinstitute.com','SCO 60-61 Sector 34A Chandigarh',null,1],

        // J&K — state_sort = 3
        ['Jammu & Kashmir','jammu-kashmir',3,'Jammu City','jammu-city','featured','North Region','Gandhi Nagar, Jammu City','+91 99146 55557',null,'Gandhi Nagar Jammu',null,1],

        // Uttar Pradesh — state_sort = 4
        ['Uttar Pradesh','uttar-pradesh',4,'Lucknow','lucknow','featured','Central Region','Hazratganj Main Center, Lucknow','+91 99146 55557',null,'Hazratganj Lucknow',null,1],

        // Bihar — state_sort = 5
        ['Bihar','bihar',5,'Patna','patna','featured','East Region','Boring Road, Patna','+91 99146 55557',null,'Boring Road Patna',null,1],

        // Uttarakhand — state_sort = 6
        ['Uttarakhand','uttarakhand',6,'Dehradun','dehradun','featured','Hill Region','Rajpur Road, Dehradun','+91 99146 55557',null,'Rajpur Road Dehradun',null,1],
    ];

    $st = $pdo->prepare("INSERT INTO locations (state,state_slug,state_sort,city,slug,card_style,hub_label,address,phone,email,map_url,short_note,sort_order,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,1)");
    foreach($rows as $r) $st->execute($r);
    $log[] = '✓ ' . count($rows) . ' locations seeded';

    // ---------- Remove the old "locations" page from pages so the dynamic route owns the URL ----------
    $pdo->prepare("DELETE FROM pages WHERE slug='locations'")->execute();
    $log[] = '✓ Removed old static `locations` page';

    // ---------- Update header menu: change /page/locations → /locations ----------
    $pdo->prepare("UPDATE menu_items SET url='locations.php' WHERE url='page/locations' AND title='Locations' AND parent_id=0")->execute();
    $pdo->prepare("UPDATE menu_items SET url='locations.php' WHERE url='page/locations' AND title='All Locations'")->execute();
    $log[] = '✓ Updated menu items to point to /locations';

} catch (Throwable $e) {
    $log[] = '✗ ERROR: ' . $e->getMessage();
}

header('Content-Type: text/plain; charset=utf-8');
echo "Locations migration & seed\n" . str_repeat('=', 50) . "\n\n";
foreach($log as $l) echo "$l\n";
echo "\n" . str_repeat('=', 50) . "\nDelete this file: database/seed_locations.php\n";
