<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "your_database";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get query parameters
    $days = isset($_GET['days']) ? intval($_GET['days']) : 30;
    $priority = isset($_GET['priority']) ? $_GET['priority'] : 'all';
    
    // Build WHERE clause
    $whereClause = "WHERE 1=1";
    
    if ($days > 0) {
        $whereClause .= " AND t.created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)";
    }
    
    if ($priority !== 'all') {
        $whereClause .= " AND t.priority = :priority";
    }
    
    // Query to get ticket data with SLA status
    $query = "
        SELECT 
            t.ticket_id,
            t.priority,
            sd.sla_name,
            sd.resolution_time_hours,
            t.subject,
            t.created_at,
            t.resolved_at,
            TIMESTAMPDIFF(HOUR, t.created_at, t.resolved_at) AS hours_to_resolve,
            CASE 
                WHEN t.resolved_at IS NULL AND NOW() > t.sla_breach_time THEN 'breached'
                WHEN t.resolved_at IS NULL THEN 'not_applicable'
                WHEN TIMESTAMPDIFF(HOUR, t.created_at, t.resolved_at) <= sd.resolution_time_hours THEN 'within_sla'
                ELSE 'breached'
            END AS sla_status,
            CASE 
                WHEN t.resolved_at IS NULL THEN NULL
                WHEN TIMESTAMPDIFF(HOUR, t.created_at, t.resolved_at) <= sd.resolution_time_hours THEN 0
                ELSE TIMESTAMPDIFF(HOUR, t.created_at, t.resolved_at) - sd.resolution_time_hours
            END AS hours_over_sla
        FROM 
            tickets t
        JOIN 
            sla_definitions sd ON t.priority = sd.priority
        $whereClause
        ORDER BY t.created_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    
    if ($priority !== 'all') {
        $stmt->bindParam(':priority', $priority);
    }
    
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['tickets' => $tickets]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>