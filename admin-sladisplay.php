<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticketing System with SLA Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fa;
        }
        .dashboard {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .card-header {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat {
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        .filter-controls {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        select, button {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        button {
            background: #4a6baf;
            color: white;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #3a5a9f;
        }
        .ticket-list {
            max-height: 400px;
            overflow-y: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .sla-met {
            color: #28a745;
        }
        .sla-breached {
            color: #dc3545;
        }
        .sla-pending {
            color: #ffc107;
        }
        .priority-critical {
            color: #dc3545;
            font-weight: bold;
        }
        .priority-high {
            color: #fd7e14;
            font-weight: bold;
        }
        .priority-medium {
            color: #ffc107;
            font-weight: bold;
        }
        .priority-low {
            color: #28a745;
            font-weight: bold;
        }
        .tab-container {
            margin-bottom: 20px;
        }
        .tab-buttons {
            display: flex;
            border-bottom: 1px solid #ddd;
        }
        .tab-button {
            padding: 10px 20px;
            background: #f8f9fa;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        .tab-button.active {
            background: #4a6baf;
            color: white;
        }
        .tab-content {
            display: none;
            padding: 20px 0;
        }
        .tab-content.active {
            display: block;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-actions {
            margin-top: 20px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="tab-container">
        <div class="tab-buttons">
            <button class="tab-button active" onclick="openTab('dashboard')">Dashboard</button>
            <button class="tab-button" onclick="openTab('tickets')">Tickets</button>
            <button class="tab-button" onclick="openTab('new-ticket')">New Ticket</button>
        </div>
        
        <div id="dashboard" class="tab-content active">
            <div class="dashboard">
                <div class="card" style="grid-column: span 2;">
                    <div class="card-header">SLA Performance Overview</div>
                    <div class="filter-controls">
                        <select id="time-period">
                            <option value="7">Last 7 Days</option>
                            <option value="30" selected>Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                            <option value="365">Last Year</option>
                            <option value="all">All Time</option>
                        </select>
                        <select id="priority-filter">
                            <option value="all">All Priorities</option>
                            <option value="critical">Critical</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                        <button id="apply-filters">Apply Filters</button>
                    </div>
                    <div class="summary-stats">
                        <div class="stat" style="background: #4a6baf;">
                            <div class="stat-value" id="total-tickets">0</div>
                            <div class="stat-label">Total Tickets</div>
                        </div>
                        <div class="stat" style="background: #28a745;">
                            <div class="stat-value" id="sla-met">0</div>
                            <div class="stat-label">SLA Met</div>
                        </div>
                        <div class="stat" style="background: #dc3545;">
                            <div class="stat-value" id="sla-breached">0</div>
                            <div class="stat-label">SLA Breached</div>
                        </div>
                        <div class="stat" style="background: #6c757d;">
                            <div class="stat-value" id="compliance-rate">0%</div>
                            <div class="stat-label">Compliance Rate</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">SLA Compliance by Priority</div>
                    <div class="chart-container">
                        <canvas id="complianceChart"></canvas>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">Average Resolution Time vs SLA Target</div>
                    <div class="chart-container">
                        <canvas id="resolutionTimeChart"></canvas>
                    </div>
                </div>

                <div class="card" style="grid-column: span 2;">
                    <div class="card-header">Recent Tickets</div>
                    <div class="ticket-list">
                        <table id="tickets-table">
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Priority</th>
                                    <th>Subject</th>
                                    <th>Created</th>
                                    <th>Resolved</th>
                                    <th>Time Taken</th>
                                    <th>SLA Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tickets-body">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="tickets" class="tab-content">
            <div class="card">
                <div class="card-header">All Tickets</div>
                <div class="filter-controls">
                    <select id="ticket-status-filter">
                        <option value="all">All Statuses</option>
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="closed">Closed</option>
                    </select>
                    <select id="ticket-priority-filter">
                        <option value="all">All Priorities</option>
                        <option value="critical">Critical</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                    <button id="apply-ticket-filters">Apply Filters</button>
                </div>
                <div class="ticket-list">
                    <table>
                        <thead>
                            <tr>
                                <th>Ticket ID</th>
                                <th>Priority</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="all-tickets-body">
                            <!-- Will be populated by PHP -->
                            <?php
                            // Database connection
                            $servername = "localhost";
                            $username = "your_username";
                            $password = "your_password";
                            $dbname = "your_database";
                            
                            try {
                                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                
                                // Fetch all tickets
                                $stmt = $conn->prepare("SELECT * FROM tickets ORDER BY created_at DESC");
                                $stmt->execute();
                                
                                $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($tickets as $ticket) {
                                    echo "<tr>";
                                    echo "<td>{$ticket['ticket_id']}</td>";
                                    echo "<td><span class='priority-{$ticket['priority']}'>{$ticket['priority']}</span></td>";
                                    echo "<td>{$ticket['subject']}</td>";
                                    echo "<td>{$ticket['status']}</td>";
                                    echo "<td>" . date('M d, Y H:i', strtotime($ticket['created_at'])) . "</td>";
                                    echo "<td>" . date('M d, Y H:i', strtotime($ticket['updated_at'])) . "</td>";
                                    echo "<td><button onclick='viewTicket({$ticket['ticket_id']})'>View</button></td>";
                                    echo "</tr>";
                                }
                            } catch(PDOException $e) {
                                echo "<tr><td colspan='7'>Error loading tickets: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div id="new-ticket" class="tab-content">
            <div class="card">
                <div class="card-header">Create New Ticket</div>
                <form id="ticket-form" action="create_ticket.php" method="POST">
                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department">
                    </div>
                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="device_used">Device Used</label>
                        <input type="text" id="device_used" name="device_used">
                    </div>
                    <div class="form-group">
                        <label for="issue_frequency">Issue Frequency</label>
                        <select id="issue_frequency" name="issue_frequency">
                            <option value="first_time">First Time</option>
                            <option value="occasionally">Occasionally</option>
                            <option value="frequently">Frequently</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="urgency_reason">Reason for Urgency (if high/critical)</label>
                        <textarea id="urgency_reason" name="urgency_reason" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="attachment">Attachment</label>
                        <input type="file" id="attachment" name="attachment">
                    </div>
                    <div class="form-actions">
                        <button type="reset">Clear</button>
                        <button type="submit">Submit Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Tab functionality
        function openTab(tabName) {
            const tabs = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove('active');
            }
            
            const tabButtons = document.getElementsByClassName('tab-button');
            for (let i = 0; i < tabButtons.length; i++) {
                tabButtons[i].classList.remove('active');
            }
            
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
            
            if (tabName === 'dashboard') {
                applyFilters();
            }
        }

        // View ticket details
        function viewTicket(ticketId) {
            alert('Viewing ticket ' + ticketId + ' - In a real app, this would show detailed view');
            // In a real implementation, this would fetch and display ticket details
        }

        // Fetch SLA data from PHP backend
        async function fetchSLAData(days, priority) {
            try {
                const response = await fetch(`get_sla_data.php?days=${days}&priority=${priority}`);
                return await response.json();
            } catch (error) {
                console.error('Error fetching SLA data:', error);
                return [];
            }
        }

        // Update dashboard with real data
        async function updateDashboardWithRealData(days, priority) {
            const data = await fetchSLAData(days, priority);
            
            if (data && data.tickets) {
                updateSummaryStats(data.tickets);
                updateComplianceChart(data.tickets);
                updateResolutionTimeChart(data.tickets);
                updateTicketsTable(data.tickets);
            }
        }

        // Update summary stats
        function updateSummaryStats(tickets) {
            const total = tickets.length;
            const met = tickets.filter(t => t.sla_status === 'within_sla').length;
            const breached = tickets.filter(t => t.sla_status === 'breached').length;
            const pending = tickets.filter(t => t.sla_status === 'not_applicable' || t.sla_status === null).length;
            const complianceRate = total > 0 ? Math.round((met / (met + breached)) * 100) : 0;
            
            document.getElementById('total-tickets').textContent = total;
            document.getElementById('sla-met').textContent = met;
            document.getElementById('sla-breached').textContent = breached;
            document.getElementById('compliance-rate').textContent = `${complianceRate}%`;
        }

        // Update compliance chart
        let complianceChart;
        function updateComplianceChart(tickets) {
            const priorities = ['critical', 'high', 'medium', 'low'];
            const metData = [];
            const breachedData = [];
            const pendingData = [];
            
            priorities.forEach(priority => {
                const priorityTickets = tickets.filter(t => t.priority === priority);
                metData.push(priorityTickets.filter(t => t.sla_status === 'within_sla').length);
                breachedData.push(priorityTickets.filter(t => t.sla_status === 'breached').length);
                pendingData.push(priorityTickets.filter(t => t.sla_status === 'not_applicable' || t.sla_status === null).length);
            });
            
            const ctx = document.getElementById('complianceChart').getContext('2d');
            
            if (complianceChart) {
                complianceChart.data.datasets[0].data = metData;
                complianceChart.data.datasets[1].data = breachedData;
                complianceChart.data.datasets[2].data = pendingData;
                complianceChart.update();
            } else {
                complianceChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: priorities.map(p => p.charAt(0).toUpperCase() + p.slice(1)),
                        datasets: [
                            {
                                label: 'SLA Met',
                                data: metData,
                                backgroundColor: '#28a745',
                                animation: {
                                    duration: 1000,
                                    easing: 'easeOutQuart'
                                }
                            },
                            {
                                label: 'SLA Breached',
                                data: breachedData,
                                backgroundColor: '#dc3545',
                                animation: {
                                    duration: 1000,
                                    easing: 'easeOutQuart'
                                }
                            },
                            {
                                label: 'Pending',
                                data: pendingData,
                                backgroundColor: '#ffc107',
                                animation: {
                                    duration: 1000,
                                    easing: 'easeOutQuart'
                                }
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                stacked: true,
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            },
                            animation: {
                                animateScale: true,
                                animateRotate: true
                            }
                        }
                    }
                });
            }
        }

        // Update resolution time chart
        let resolutionTimeChart;
        function updateResolutionTimeChart(tickets) {
            const priorities = ['critical', 'high', 'medium', 'low'];
            const avgResolutionTimes = [];
            const slaTargets = [];
            
            priorities.forEach(priority => {
                const resolvedTickets = tickets.filter(t => t.priority === priority && t.resolved_at);
                const avgTime = resolvedTickets.length > 0 
                    ? resolvedTickets.reduce((sum, t) => sum + (t.hours_to_resolve || 0), 0) / resolvedTickets.length
                    : 0;
                avgResolutionTimes.push(avgTime);
                
                // Get SLA target from definitions or use defaults
                const slaTarget = 
                    resolvedTickets.length > 0 && resolvedTickets[0].resolution_time_hours 
                    ? resolvedTickets[0].resolution_time_hours 
                    : getDefaultSLATarget(priority);
                slaTargets.push(slaTarget);
            });
            
            const ctx = document.getElementById('resolutionTimeChart').getContext('2d');
            
            if (resolutionTimeChart) {
                resolutionTimeChart.data.datasets[0].data = avgResolutionTimes;
                resolutionTimeChart.data.datasets[1].data = slaTargets;
                resolutionTimeChart.update();
            } else {
                resolutionTimeChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: priorities.map(p => p.charAt(0).toUpperCase() + p.slice(1)),
                        datasets: [
                            {
                                label: 'Average Resolution Time (hours)',
                                data: avgResolutionTimes,
                                backgroundColor: '#4a6baf',
                                animation: {
                                    duration: 1000,
                                    easing: 'easeOutQuart'
                                }
                            },
                            {
                                label: 'SLA Target (hours)',
                                data: slaTargets,
                                type: 'line',
                                borderColor: '#28a745',
                                borderWidth: 2,
                                fill: false,
                                pointBackgroundColor: '#28a745',
                                pointRadius: 5,
                                pointHoverRadius: 7
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            },
                            animation: {
                                animateScale: true,
                                animateRotate: true
                            },
                            datalabels: {
                                display: false
                            }
                        }
                    }
                });
            }
        }

        function getDefaultSLATarget(priority) {
            const defaults = {
                critical: 2,
                high: 8,
                medium: 24,
                low: 72
            };
            return defaults[priority] || 24;
        }

        // Update tickets table
        function updateTicketsTable(tickets) {
            const tableBody = document.getElementById('tickets-body');
            tableBody.innerHTML = '';
            
            // Sort by created date (newest first)
            const sortedTickets = [...tickets].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            
            // Show only the first 20 for performance
            const displayTickets = sortedTickets.slice(0, 20);
            
            displayTickets.forEach(ticket => {
                const row = document.createElement('tr');
                
                // Format dates
                const createdDate = new Date(ticket.created_at).toLocaleString();
                const resolvedDate = ticket.resolved_at 
                    ? new Date(ticket.resolved_at).toLocaleString() 
                    : 'Pending';
                
                // Format time taken
                let timeTaken = 'Pending';
                if (ticket.resolved_at) {
                    const hours = Math.floor(ticket.hours_to_resolve || 0);
                    const minutes = Math.floor(((ticket.hours_to_resolve || 0) - hours) * 60);
                    timeTaken = `${hours}h ${minutes}m`;
                }
                
                // Determine SLA status
                let slaStatus = 'pending';
                let slaStatusClass = 'sla-pending';
                if (ticket.sla_status === 'within_sla') {
                    slaStatus = 'met';
                    slaStatusClass = 'sla-met';
                } else if (ticket.sla_status === 'breached') {
                    slaStatus = 'breached';
                    slaStatusClass = 'sla-breached';
                }
                
                // Add row content
                row.innerHTML = `
                    <td>${ticket.ticket_id}</td>
                    <td><span class="priority-${ticket.priority}">${ticket.priority}</span></td>
                    <td>${ticket.subject}</td>
                    <td>${createdDate}</td>
                    <td>${resolvedDate}</td>
                    <td>${timeTaken}</td>
                    <td class="${slaStatusClass}">${slaStatus}</td>
                    <td><button onclick="viewTicket(${ticket.ticket_id})">View</button></td>
                `;
                
                tableBody.appendChild(row);
            });
        }

        // Apply filters and update all visualizations
        function applyFilters() {
            const days = document.getElementById('time-period').value;
            const priority = document.getElementById('priority-filter').value;
            updateDashboardWithRealData(days, priority);
        }

        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', () => {
            applyFilters();
            
            // Add event listener for the apply filters button
            document.getElementById('apply-filters').addEventListener('click', applyFilters);
            
            // Add event listener for ticket filters
            document.getElementById('apply-ticket-filters').addEventListener('click', () => {
                const status = document.getElementById('ticket-status-filter').value;
                const priority = document.getElementById('ticket-priority-filter').value;
                filterTicketsTable(status, priority);
            });
            
            // Add animation to stats cards
            const stats = document.querySelectorAll('.stat');
            stats.forEach((stat, index) => {
                stat.style.animationDelay = `${index * 0.1}s`;
            });
        });

        // Filter tickets table
        function filterTicketsTable(status, priority) {
            const rows = document.getElementById('all-tickets-body').querySelectorAll('tr');
            
            rows.forEach(row => {
                const rowStatus = row.cells[3].textContent;
                const rowPriority = row.cells[1].querySelector('span').className.replace('priority-', '');
                
                const statusMatch = status === 'all' || rowStatus === status;
                const priorityMatch = priority === 'all' || rowPriority === priority;
                
                if (statusMatch && priorityMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>