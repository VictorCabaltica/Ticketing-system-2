<?php
// db_connection.php - Ensure this file includes your database connection code
include 'db_connection.php';
include 'admin-sidebar.php';

// Fetch all tickets from the database
$sql = "SELECT * FROM tickets ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$tickets_result = $stmt->get_result();
date_default_timezone_set('Asia/Manila');
// Function to calculate remaining time

function getRemainingTime($created_at, $priority, $status) {


  $created = new DateTime($created_at);

  $slaHours = [
      'critical' => 2,
      'high'     => 4,
      'medium'   => 6,
      'low'      => 8
  ];

  if (!isset($slaHours[$priority])) return 'Invalid Priority';

  // Calculate deadline
  $deadline = clone $created;
  $deadline->modify("+{$slaHours[$priority]} hours");

  $now = new DateTime();

  if ($status === 'closed') {
      // If closed, check if it was closed before SLA deadline
      return $now <= $deadline ? 'SLA Met' : 'Overdue';
  }

  // For open or in_progress
  if ($now > $deadline) {
      return 'Overdue';
  } else {
      $remaining = $now->diff($deadline);
      return $remaining->format('%h hours, %i minutes remaining');
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Tickets</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #4361ee;
      --danger-color: #f72585;
      --success-color: #4cc9f0;
      --warning-color: #f8961e;
      --critical-color: #d00000;
      --high-color: #ff5400;
      --medium-color: #ff9e00;
      --low-color: #38b000;
      --bg-color: #f8f9fa;
      --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      --transition: all 0.3s ease;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--bg-color);
      margin: 0;
      padding: 0;
    }
    
    .main-content {
      margin-left: 250px;
      padding: 20px;
      transition: var(--transition);
    }
    
    .table-container {
      background: white;
      border-radius: 10px;
      box-shadow: var(--card-shadow);
      padding: 25px;
      margin: 20px auto;
      max-width: 1200px;
      animation: fadeIn 0.5s ease-out;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    h2 {
      color: var(--primary-color);
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .filter-controls {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    
    .filter-controls select, .filter-controls input {
      padding: 8px 12px;
      border-radius: 5px;
      border: 1px solid #ddd;
      min-width: 150px;
    }
    
    .filter-controls button {
      padding: 8px 15px;
      background-color: var(--primary-color);
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: var(--transition);
    }
    
    .filter-controls button:hover {
      background-color: #3a56d4;
      transform: translateY(-2px);
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    
    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #e0e0e0;
    }
    
    th {
      background-color: #f1f3f9;
      font-weight: 600;
      position: sticky;
      top: 0;
    }
    
    tr {
      transition: var(--transition);
    }
    
    tr:hover {
      background-color: #f8f9ff;
      transform: scale(1.005);
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .priority-critical {
      color: var(--critical-color);
      font-weight: bold;
    }
    
    .priority-high {
      color: var(--high-color);
      font-weight: bold;
    }
    
    .priority-medium {
      color: var(--medium-color);
      font-weight: bold;
    }
    
    .priority-low {
      color: var(--low-color);
      font-weight: bold;
    }
    
    .status-badge {
      padding: 6px 10px;
      border-radius: 20px;
      color: white;
      font-size: 0.8rem;
      display: inline-block;
      min-width: 80px;
      text-align: center;
      transition: var(--transition);
    }
    
    .status-open {
      background-color: var(--primary-color);
    }
    
    .status-in_progress {
      background-color: var(--warning-color);
    }
    
    .status-closed {
      background-color: var(--success-color);
    }
    
    .sla-met {
      color: var(--success-color);
      font-weight: bold;
    }
    
    .sla-overdue {
      color: var(--danger-color);
      font-weight: bold;
      animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.6; }
      100% { opacity: 1; }
    }
    
    .actions {
      display: flex;
      gap: 8px;
    }
    
    .actions a, .actions button {
      padding: 6px 12px;
      border-radius: 4px;
      text-decoration: none;
      font-size: 0.85rem;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 5px;
    }
    
    .actions a {
      background-color: var(--primary-color);
      color: white;
    }
    
    .actions a:hover {
      background-color: #3a56d4;
      transform: translateY(-2px);
    }
    
    .actions button {
      background-color: var(--danger-color);
      color: white;
      border: none;
      cursor: pointer;
    }
    
    .actions button:hover {
      background-color: #e5177e;
      transform: translateY(-2px);
    }
    
    .empty-state {
      text-align: center;
      padding: 40px;
      color: #666;
    }
    
    .empty-state i {
      font-size: 50px;
      margin-bottom: 20px;
      color: #ddd;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .main-content {
        margin-left: 0;
      }
      
      .filter-controls {
        flex-direction: column;
      }
      
      table {
        display: block;
        overflow-x: auto;
      }
    }
    
    /* Loading animation */
    .loading-row td {
      position: relative;
      overflow: hidden;
    }
    
    .loading-row td::after {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8), transparent);
      animation: loading 1.5s infinite;
    }
    
    @keyframes loading {
      0% { transform: translateX(-100%); }
      100% { transform: translateX(100%); }
    }
  </style>
</head>
<body>

<div class="main-content">
  <div class="table-container">
    <h2><i class="fas fa-ticket-alt"></i> Manage Tickets</h2>
    
    <div class="filter-controls">
      <select id="priority-filter">
        <option value="">All Priorities</option>
        <option value="critical">Critical</option>
        <option value="high">High</option>
        <option value="medium">Medium</option>
        <option value="low">Low</option>
      </select>
      
      <select id="status-filter">
        <option value="">All Statuses</option>
        <option value="open">Open</option>
        <option value="in_progress">In Progress</option>
        <option value="closed">Closed</option>
      </select>
      
      <input type="text" id="search-input" placeholder="Search tickets...">
      
      <button id="apply-filters"><i class="fas fa-filter"></i> Apply Filters</button>
      <button id="reset-filters"><i class="fas fa-sync-alt"></i> Reset</button>
    </div>
    
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Ticket ID</th>
            <th>Name</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Created At</th>
            <th>SLA Status</th>
            <th>Subject</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="ticket-table">
          <?php if ($tickets_result->num_rows > 0): ?>
            <?php while($ticket = $tickets_result->fetch_assoc()): 
              $remainingTime = getRemainingTime($ticket['created_at'], $ticket['priority'], $ticket['status']);
              $slaClass = ($remainingTime === 'Overdue') ? 'sla-overdue' : (($remainingTime === 'SLA Met') ? 'sla-met' : '');
            ?>
              <tr data-priority="<?php echo $ticket['priority']; ?>" data-status="<?php echo $ticket['status']; ?>">
                <td><?php echo $ticket['ticket_id']; ?></td>
                <td><?php echo htmlspecialchars($ticket['name']); ?></td>
                <td class="priority-<?php echo $ticket['priority']; ?>">
                  <i class="fas fa-<?php echo $ticket['priority'] === 'critical' ? 'fire' : ($ticket['priority'] === 'high' ? 'exclamation-triangle' : ($ticket['priority'] === 'medium' ? 'exclamation-circle' : 'info-circle')); ?>"></i>
                  <?php echo ucfirst($ticket['priority']); ?>
                </td>
                <td>
                  <span class="status-badge status-<?php echo $ticket['status']; ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                  </span>
                </td>
                <td><?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></td>
                <td class="<?php echo $slaClass; ?>" id="remaining-time-<?php echo $ticket['ticket_id']; ?>">
                  <?php echo $remainingTime; ?>
                </td>
                <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                <td class="actions">
                  <a href="admin-update-ticket.php?ticket_id=<?php echo $ticket['ticket_id']; ?>">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                  <td class="actions">
  <form action="admin-delete-ticket.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this ticket?');" style="display:inline;">
    <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>">
    <button type="submit"><i class="fas fa-trash"></i> Delete</button>
  </form>
</td>

                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No tickets found</h3>
                <p>There are currently no tickets in the system</p>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Function to delete a ticket with confirmation
  function deleteTicket(ticketId) {
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, delete it!',
      background: 'white',
      backdrop: `
        rgba(0,0,0,0.4)
        url("/images/nyan-cat.gif")
        left top
        no-repeat
      `
    }).then((result) => {
      if (result.isConfirmed) {
        // Show loading state
        const row = document.querySelector(`tr[data-ticket-id="${ticketId}"]`) || 
                   document.querySelector(`tr:has(td:first-child:contains("${ticketId}"))`);
        if (row) {
          row.classList.add('loading-row');
        }
        
        // AJAX request to delete ticket
        fetch('admin-delete-ticket.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `ticket_id=${ticketId}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire(
              'Deleted!',
              'The ticket has been deleted.',
              'success'
            ).then(() => {
              // Fade out and remove the row
              if (row) {
                row.style.opacity = '0';
                setTimeout(() => {
                  row.remove();
                  
                  // If no tickets left, show empty state
                  if (document.querySelectorAll('#ticket-table tr:not(.empty-state)').length === 0) {
                    const emptyState = `
                      <tr>
                        <td colspan="8" class="empty-state">
                          <i class="fas fa-inbox"></i>
                          <h3>No tickets found</h3>
                          <p>There are currently no tickets in the system</p>
                        </td>
                      </tr>
                    `;
                    document.getElementById('ticket-table').innerHTML = emptyState;
                  }
                }, 300);
              } else {
                location.reload();
              }
            });
          } else {
            throw new Error(data.message || 'Failed to delete ticket');
          }
        })
        .catch(error => {
          Swal.fire(
            'Error!',
            error.message,
            'error'
          );
          if (row) row.classList.remove('loading-row');
        });
      }
    });
  }

  // Filter tickets function
  function filterTickets() {
    const priorityFilter = document.getElementById('priority-filter').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value.toLowerCase();
    const searchQuery = document.getElementById('search-input').value.toLowerCase();
    
    const rows = document.querySelectorAll('#ticket-table tr:not(.empty-state)');
    
    let visibleRows = 0;
    
    rows.forEach(row => {
      const priority = row.getAttribute('data-priority') || '';
      const status = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();
      
      const matchesPriority = !priorityFilter || priority === priorityFilter;
      const matchesStatus = !statusFilter || status.replace('_', '') === statusFilter.replace('_', '');
      const matchesSearch = !searchQuery || rowText.includes(searchQuery);
      
      if (matchesPriority && matchesStatus && matchesSearch) {
        row.style.display = '';
        visibleRows++;
        
        // Add animation for appearing rows
        row.style.animation = 'fadeIn 0.5s ease-out';
      } else {
        row.style.display = 'none';
      }
    });
    
    // Show empty state if no matches
    const emptyState = document.querySelector('.empty-state');
    if (visibleRows === 0 && rows.length > 0) {
      if (!emptyState) {
        const emptyRow = `
          <tr class="empty-state-row">
            <td colspan="8" class="empty-state">
              <i class="fas fa-search"></i>
              <h3>No matching tickets</h3>
              <p>Try adjusting your filters or search query</p>
            </td>
          </tr>
        `;
        document.getElementById('ticket-table').appendChild(document.createRange().createContextualFragment(emptyRow));
      }
    } else if (emptyState) {
      emptyState.closest('tr').remove();
    }
  }
  
  // Event listeners for filters
  document.getElementById('apply-filters').addEventListener('click', filterTickets);
  document.getElementById('reset-filters').addEventListener('click', () => {
    document.getElementById('priority-filter').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('search-input').value = '';
    filterTickets();
  });
  
  document.getElementById('search-input').addEventListener('input', filterTickets);
  
  // Real-time SLA countdown for open tickets
  function updateSLACountdowns() {
    const now = new Date();
    const rows = document.querySelectorAll('#ticket-table tr:not(.empty-state)');
    
    rows.forEach(row => {
      const status = row.getAttribute('data-status');
      const priority = row.getAttribute('data-priority');
      const createdCell = row.querySelector('td:nth-child(5)');
      const slaCell = row.querySelector('td:nth-child(6)');
      
      if (status !== 'closed' && createdCell && slaCell) {
        const createdText = createdCell.textContent.trim();
        const createdDate = new Date(createdText);
        
        if (!isNaN(createdDate.getTime())) {
          const slaHours = {
            'critical': 2,
            'high': 4,
            'medium': 6,
            'low': 8
          }[priority] || 8;
          
          const deadline = new Date(createdDate);
          deadline.setHours(deadline.getHours() + slaHours);
          
          if (now > deadline) {
            slaCell.textContent = 'Overdue';
            slaCell.className = 'sla-overdue';
          } else {
            const diff = deadline - now;
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            slaCell.textContent = `${hours} hours, ${minutes} minutes remaining`;
            slaCell.className = '';
            
            // Add warning class if less than 1 hour remains
            if (hours === 0 && minutes < 60) {
              slaCell.classList.add('sla-overdue');
            }
          }
        }
      }
    });
  }
  
  // Update countdowns every minute
  updateSLACountdowns();
  setInterval(updateSLACountdowns, 60000);
  
  // Initial filter application
  document.addEventListener('DOMContentLoaded', filterTickets);
</script>
</body>
</html>