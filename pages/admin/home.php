<?php
session_start();
// Redirect non-admin users to the login page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

require '../../database/connection.php';

// Set the page title
$title = "Admin Dashboard";

// Include common layout files
include '../../includes/header.php';
include '../../includes/topbar.php';
include '../../includes/sidebar.php';

// Function to fetch count from a table with PDO
function fetchCount($conn, $tableName)
{
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM $tableName");
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data['count'] ?? 0;
    } catch (Exception $e) {
        error_log("Error fetching count from $tableName: " . $e->getMessage());
        return 0;
    }
}

// Fetch counts for total users, total events, and total participants
$totalUsers = fetchCount($conn, 'users');
$totalEvents = fetchCount($conn, 'events');
$totalParticipants = fetchCount($conn, 'event_participants');
?>

<div id="page-content-wrapper">
    <div class="container mt-4">
        <h1 class="mb-4">Welcome to Admin Dashboard</h1>
        <div class="row">
            <!-- Total Users Card -->
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <h5>Total Users</h5>
                    <h3><?php echo htmlspecialchars($totalUsers); ?></h3>
                    <small>Last updated: <?php echo htmlspecialchars(date('Y-m-d H:i:s')); ?></small>
                </div>
            </div>

            <!-- Total Events Card -->
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <h5>Total Events</h5>
                    <h3><?php echo htmlspecialchars($totalEvents); ?></h3>
                    <small>Last updated: <?php echo htmlspecialchars(date('Y-m-d H:i:s')); ?></small>
                </div>
            </div>

            <!-- Total Participants Card -->
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <h5>Total Participants</h5>
                    <h3><?php echo htmlspecialchars($totalParticipants); ?></h3>
                    <small>Last updated: <?php echo htmlspecialchars(date('Y-m-d H:i:s')); ?></small>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Highcharts Container -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5>Analytics: Total Users, Events, and Participants</h5>
                        <div id="analyticsChart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Detect if the system prefers dark mode
        const isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;

        const chartOptions = {
            chart: {
                type: 'column',
                backgroundColor: isDarkMode ? '#1c1e21' : '#ffffff', // Dynamic background
                style: {
                    fontFamily: 'Arial, sans-serif'
                }
            },
            title: {
                text: 'Analytics Overview',
                style: {
                    color: isDarkMode ? '#ffffff' : '#333333' // Dynamic title color
                }
            },
            xAxis: {
                categories: ['Total Users', 'Total Events', 'Total Participants'],
                title: {
                    text: 'Metrics',
                    style: {
                        color: isDarkMode ? '#ffffff' : '#333333' // Dynamic axis title color
                    }
                },
                labels: {
                    style: {
                        color: isDarkMode ? '#ffffff' : '#333333' // Dynamic label color
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Count',
                    style: {
                        color: isDarkMode ? '#ffffff' : '#333333' // Dynamic axis title color
                    }
                },
                labels: {
                    style: {
                        color: isDarkMode ? '#ffffff' : '#333333' // Dynamic label color
                    }
                },
                gridLineColor: isDarkMode ? '#555555' : '#e6e6e6' // Grid line color for better contrast
            },
            tooltip: {
                backgroundColor: isDarkMode ? '#333333' : '#ffffff', // Dynamic tooltip background
                style: {
                    color: isDarkMode ? '#ffffff' : '#333333' // Dynamic tooltip text color
                },
                borderColor: isDarkMode ? '#555555' : '#dddddd' // Dynamic tooltip border
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            exporting: {
                buttons: {
                    contextButton: {
                        menuItems: [
                            'downloadPNG',
                            'downloadJPEG',
                            'downloadPDF',
                            'downloadCSV',
                            'downloadXLS',
                            'viewData'
                        ]
                    }
                }
            },
            credits: {
                enabled: false // Disable Highcharts credits
            },
            series: [{
                name: 'Counts',
                data: [
                    <?php echo htmlspecialchars($totalUsers); ?>,
                    <?php echo htmlspecialchars($totalEvents); ?>,
                    <?php echo htmlspecialchars($totalParticipants); ?>
                ],
                color: isDarkMode ? '#00aaff' : '#007bff' // Dynamic column color
            }]
        };

        // Initialize the chart
        Highcharts.chart('analyticsChart', chartOptions);

        // Listen for changes to the system's color scheme
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
            chartOptions.chart.backgroundColor = e.matches ? '#1c1e21' : '#ffffff';
            chartOptions.title.style.color = e.matches ? '#ffffff' : '#333333';
            chartOptions.xAxis.title.style.color = e.matches ? '#ffffff' : '#333333';
            chartOptions.xAxis.labels.style.color = e.matches ? '#ffffff' : '#333333';
            chartOptions.yAxis.title.style.color = e.matches ? '#ffffff' : '#333333';
            chartOptions.yAxis.labels.style.color = e.matches ? '#ffffff' : '#333333';
            chartOptions.yAxis.gridLineColor = e.matches ? '#555555' : '#e6e6e6';
            chartOptions.tooltip.backgroundColor = e.matches ? '#333333' : '#ffffff';
            chartOptions.tooltip.style.color = e.matches ? '#ffffff' : '#333333';
            chartOptions.tooltip.borderColor = e.matches ? '#555555' : '#dddddd';
            chartOptions.series[0].color = e.matches ? '#00aaff' : '#007bff';

            // Re-render the chart
            Highcharts.chart('analyticsChart', chartOptions);
        });
    });
</script>
