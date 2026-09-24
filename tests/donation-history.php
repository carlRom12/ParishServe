<?php
// php tests/donation-history.php. Test records are always rolled back.
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/donation-history.php';
$conn->begin_transaction();
try {
    $mine=2147483600; $other=2147483601;
    for ($i=0;$i<23;$i++) {
        $owner=$i<21 ? $mine : ($i===21 ? $other : null);
        $ref='TEST-'.bin2hex(random_bytes(5));
        $stmt=$conn->prepare("INSERT INTO donations (donation_no,contact_number,donor_name,amount,purpose,user_id) VALUES (?,'09123456789','Same Name',100,'Test',?)");
        $stmt->bind_param('si',$ref,$owner);$stmt->execute();
    }
    $page=ps_donation_history($conn,$mine);
    if(count($page['donations'])!==20 || !$page['nextCursor']) throw new RuntimeException('Pagination failed.');
    $next=ps_donation_history($conn,$mine,$page['nextCursor']);
    if(count($next['donations'])!==1 || $next['nextCursor']!==null) throw new RuntimeException('Second page failed.');
    if(count(ps_donation_history($conn,$other)['donations'])!==1) throw new RuntimeException('Ownership filter failed.');
    $_SESSION=[];
    if(ps_donation_account_id($conn)!==null) throw new RuntimeException('Guest accepted.');
    $_SESSION=['user_id'=>2147483602];
    if(ps_donation_account_id($conn)!==null) throw new RuntimeException('Unknown account accepted.');
    echo "Ownership isolation, guest handling, and pagination passed.\n";
} finally { $conn->rollback(); }
